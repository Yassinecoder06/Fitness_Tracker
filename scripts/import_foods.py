import logging
import os
import re
import json
import time
import random
from typing import Dict, List, Optional, Tuple

import psycopg2
import requests
from dotenv import load_dotenv
from psycopg2.extras import execute_batch

load_dotenv(override=True)

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
)

OPENFOODFACTS_ENDPOINT = os.getenv("OPENFOODFACTS_ENDPOINT", "https://world.openfoodfacts.org/cgi/search.pl").strip()
TARGET_COUNT = int(os.getenv("TARGET_COUNT", "50"))
MAX_PER_TERM = int(os.getenv("MAX_PER_TERM", "50"))
PAGE_SIZE = 100
REQUEST_TIMEOUT = 20
SLEEP_BETWEEN_CALLS = 0.25
MAX_FETCH_RETRIES = int(os.getenv("MAX_FETCH_RETRIES", "5"))
MAX_BACKOFF_SECONDS = float(os.getenv("OPENFOODFACTS_MAX_BACKOFF_SECONDS", "20"))
EXPORT_JSON_PATH = os.getenv("EXPORT_JSON_PATH", "db/foods_export.json")
EXPORT_SQL_PATH = os.getenv("EXPORT_SQL_PATH", "sql/foods_seed_from_openfoodfacts.sql")

# OpenFoodFacts frequently blocks anonymous/default clients. Use an explicit
# app identity header and allow override via env for production usage.
DEFAULT_OFF_USER_AGENT = "FitTrackFoodImporter/1.0 (Fitness Tracker data sync)"
OFF_USER_AGENT = os.getenv("OPENFOODFACTS_USER_AGENT", DEFAULT_OFF_USER_AGENT).strip()
OPENFOODFACTS_USER_ID = (os.getenv("OPENFOODFACTS_USER_ID") or "").strip()
OPENFOODFACTS_PASSWORD = os.getenv("OPENFOODFACTS_PASSWORD") or ""

HTTP_SESSION = requests.Session()
HTTP_SESSION.headers.update(
    {
        "User-Agent": OFF_USER_AGENT,
        "Accept": "application/json",
    }
)

if OPENFOODFACTS_USER_ID and OPENFOODFACTS_PASSWORD:
    HTTP_SESSION.auth = (OPENFOODFACTS_USER_ID, OPENFOODFACTS_PASSWORD)

FIT_CATEGORIES = ["Fruits", "Vegetables", "Protein", "Grains", "Dairy", "Snacks"]

SEARCH_TERMS = {
    "Protein": ["chicken", "egg", "tofu", "tuna", "protein"],
    "Grains": ["rice", "oats", "bread", "quinoa", "grain"],
    "Fruits": ["banana", "apple", "orange", "fruit", "berries"],
    "Vegetables": ["broccoli", "spinach", "carrot", "vegetable", "kale"],
    "Dairy": ["milk", "yogurt", "cheese", "dairy", "cottage"],
    "Snacks": ["nuts", "snack", "granola", "bar", "cracker"],
}


def safe_float(value, default=0.0) -> float:
    try:
        if value is None or value == "":
            return float(default)
        return max(float(value), 0.0)
    except (TypeError, ValueError):
        return float(default)


def normalize_name(name: str) -> str:
    name = (name or "").strip().lower()
    return re.sub(r"\s+", " ", name)


def infer_category(tags: List[str], fallback: str) -> str:
    joined = " ".join((tags or [])).lower()

    rules = [
        ("Fruits", ["fruit", "fruits", "berries", "banana", "apple", "orange"]),
        ("Vegetables", ["vegetable", "vegetables", "broccoli", "spinach", "carrot", "kale"]),
        ("Protein", ["protein", "meat", "fish", "egg", "tofu", "chicken", "tuna", "legume"]),
        ("Grains", ["grain", "cereal", "rice", "oat", "bread", "quinoa", "pasta"]),
        ("Dairy", ["dairy", "milk", "cheese", "yogurt", "yoghurt"]),
        ("Snacks", ["snack", "chips", "cracker", "bar", "nuts"]),
    ]

    for category, keywords in rules:
        if any(keyword in joined for keyword in keywords):
            return category

    return fallback if fallback in FIT_CATEGORIES else "Snacks"


def fetch_products(term: str, page: int) -> List[Dict]:
    params = {
        "search_terms": term,
        "json": 1,
        "page_size": PAGE_SIZE,
        "page": page,
    }

    last_error: Optional[Exception] = None
    for attempt in range(1, MAX_FETCH_RETRIES + 1):
        try:
            response = HTTP_SESSION.get(OPENFOODFACTS_ENDPOINT, params=params, timeout=REQUEST_TIMEOUT)
            response.raise_for_status()
            payload = response.json()
            return payload.get("products", [])
        except requests.HTTPError as exc:
            last_error = exc
            status_code = exc.response.status_code if exc.response is not None else None
            response_text = (exc.response.text[:250] if exc.response is not None and exc.response.text else "")

            if status_code in (429, 503):
                logging.warning(
                    "OpenFoodFacts rate-limited/unavailable (%s) term=%s page=%s attempt=%s/%s. "
                    "Using backoff and retry.",
                    status_code,
                    term,
                    page,
                    attempt,
                    MAX_FETCH_RETRIES,
                )
                if response_text and "temporarily unavailable" in response_text.lower():
                    logging.warning(
                        "OpenFoodFacts returned temporary-unavailable page. "
                        "Set OPENFOODFACTS_USER_ID/OPENFOODFACTS_PASSWORD in .env for priority access."
                    )

            if status_code == 403:
                logging.warning(
                    "OpenFoodFacts returned 403 (term=%s page=%s attempt=%s user-agent=%s). "
                    "Set OPENFOODFACTS_USER_AGENT to a descriptive value if needed.",
                    term,
                    page,
                    attempt,
                    OFF_USER_AGENT,
                )

            if attempt < MAX_FETCH_RETRIES:
                retry_after = None
                if exc.response is not None:
                    retry_after = exc.response.headers.get("Retry-After")

                if retry_after:
                    try:
                        sleep_seconds = max(float(retry_after), 0.5)
                    except ValueError:
                        sleep_seconds = min((2 ** attempt) + random.uniform(0.0, 0.7), MAX_BACKOFF_SECONDS)
                else:
                    sleep_seconds = min((2 ** attempt) + random.uniform(0.0, 0.7), MAX_BACKOFF_SECONDS)

                time.sleep(sleep_seconds)
                continue
            raise
        except requests.RequestException as exc:
            last_error = exc
            if attempt < MAX_FETCH_RETRIES:
                sleep_seconds = min((2 ** attempt) + random.uniform(0.0, 0.7), MAX_BACKOFF_SECONDS)
                time.sleep(sleep_seconds)
                continue
            raise

    if last_error is not None:
        raise last_error
    return []


def upload_image_to_supabase(image_url: str, source_product_id: str) -> Optional[str]:
    supabase_url = os.getenv("SUPABASE_URL", "").rstrip("/")
    service_role = os.getenv("SUPABASE_SERVICE_ROLE_KEY", "")
    bucket = os.getenv("SUPABASE_STORAGE_BUCKET", "food-images")

    if not supabase_url or not service_role or not image_url or not source_product_id:
        return None

    try:
        image_response = requests.get(image_url, timeout=REQUEST_TIMEOUT)
        image_response.raise_for_status()
        content_type = image_response.headers.get("Content-Type", "image/jpeg")

        object_path = f"openfoodfacts/{source_product_id}.jpg"
        upload_url = f"{supabase_url}/storage/v1/object/{bucket}/{object_path}"
        headers = {
            "Authorization": f"Bearer {service_role}",
            "apikey": service_role,
            "Content-Type": content_type,
            "x-upsert": "true",
        }

        upload_response = requests.post(upload_url, headers=headers, data=image_response.content, timeout=REQUEST_TIMEOUT)

        # 200/201 success, 409 may happen on existing object without upsert support in some setups
        if upload_response.status_code not in (200, 201):
            logging.debug("Image upload failed %s: %s", upload_response.status_code, upload_response.text)
            return None

        return f"{supabase_url}/storage/v1/object/public/{bucket}/{object_path}"

    except requests.RequestException as exc:
        logging.debug("Image mirror failed for %s: %s", source_product_id, exc)
        return None


def clean_product(product: Dict, default_category: str, image_mode: str) -> Optional[Tuple]:
    name = (product.get("product_name") or "").strip()
    if not name:
        return None

    nutriments = product.get("nutriments", {}) or {}
    kcal = safe_float(nutriments.get("energy-kcal_100g"))
    protein = safe_float(nutriments.get("proteins_100g"))
    carbs = safe_float(nutriments.get("carbohydrates_100g"))
    fat = safe_float(nutriments.get("fat_100g"))

    if kcal == 0 and protein == 0 and carbs == 0 and fat == 0:
        return None

    tags = product.get("categories_tags") or []
    category = infer_category(tags, default_category)
    source_product_id = str(product.get("id") or "").strip() or None

    image_url = product.get("image_front_url")
    if image_mode == "mirror" and image_url and source_product_id:
        mirrored = upload_image_to_supabase(image_url, source_product_id)
        if mirrored:
            image_url = mirrored

    return (
        name,
        category,
        round(kcal, 2),
        round(protein, 2),
        round(carbs, 2),
        round(fat, 2),
        "100g",
        image_url,
        "openfoodfacts",
        source_product_id,
    )


def row_to_dict(row: Tuple) -> Dict:
    (
        name,
        category,
        calories,
        protein,
        carbs,
        fat,
        serving,
        image_url,
        source,
        source_product_id,
    ) = row

    return {
        "name": name,
        "category": category,
        "calories": float(calories),
        "protein": float(protein),
        "carbs": float(carbs),
        "fat": float(fat),
        "serving": serving,
        "image_url": image_url,
        "source": source,
        "source_product_id": source_product_id,
    }


def sql_literal(value) -> str:
    if value is None:
        return "NULL"
    if isinstance(value, (int, float)):
        return str(value)
    escaped = str(value).replace("'", "''")
    return f"'{escaped}'"


def ensure_parent_dir(file_path: str) -> None:
    parent = os.path.dirname(file_path)
    if parent:
        os.makedirs(parent, exist_ok=True)


def write_json_export(rows_to_upsert: List[Tuple], output_path: str) -> None:
    ensure_parent_dir(output_path)
    payload = [row_to_dict(row) for row in rows_to_upsert]
    with open(output_path, "w", encoding="utf-8") as f:
        json.dump(payload, f, ensure_ascii=False, indent=2)


def write_sql_seed(rows_to_upsert: List[Tuple], output_path: str) -> None:
    ensure_parent_dir(output_path)
    with_source_id = [row for row in rows_to_upsert if row[-1] is not None]
    without_source_id = [row for row in rows_to_upsert if row[-1] is None]

    with open(output_path, "w", encoding="utf-8") as f:
        f.write("-- Auto-generated from scripts/import_foods.py\n")
        f.write("-- Source: OpenFoodFacts (direct image URLs)\n\n")
        if with_source_id:
            f.write("-- Upsert rows that have source_product_id\n")
            f.write("insert into public.foods (\n")
            f.write("  name, category, calories, protein, carbs, fat,\n")
            f.write("  serving, image_url, source, source_product_id\n")
            f.write(") values\n")

            values_sql = []
            for row in with_source_id:
                values = ", ".join(sql_literal(v) for v in row)
                values_sql.append(f"  ({values})")

            f.write(",\n".join(values_sql))
            f.write("\n")
            f.write("on conflict (source, source_product_id)\n")
            f.write("where source_product_id is not null\n")
            f.write("do update set\n")
            f.write("  name = excluded.name,\n")
            f.write("  category = excluded.category,\n")
            f.write("  calories = excluded.calories,\n")
            f.write("  protein = excluded.protein,\n")
            f.write("  carbs = excluded.carbs,\n")
            f.write("  fat = excluded.fat,\n")
            f.write("  serving = excluded.serving,\n")
            f.write("  image_url = excluded.image_url,\n")
            f.write("  updated_at = now();\n\n")

        if without_source_id:
            f.write("-- Upsert rows that do not have source_product_id\n")
            f.write("insert into public.foods (\n")
            f.write("  name, category, calories, protein, carbs, fat,\n")
            f.write("  serving, image_url, source, source_product_id\n")
            f.write(") values\n")

            values_sql = []
            for row in without_source_id:
                values = ", ".join(sql_literal(v) for v in row)
                values_sql.append(f"  ({values})")

            f.write(",\n".join(values_sql))
            f.write("\n")
            f.write("on conflict (lower(name), category, lower(serving))\n")
            f.write("do update set\n")
            f.write("  calories = excluded.calories,\n")
            f.write("  protein = excluded.protein,\n")
            f.write("  carbs = excluded.carbs,\n")
            f.write("  fat = excluded.fat,\n")
            f.write("  image_url = excluded.image_url,\n")
            f.write("  updated_at = now();\n")


def upsert_to_database(conn, rows_to_upsert: List[Tuple]) -> None:
    with conn.cursor() as cur:
        # Stage rows in a temp table so we can merge deterministically across
        # both unique keys: (source, source_product_id) and logical food key.
        cur.execute(
            """
            create temporary table if not exists tmp_food_seed (
                name text not null,
                category public.food_category not null,
                calories numeric(6,2) not null,
                protein numeric(6,2) not null,
                carbs numeric(6,2) not null,
                fat numeric(6,2) not null,
                serving text not null,
                image_url text,
                source text not null,
                source_product_id text
            ) on commit drop
            """
        )
        cur.execute("truncate table tmp_food_seed")

        stage_insert_sql = """
            insert into tmp_food_seed (
                name, category, calories, protein, carbs, fat,
                serving, image_url, source, source_product_id
            )
            values (%s, %s::public.food_category, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        execute_batch(cur, stage_insert_sql, rows_to_upsert, page_size=500)

        # Keep one staged row per logical food key to prevent duplicate inserts.
        cur.execute(
            """
            delete from tmp_food_seed a
            using tmp_food_seed b
            where a.ctid < b.ctid
              and lower(a.name) = lower(b.name)
              and a.category = b.category
              and lower(a.serving) = lower(b.serving)
            """
        )

        # Update existing records by logical key first.
        cur.execute(
            """
            update public.foods f
            set
                name = s.name,
                calories = s.calories,
                protein = s.protein,
                carbs = s.carbs,
                fat = s.fat,
                image_url = s.image_url,
                source = s.source,
                source_product_id = coalesce(f.source_product_id, s.source_product_id),
                updated_at = now()
            from tmp_food_seed s
            where lower(f.name) = lower(s.name)
              and f.category = s.category
              and lower(f.serving) = lower(s.serving)
            """
        )

        # Remove rows that matched existing logical keys.
        cur.execute(
            """
            delete from tmp_food_seed s
            using public.foods f
            where lower(f.name) = lower(s.name)
              and f.category = s.category
              and lower(f.serving) = lower(s.serving)
            """
        )

        # Insert remaining rows with source product IDs.
        cur.execute(
            """
            insert into public.foods (
                name, category, calories, protein, carbs, fat,
                serving, image_url, source, source_product_id
            )
            select
                name, category, calories, protein, carbs, fat,
                serving, image_url, source, source_product_id
            from tmp_food_seed
            where source_product_id is not null
            on conflict (source, source_product_id)
            where source_product_id is not null
            do update set
                name = excluded.name,
                category = excluded.category,
                calories = excluded.calories,
                protein = excluded.protein,
                carbs = excluded.carbs,
                fat = excluded.fat,
                serving = excluded.serving,
                image_url = excluded.image_url,
                updated_at = now()
            """
        )

        # Insert remaining rows without source product IDs.
        cur.execute(
            """
            insert into public.foods (
                name, category, calories, protein, carbs, fat,
                serving, image_url, source, source_product_id
            )
            select
                name, category, calories, protein, carbs, fat,
                serving, image_url, source, source_product_id
            from tmp_food_seed
            where source_product_id is null
            on conflict (lower(name), category, lower(serving))
            do update set
                calories = excluded.calories,
                protein = excluded.protein,
                carbs = excluded.carbs,
                fat = excluded.fat,
                image_url = excluded.image_url,
                updated_at = now()
            """
        )


def connect_database() -> psycopg2.extensions.connection:
    db_url = (os.getenv("SUPABASE_DB_URL") or "").strip()

    # Primary path: DSN URL from env (typically direct host:5432).
    if db_url:
        try:
            return psycopg2.connect(db_url)
        except Exception as exc:
            logging.warning("Primary SUPABASE_DB_URL connection failed: %s", exc)

    direct_host = (os.getenv("SUPABASE_DB_HOST") or "").strip()
    direct_port = (os.getenv("SUPABASE_DB_PORT") or "5432").strip()
    direct_db_name = (os.getenv("SUPABASE_DB_NAME") or "postgres").strip()
    direct_user = (os.getenv("SUPABASE_DB_USER") or "").strip()
    direct_password = os.getenv("SUPABASE_DB_PASSWORD") or ""
    direct_sslmode = (os.getenv("SUPABASE_DB_SSLMODE") or "require").strip()

    if all([direct_host, direct_db_name, direct_user, direct_password]):
        try:
            return psycopg2.connect(
                host=direct_host,
                port=direct_port,
                dbname=direct_db_name,
                user=direct_user,
                password=direct_password,
                sslmode=direct_sslmode,
            )
        except Exception as exc:
            logging.warning("Direct DB connection (%s:%s) failed: %s", direct_host, direct_port, exc)

    raise RuntimeError(
        "DB connection config missing/failed. Provide direct settings "
        "(SUPABASE_DB_URL or SUPABASE_DB_HOST/PORT/NAME/USER/PASSWORD)."
    )

def main() -> None:
    db_url = os.getenv("SUPABASE_DB_URL")
    direct_fallback_available = all(
        [
            os.getenv("SUPABASE_DB_HOST"),
            os.getenv("SUPABASE_DB_NAME"),
            os.getenv("SUPABASE_DB_USER"),
            os.getenv("SUPABASE_DB_PASSWORD"),
        ]
    )
    image_mode = os.getenv("IMAGE_MODE", "direct").strip().lower()
    run_mode = os.getenv("RUN_MODE", "export").strip().lower()

    if image_mode not in ("mirror", "direct"):
        raise RuntimeError("IMAGE_MODE must be mirror or direct")

    if run_mode not in ("export", "db"):
        raise RuntimeError("RUN_MODE must be export or db")

    if MAX_PER_TERM <= 0:
        raise RuntimeError("MAX_PER_TERM must be greater than 0")

    if run_mode == "db" and not db_url and not direct_fallback_available:
        raise RuntimeError(
            "RUN_MODE=db requires direct DB settings"
        )

    if run_mode == "export" and image_mode != "direct":
        logging.warning("RUN_MODE=export uses direct image URLs. Overriding IMAGE_MODE=%s -> direct", image_mode)
        image_mode = "direct"

    logging.info(
        "Starting import. Target=%s max_per_term=%s mode=%s run_mode=%s",
        TARGET_COUNT,
        MAX_PER_TERM,
        image_mode,
        run_mode,
    )

    conn = None
    if run_mode == "db":
        conn = connect_database()
        conn.autocommit = False

    seen_keys = set()
    rows_to_upsert = []

    try:
        for default_category, terms in SEARCH_TERMS.items():
            for term in terms:
                page = 1
                term_collected = 0
                while len(rows_to_upsert) < TARGET_COUNT and term_collected < MAX_PER_TERM:
                    try:
                        products = fetch_products(term, page)
                    except requests.RequestException as exc:
                        logging.warning("API error term=%s page=%s: %s", term, page, exc)
                        break

                    if not products:
                        break

                    for product in products:
                        cleaned = clean_product(product, default_category, image_mode)
                        if not cleaned:
                            continue

                        # For the export dataset we only keep foods that have a usable image URL.
                        if not cleaned[7]:
                            continue

                        name, category, *_rest, source, source_product_id = cleaned
                        dedupe_key = (normalize_name(name), category, source_product_id or "")
                        if dedupe_key in seen_keys:
                            continue

                        seen_keys.add(dedupe_key)
                        rows_to_upsert.append(cleaned)
                        term_collected += 1

                        if len(rows_to_upsert) >= TARGET_COUNT or term_collected >= MAX_PER_TERM:
                            break

                    logging.info(
                        "Collected total=%s/%s term=%s/%s (term_name=%s, page=%s)",
                        len(rows_to_upsert),
                        TARGET_COUNT,
                        term_collected,
                        MAX_PER_TERM,
                        term,
                        page,
                    )

                    if len(rows_to_upsert) >= TARGET_COUNT or term_collected >= MAX_PER_TERM:
                        break

                    page += 1
                    time.sleep(SLEEP_BETWEEN_CALLS)

                if len(rows_to_upsert) >= TARGET_COUNT:
                    break

            if len(rows_to_upsert) >= TARGET_COUNT:
                break

        if not rows_to_upsert:
            logging.warning("No rows collected. Exiting.")
            return

        write_json_export(rows_to_upsert, EXPORT_JSON_PATH)
        write_sql_seed(rows_to_upsert, EXPORT_SQL_PATH)
        logging.info("Export complete. JSON=%s SQL=%s rows=%s", EXPORT_JSON_PATH, EXPORT_SQL_PATH, len(rows_to_upsert))

        if run_mode == "db" and conn is not None:
            upsert_to_database(conn, rows_to_upsert)
            conn.commit()
            logging.info("Database upsert complete. Total processed rows=%s", len(rows_to_upsert))

    except Exception:
        if conn is not None:
            conn.rollback()
            logging.exception("Import failed. Rolled back transaction.")
        else:
            logging.exception("Export failed.")
        raise

    finally:
        if conn is not None:
            conn.close()


if __name__ == "__main__":
    main()
