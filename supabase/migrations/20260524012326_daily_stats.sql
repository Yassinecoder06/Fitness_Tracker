CREATE TABLE daily_stats (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id INT NOT NULL,
  date DATE NOT NULL,
  water_glasses INT DEFAULT 0,
  calories_consumed INT DEFAULT 0
);