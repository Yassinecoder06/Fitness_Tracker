-- Update exercise categories and add exercise library

do $$
begin
  if not exists (select 1 from pg_type where typname = 'exercise_category_v2') then
    create type public.exercise_category_v2 as enum (
      'Cardio',
      'Strength',
      'Calisthenics',
      'Sports'
    );
  end if;
end $$;

alter table public.exercises
  alter column category type public.exercise_category_v2
  using (
    case
      when category::text = 'Flexibility' then 'Calisthenics'
      else category::text
    end
  )::public.exercise_category_v2;

do $$
begin
  if exists (select 1 from pg_type where typname = 'exercise_category') then
    drop type public.exercise_category;
  end if;
end $$;

alter type public.exercise_category_v2 rename to exercise_category;

alter table public.exercises
  add column if not exists exercise_id uuid;

create table if not exists public.exercise_library (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  category public.exercise_category not null,
  instructions text not null default '',
  created_at timestamptz not null default now()
);

create unique index if not exists ux_exercise_library_name_category
  on public.exercise_library (name, category);

create index if not exists idx_exercise_library_category
  on public.exercise_library (category);

create index if not exists idx_exercise_library_name_lower
  on public.exercise_library (lower(name));

do $$
begin
  if not exists (
    select 1
    from pg_constraint
    where conname = 'fk_exercises_library'
  ) then
    alter table public.exercises
      add constraint fk_exercises_library
      foreign key (exercise_id)
      references public.exercise_library (id)
      on delete set null;
  end if;
end $$;
