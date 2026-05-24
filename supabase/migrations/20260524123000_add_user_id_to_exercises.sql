do $$
begin
  if exists (
    select 1
    from information_schema.tables
    where table_schema = 'public'
      and table_name = 'exercises'
  ) then
    alter table public.exercises
      add column if not exists user_id bigint;

    alter table public.exercises
      add constraint exercises_user_id_fkey
      foreign key (user_id) references public.users(id) on delete cascade;

    create index if not exists idx_exercises_user_logged_at
      on public.exercises (user_id, logged_at desc);
  end if;
end $$;
