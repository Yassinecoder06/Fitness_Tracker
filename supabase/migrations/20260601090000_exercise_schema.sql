create extension if not exists pgcrypto;

-- Canonical exercise category domain
 do $$
 begin
   if not exists (select 1 from pg_type where typname = 'exercise_category') then
     create type public.exercise_category as enum (
       'Cardio',
       'Strength',
       'Flexibility',
       'Sports'
     );
   end if;
 end $$;

create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

create table if not exists public.exercises (
  id uuid primary key default gen_random_uuid(),
  user_id bigint not null references public.users(id) on delete cascade,
  name text not null,
  category public.exercise_category not null,
  duration_minutes integer not null check (duration_minutes > 0),
  calories_burned numeric(6,2) not null default 0 check (calories_burned >= 0),
  logged_at timestamptz not null default now(),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists idx_exercises_user_logged_at on public.exercises (user_id, logged_at desc);
create index if not exists idx_exercises_category on public.exercises (category);
create index if not exists idx_exercises_logged_at on public.exercises (logged_at desc);
create index if not exists idx_exercises_created_at on public.exercises (created_at desc);

drop trigger if exists trg_exercises_set_updated_at on public.exercises;
create trigger trg_exercises_set_updated_at
before update on public.exercises
for each row execute function public.set_updated_at();
