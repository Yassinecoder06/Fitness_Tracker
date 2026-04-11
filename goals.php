<?php
require 'config/db_postgres_env.php';

$user_id = 1; // hardcoded until auth is integrated
$pdo = getDBConnection();
$success = false;

// Handle POST - upsert goals
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_weight = intval($_POST['target_weight'] ?? 0);
    $daily_calories = intval($_POST['daily_calories'] ?? 0);
    $weekly_workouts = intval($_POST['weekly_workouts'] ?? 0);

    $stmt = $pdo->prepare("INSERT INTO goals (user_id, target_weight, daily_calories, weekly_workouts)
        VALUES (?, ?, ?, ?)
        ON CONFLICT (user_id) DO UPDATE SET
        target_weight = EXCLUDED.target_weight,
        daily_calories = EXCLUDED.daily_calories,
        weekly_workouts = EXCLUDED.weekly_workouts");
    $stmt->execute([$user_id, $target_weight, $daily_calories, $weekly_workouts]);
    $success = true;
}

// Fetch current goals for pre-fill
$stmt = $pdo->prepare("SELECT * FROM goals WHERE user_id = ?");
$stmt->execute([$user_id]);
$goals = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Goals — FitTrack</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="padding: 32px 0;">
        <h1 class="page-header">My Fitness Goals</h1>

        <form method="POST" action="goals.php" id="goalsForm">
            <div class="grid-2">
                <div class="card">
                    <div class="form-group">
                        <label for="target_weight">Target Weight</label>
                        <input
                            id="target_weight"
                            name="target_weight"
                            type="number"
                            class="form-input"
                            value="<?= htmlspecialchars((string) ($goals['target_weight'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>
                </div>

                <div class="card">
                    <div class="form-group">
                        <label for="daily_calories">Daily Calorie Target</label>
                        <input
                            id="daily_calories"
                            name="daily_calories"
                            type="number"
                            class="form-input"
                            value="<?= htmlspecialchars((string) ($goals['daily_calories'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>
                </div>

                <div class="card">
                    <div class="form-group">
                        <label for="weekly_workouts">Weekly Workout Target</label>
                        <input
                            id="weekly_workouts"
                            name="weekly_workouts"
                            type="number"
                            class="form-input"
                            value="<?= htmlspecialchars((string) ($goals['weekly_workouts'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>
                </div>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn" id="saveGoalsBtn">Save Goals</button>
            </div>
        </form>

        <div class="toast" id="successToast">✅ Goals saved successfully!</div>
    </div>

    <script src="assets/js/goals.js"></script>

    <?php if ($success): ?>
    <script>
        const toast = document.getElementById('successToast');
        if (toast) {
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    </script>
    <?php endif; ?>
</body>
</html>
