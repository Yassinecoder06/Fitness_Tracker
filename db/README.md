# Database Notes

Use `sql/schema.sql` as the source of truth for the Member 4 Food Database schema.

## Supabase CLI

1. `supabase init`
2. `supabase start`
3. Create a migration and paste `sql/schema.sql` content.
4. `supabase db push`

## Seed Data (Member 4)

1. Run `importation/import_foods.py` in export mode to generate:
	- `db/foods_export.json`
	- `sql/foods_seed_from_openfoodfacts.sql`
2. Apply `sql/foods_seed_from_openfoodfacts.sql` to insert/upsert food rows.

## Required Storage Bucket (Plan B)

Create a public bucket named `food-images` in Supabase Storage.
