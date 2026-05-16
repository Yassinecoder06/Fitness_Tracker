INSERT INTO users (name, email, password)
VALUES
('Yassin', 'yassin@test.com', '123456'),
('Ali', 'ali@test.com', '123456'),
('Sara', 'sara@test.com', '123456');
INSERT INTO meals (user_id, food_name, meal_type, calories, protein, carbs, fat, date)
VALUES
(1, 'Omelette', 'Breakfast', 250, 18, 2, 20, CURRENT_DATE),
(1, 'Chicken Rice', 'Lunch', 650, 45, 70, 15, CURRENT_DATE),
(1, 'Apple', 'Snack', 95, 0, 25, 0, CURRENT_DATE);
