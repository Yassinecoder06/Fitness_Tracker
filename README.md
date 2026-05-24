# FitTrack

FitTrack is a PHP + Supabase fitness tracker. It includes a dashboard, food database, diary, exercise logging, goals, and progress analytics. Food data can be sourced from OpenFoodFacts via the local scraper/importer.

## Features

- Dashboard with calories, macros, meals, and recent exercise summary
- Diary with meals, exercises, water, and notes tied to the logged-in user
- Food database with filters and pagination
- Exercise logging and category browsing
- Goals and progress tracking (weight logs and calories history)

## Tech stack

- PHP 8+
- Supabase (Postgres)
- Vanilla JS and CSS

## Project structure

- Root PHP pages: index.php, diary.php, food.php, exercise.php, progress.php, goals.php, login.php, register.php, logout.php
- Backend helpers: backend/
- Client assets: css/ and js/
- Supabase config and migrations: supabase/
- SQL files: sql/
- Import/export data: db/
- Python scripts: scripts/
- Legacy HTML reference: legacy-html/

## Environment variables (.env)

Create a .env in the project root. Do not commit it. Use placeholders like below and fill with your real values.

```dotenv
# Local database connection
SUPABASE_DB_URL=postgresql://USER:PASSWORD@HOST:PORT/DB_NAME?sslmode=disable
SUPABASE_DB_HOST=127.0.0.1
SUPABASE_DB_PORT=54322
SUPABASE_DB_NAME=postgres
SUPABASE_DB_USER=postgres
SUPABASE_DB_PASSWORD=postgres
SUPABASE_DB_SSLMODE=disable

# PHP runtime (local + cloud, toggle via SUPABASE_USE_CLOUD)
SUPABASE_USE_CLOUD=false
SUPABASE_PG_POOLER_DSN_LOCAL=pgsql:host=127.0.0.1;port=54322;dbname=postgres;sslmode=disable
SUPABASE_PG_POOLER_USER_LOCAL=postgres
SUPABASE_PG_POOLER_PASSWORD_LOCAL=postgres
SUPABASE_PG_POOLER_DSN_CLOUD=pgsql:host=YOUR-PROJECT.pooler.supabase.com;port=6543;dbname=postgres;sslmode=require
SUPABASE_PG_POOLER_USER_CLOUD=postgres.YOUR_PROJECT_REF
SUPABASE_PG_POOLER_PASSWORD_CLOUD=YOUR_CLOUD_DB_PASSWORD

# Local Supabase API
SUPABASE_URL=http://127.0.0.1:54321
SUPABASE_SERVICE_ROLE_KEY=YOUR_SERVICE_ROLE_KEY
```

## Local setup

1) Start Supabase locally:

```powershell
supabase init
supabase start
```

2) Get local service values:

```powershell
supabase status
```

3) Create .env and fill values from `supabase status`.

4) Apply migrations:

```powershell
supabase db push
```

5) Run the PHP server:

```powershell
php -S localhost:8000
```

Open http://localhost:8000/index.php

## Cloud setup

1) Set `SUPABASE_USE_CLOUD=true` in .env.
2) Fill the cloud pooler credentials in .env.
3) Push migrations:

```powershell
supabase db push
```

## Docker (PHP app only)

```powershell
docker build -t fittrack-php .
docker run --rm -p 8000:8000 --env-file .env fittrack-php
```

Or with docker-compose:

```powershell
docker compose up --build
```

## Common Supabase CLI commands

```powershell
supabase status
supabase db push
supabase stop
```

## Notes

- App data is scoped by user_id, so make sure you are logged in.
- If you reset the cloud DB, clear migration history before re-pushing.
