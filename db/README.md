# Database Notes

Use `sql/schema.sql` as the source of truth for the Food Database module schema.

## Supabase CLI

1. `supabase init`
2. `supabase start`
3. Create a migration and paste `sql/schema.sql` content.
4. `supabase db push`

## Required Storage Bucket (Plan B)

Create a public bucket named `food-images` in Supabase Storage.
