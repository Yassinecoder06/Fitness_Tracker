# FitTrack

Food data was webscraped from OpenFoodFacts.

## Prerequisites

- Docker Desktop running
- Supabase CLI installed
- PHP 8+ (for the local web server)

## Local setup (A to B)

1. Initialize and start Supabase from the project root:

   ```powershell
   supabase init
   supabase start
   ```

2. Fetch local service details:

   ```powershell
   supabase status
   ```

   You will need:
   - API URL (usually http://127.0.0.1:54321)
   - DB URL (usually postgresql://postgres:postgres@127.0.0.1:54322/postgres)
   - service_role key

3. Configure environment variables:

   ```powershell
   Copy-Item .env.example .env -Force
   ```

   Open `.env` and set `SUPABASE_SERVICE_ROLE_KEY` using the value from `supabase status`.

4. Apply database migrations (schema + seed data):

   ```powershell
   supabase db push
   ```

5. Run the PHP app locally:

   ```powershell
   php -S localhost:8000
   ```

   Open:
   - http://localhost:8000/index.php

## Project structure (quick map)

- PHP pages are in the project root (for example: index.php, food.php, diary.php).
- Client assets:
  - CSS: css/
  - JS: js/
- Legacy static HTML pages (reference only): legacy-html/
- Supabase migrations: supabase/migrations/

## Common Supabase CLI commands

- Start/stop:

  ```powershell
  supabase start
  supabase stop
  ```

- Status:

  ```powershell
  supabase status
  ```
