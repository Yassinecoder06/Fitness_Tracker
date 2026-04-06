create extension if not exists pgcrypto;

-- Canonical category domain
 do $$
 begin
   if not exists (select 1 from pg_type where typname = 'food_category') then
     create type public.food_category as enum (
       'Fruits',
       'Vegetables',
       'Protein',
       'Grains',
       'Dairy',
       'Snacks'
     );
   end if;
 end $$;

create table if not exists public.foods (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  category public.food_category not null,
  calories numeric(6,2) not null default 0 check (calories >= 0),
  protein numeric(6,2) not null default 0 check (protein >= 0),
  carbs numeric(6,2) not null default 0 check (carbs >= 0),
  fat numeric(6,2) not null default 0 check (fat >= 0),
  serving text not null default '100g',
  image_url text,
  source text not null default 'openfoodfacts',
  source_product_id text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create unique index if not exists ux_foods_source_product
  on public.foods (source, source_product_id)
  where source_product_id is not null;

create unique index if not exists ux_foods_name_category_serving
  on public.foods (lower(name), category, lower(serving));

create index if not exists idx_foods_name_lower on public.foods (lower(name));
create index if not exists idx_foods_category on public.foods (category);
create index if not exists idx_foods_created_at on public.foods (created_at desc);

create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

drop trigger if exists trg_foods_set_updated_at on public.foods;
create trigger trg_foods_set_updated_at
before update on public.foods
for each row execute function public.set_updated_at();
