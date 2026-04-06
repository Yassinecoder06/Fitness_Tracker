import logging
import os
import re
import time
from typing import Dict, List, Optional, Tuple

import psycopg2
import requests
from dotenv import load_dotenv
from psycopg2.extras import execute_batch

load_dotenv()

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
)

OPENFOODFACTS_ENDPOINT = "https://world.openfoodfacts.org/cgi/search.pl"
TARGET_COUNT = 1000
PAGE_SIZE = 100
REQUEST_TIMEOUT = 20
SLEEP_BETWEEN_CALLS = 0.25

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
    response = requests.get(OPENFOODFACTS_ENDPOINT, params=params, timeout=REQUEST_TIMEOUT)
    response.raise_for_status()
    payload = response.json()
    return payload.get("products", [])


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


def main() -> None:
    db_url = os.getenv("SUPABASE_DB_URL")
    image_mode = os.getenv("IMAGE_MODE", "mirror").strip().lower()

    if not db_url:
        raise RuntimeError("SUPABASE_DB_URL is required")

    if image_mode not in ("mirror", "direct"):
        raise RuntimeError("IMAGE_MODE must be mirror or direct")

    logging.info("Starting import. Target=%s mode=%s", TARGET_COUNT, image_mode)

    conn = psycopg2.connect(db_url)
    conn.autocommit = False

    seen_keys = set()
    rows_to_upsert = []

    try:
        for default_category, terms in SEARCH_TERMS.items():
            for term in terms:
                page = 1
                while len(rows_to_upsert) < TARGET_COUNT:
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

                        name, category, *_rest, source, source_product_id = cleaned
                        dedupe_key = (normalize_name(name), category, source_product_id or "")
                        if dedupe_key in seen_keys:
                            continue

                        seen_keys.add(dedupe_key)
                        rows_to_upsert.append(cleaned)

                        if len(rows_to_upsert) >= TARGET_COUNT:
                            break

                    logging.info(
                        "Collected %s/%s foods (term=%s, page=%s)",
                        len(rows_to_upsert),
                        TARGET_COUNT,
                        term,
                        page,
                    )

                    if len(rows_to_upsert) >= TARGET_COUNT:
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

        with conn.cursor() as cur:
            with_source_id = [row for row in rows_to_upsert if row[-1] is not None]
            without_source_id = [row for row in rows_to_upsert if row[-1] is None]

            if with_source_id:
                upsert_by_source_sql = """
                    insert into public.foods (
                        name, category, calories, protein, carbs, fat,
                        serving, image_url, source, source_product_id
                    )
                    values (%s, %s::public.food_category, %s, %s, %s, %s, %s, %s, %s, %s)
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
                execute_batch(cur, upsert_by_source_sql, with_source_id, page_size=200)

            if without_source_id:
                upsert_by_name_sql = """
                    insert into public.foods (
                        name, category, calories, protein, carbs, fat,
                        serving, image_url, source, source_product_id
                    )
                    values (%s, %s::public.food_category, %s, %s, %s, %s, %s, %s, %s, %s)
                    on conflict (lower(name), category, lower(serving))
                    do update set
                        calories = excluded.calories,
                        protein = excluded.protein,
                        carbs = excluded.carbs,
                        fat = excluded.fat,
                        image_url = excluded.image_url,
                        updated_at = now()
                """
                execute_batch(cur, upsert_by_name_sql, without_source_id, page_size=200)

        conn.commit()
        logging.info("Import complete. Total processed rows=%s", len(rows_to_upsert))

    except Exception:
        conn.rollback()
        logging.exception("Import failed. Rolled back transaction.")
        raise

    finally:
        conn.close()


if __name__ == "__main__":
    main()
