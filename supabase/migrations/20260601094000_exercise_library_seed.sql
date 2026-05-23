-- Seed exercise library (original list)

insert into public.exercise_library (name, category, instructions)
values
  ('Brisk Walk', 'Cardio', 'Keep shoulders relaxed, swing arms lightly, and maintain a steady pace.'),
  ('Jogging', 'Cardio', 'Land softly mid-foot, keep a tall posture, and breathe rhythmically.'),
  ('Cycling', 'Cardio', 'Set the seat so knees stay slightly bent at the bottom of each pedal stroke.'),
  ('Jump Rope', 'Cardio', 'Use small wrist circles, keep elbows close, and land on the balls of your feet.'),
  ('Rowing Machine', 'Cardio', 'Drive with legs first, then lean back slightly and pull the handle to the ribs.'),
  ('Stair Climb', 'Cardio', 'Step through the whole foot and keep a steady cadence.'),

  ('Bench Press', 'Strength', 'Keep feet planted, lower the bar to mid-chest, and press with a steady path.'),
  ('Back Squat', 'Strength', 'Brace the core, keep knees tracking toes, and maintain a neutral spine.'),
  ('Deadlift', 'Strength', 'Hinge at the hips, keep the bar close, and lift with a flat back.'),
  ('Overhead Press', 'Strength', 'Squeeze glutes, brace the core, and press the weight overhead without leaning back.'),
  ('Bent-Over Row', 'Strength', 'Hinge forward, keep back flat, and pull elbows toward your hips.'),
  ('Kettlebell Swing', 'Strength', 'Use a hip hinge, snap hips forward, and let the bell float chest high.'),

  ('Push-Up', 'Calisthenics', 'Keep a straight line head to heels and lower chest to elbow height.'),
  ('Pull-Up', 'Calisthenics', 'Start from a dead hang and pull chin above the bar without swinging.'),
  ('Dips', 'Calisthenics', 'Keep shoulders down, elbows close, and lower until upper arms are parallel.'),
  ('Bodyweight Squat', 'Calisthenics', 'Sit hips back, keep chest up, and push knees out over toes.'),
  ('Plank', 'Calisthenics', 'Brace the core and keep hips level while holding a straight line.'),
  ('Mountain Climbers', 'Calisthenics', 'Keep hips low and drive knees forward in a controlled rhythm.'),

  ('Soccer Dribble', 'Sports', 'Use light touches, keep the ball close, and stay on the balls of your feet.'),
  ('Basketball Layups', 'Sports', 'Approach on a controlled pace and finish softly off the glass.'),
  ('Tennis Rally', 'Sports', 'Split step before contact and follow through toward the target.'),
  ('Badminton Clears', 'Sports', 'Rotate the shoulders and finish the swing high and forward.'),
  ('Volleyball Pepper', 'Sports', 'Stay low, keep a platform with forearms, and communicate with partners.'),
  ('Swimming Laps', 'Sports', 'Keep a long body line and exhale steadily underwater.')
on conflict (name, category)
where name is not null
do update set
  instructions = excluded.instructions;
