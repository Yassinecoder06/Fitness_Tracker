<?php
require_once __DIR__ . '/backend/bootstrap.php';
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';

$pdo = get_pdo();
ensure_authenticated($pdo, '/goals.php');

$user_id = $_SESSION['user_id'];
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

$curr_weight = $goals['target_weight'] ?? '';
$curr_cals = $goals['daily_calories'] ?? '';
$curr_workouts = $goals['weekly_workouts'] ?? '';
$name = get_user_name();
$avatarInitials = get_user_initials($name);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FitTrack Goals — Set your weight, calorie, and workout targets.">
    <title>FitTrack | Goals</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar__left">
            <button class="navbar__hamburger" aria-label="Toggle menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <line x1="3" y1="6" x2="21" y2="6" />
                    <line x1="3" y1="12" x2="21" y2="12" />
                    <line x1="3" y1="18" x2="21" y2="18" />
                </svg>
            </button>
            <a href="index.php" class="navbar__logo">
                <div class="navbar__logo-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 20V10" />
                        <path d="M12 20V4" />
                        <path d="M6 20v-6" />
                    </svg></div>
                FitTrack
            </a>
        </div>
        <div class="navbar__right">
            <button class="navbar__icon-btn" aria-label="Notifications">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                </svg>
                <span class="navbar__badge"></span>
            </button>
            <div class="navbar__avatar" title="<?= htmlspecialchars((string)$name) ?>">
                <?= htmlspecialchars($avatarInitials) ?>
            </div>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <div class="sidebar-overlay"></div>
    <aside class="sidebar">
        <nav class="sidebar__nav">
            <span class="sidebar__section-title">Menu</span>
            <a href="index.php" class="sidebar__link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                </svg> Dashboard</a>
            <a href="diary.php" class="sidebar__link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                </svg> Diary</a>
            <a href="food.php" class="sidebar__link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                    <line x1="6" y1="1" x2="6" y2="4" />
                    <line x1="10" y1="1" x2="10" y2="4" />
                    <line x1="14" y1="1" x2="14" y2="4" />
                </svg> Food</a>
            <a href="exercise.php" class="sidebar__link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.4 14.4L9.6 9.6" />
                    <path
                        d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767-1.768a2 2 0 1 1-2.829-2.829l-1.767-1.767a2 2 0 1 1-2.829-2.829L4.869 7.697a2 2 0 1 1 2.828-2.829l1.768 1.768a2 2 0 1 1 2.828 2.829l1.768 1.767a2 2 0 1 1 2.828 2.829l1.768 1.767a2 2 0 1 1-2.828 2.829z" />
                </svg> Exercise</a>
            <span class="sidebar__section-title">Analytics</span>
            <a href="progress.php" class="sidebar__link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10" />
                    <line x1="12" y1="20" x2="12" y2="4" />
                    <line x1="6" y1="20" x2="6" y2="14" />
                </svg> Progress</a>
            <a href="goals.php" class="sidebar__link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <circle cx="12" cy="12" r="6" />
                    <circle cx="12" cy="12" r="2" />
                </svg> Goals</a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <div class="main__header">
            <h1 class="main__title">Goals</h1>
            <p class="main__subtitle">Set and manage your fitness targets</p>
        </div>

        <form method="POST" action="goals.php" id="goalsForm">
            <div class="goals-grid">
                <div class="goal-card animate-in" style="grid-column: 1 / -1;">
                    <div class="goal-card__icon" style="background: var(--primary-bg)">🎯</div>
                    <h2 class="goal-card__title">Your Goal Plan</h2>
                    <p class="goal-card__desc">Set your target weight, daily calories, and weekly workouts in one place.</p>
                    <div class="goal-card__current">
                        <span class="goal-card__current-label">Current targets</span>
                        <span class="goal-card__current-value">
                            <?= $curr_weight ? $curr_weight . ' kg' : 'Weight: Not set' ?> ·
                            <?= $curr_cals ? number_format($curr_cals) . ' kcal' : 'Calories: Not set' ?> ·
                            <?= $curr_workouts ? $curr_workouts . 'x / week' : 'Workouts: Not set' ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="target_weight">Target Weight (kg)</label>
                        <input class="form-input" type="number" id="target_weight" name="target_weight" placeholder="75" value="<?= htmlspecialchars((string)$curr_weight) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="daily_calories">Daily Calorie Goal (kcal)</label>
                        <input class="form-input" type="number" id="daily_calories" name="daily_calories" placeholder="2700" value="<?= htmlspecialchars((string)$curr_cals) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="weekly_workouts">Workouts Per Week</label>
                        <input class="form-input" type="number" id="weekly_workouts" name="weekly_workouts" placeholder="5" value="<?= htmlspecialchars((string)$curr_workouts) ?>" min="1" max="7">
                    </div>

                    <button type="submit" class="btn btn--primary btn--block">Save Goal</button>
                </div>
            </div>
        </form>
    </main>

    <script src="js/main.js"></script>

    <?php if ($success): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof showToast === 'function') {
                showToast('✅ Goals saved successfully!');
            }
        });
    </script>
    <?php endif; ?>
</body>

</html>
