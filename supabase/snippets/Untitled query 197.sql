INSERT INTO public.exercises (name, category, duration_minutes, calories_burned, logged_at)
VALUES
('Running (moderate pace)', 'Cardio', 30, 320.50, now() - interval '1 day'),
('Cycling', 'Cardio', 45, 400.00, now() - interval '2 days'),
('Jump Rope', 'Cardio', 15, 180.75, now() - interval '3 days'),

('Push Ups', 'Strength', 20, 150.00, now() - interval '1 day'),
('Weight Lifting (Upper Body)', 'Strength', 50, 350.00, now() - interval '2 days'),
('Squats', 'Strength', 25, 200.00, now() - interval '4 days'),



('Football Match', 'Sports', 60, 600.00, now() - interval '5 days'),
('Basketball Game', 'Sports', 50, 520.00, now() - interval '2 days'),
('Tennis Practice', 'Sports', 30, 300.00, now() - interval '1 day');