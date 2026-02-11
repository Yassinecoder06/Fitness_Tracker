# FitTrack

FitTrack is a fitness web app concept that evolves across course phases, starting with a static site and growing into a full-stack app.

## Core Features

- Set goals: weight goal, workout frequency, target calories.
- Plan workouts: list of exercises (push-ups, squats, running) with sets/reps/time.
- Track progress: log workouts and see progress over time.
- Basic nutrition: simple daily calorie target and what the user ate.

## Phase 1 - HTML and CSS

Pages:

- Home
- Workouts
- Progress
- Nutrition
- About
- Contact

Focus:

- Layout and responsive design
- Navbar
- Cards for workouts
- Simple progress section (plain text or tables)

## Phase 2 - JavaScript

Features:

- BMI calculator (height + weight -> result)
- Simple workout timer (start/stop, countdown)
- Form validation (goals, contact form)
- Local storage for basic data (user goals, last workout)

## Phase 3 - PHP

Server-side:

- User signup/login (sessions)
- Save workouts and logs in a database
- Per-user data on pages
- Contact form that sends or saves messages

## Phase 4 - Symfony

Full app structure:

- Entities: User, Workout, Exercise, WorkoutLog, Goal
- CRUD for workouts and logs
- Dashboard with stats (total workouts, total time)
- Optional API endpoint for JSON stats

## Next Step

Choose one to start with:

1. Sketch the exact pages and sections for Phase 1
2. Design a simple database structure for later (PHP/Symfony)
