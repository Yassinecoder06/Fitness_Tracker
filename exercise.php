<?php
declare(strict_types=1);

require_once __DIR__ . '/backend/exercise_repository.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_ajax_request(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function format_log_date(?string $value): string
{
    if ($value === null || $value === '') {
        return 'Today';
    }

    $date = new DateTimeImmutable($value);
    $today = new DateTimeImmutable('today');
    $yesterday = $today->modify('-1 day');

    if ($date->format('Y-m-d') === $today->format('Y-m-d')) {
        return 'Today';
    }

    if ($date->format('Y-m-d') === $yesterday->format('Y-m-d')) {
        return 'Yesterday';
    }

    return $date->format('M j, Y');
}

function format_calories($value): string
{
    $number = (float)$value;
    if (abs($number - round($number)) < 0.01) {
        return (string)(int)round($number);
    }

    return number_format($number, 1);
}

$categoryMeta = [
    'Cardio' => [
        'icon' => '🏃',
        'class' => 'exercise-category-card__icon--cardio',
        'bg' => 'var(--primary-bg)',
    ],
    'Strength' => [
        'icon' => '🏋️',
        'class' => 'exercise-category-card__icon--strength',
        'bg' => 'var(--accent-orange-bg)',
    ],
    'Calisthenics' => [
        'icon' => '🤸',
        'class' => 'exercise-category-card__icon--calisthenics',
        'bg' => 'var(--accent-green-bg)',
    ],
    'Sports' => [
        'icon' => '⚽',
        'class' => 'exercise-category-card__icon--sports',
        'bg' => 'var(--accent-purple-bg)',
    ],
];

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $created = insert_exercise($_POST);
        $created['display_date'] = format_log_date($created['logged_at'] ?? null);
        $created['calories_display'] = format_calories($created['calories_burned'] ?? 0);

        if (is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'exercise' => $created]);
            exit;
        }

        header('Location: exercise.php');
        exit;
    } catch (Throwable $exception) {
        if (is_ajax_request()) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Unable to save exercise.']);
            exit;
        }

        $error = 'Unable to save exercise.';
    }
}

$categories = EXERCISE_CATEGORIES;
$selectedCategory = normalize_exercise_category($_GET['category'] ?? null);
$categoryCounts = fetch_exercise_library_counts();
$libraryExercises = fetch_exercise_library($selectedCategory);
$recentExercises = fetch_recent_exercises(5);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FitTrack Exercise — Browse exercise categories and log your workouts.">
    <title>FitTrack | Exercise</title>
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
        <div class="navbar__search">
            <svg class="navbar__search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" placeholder="Search food, exercises, goals...">
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
            <div class="navbar__avatar" title="User">JD</div>
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
            <a href="exercise.php" class="sidebar__link active"><svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            <a href="goals.php" class="sidebar__link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <circle cx="12" cy="12" r="6" />
                    <circle cx="12" cy="12" r="2" />
                </svg> Goals</a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <div class="main__header flex-between">
            <div>
                <h1 class="main__title">Exercise</h1>
                <p class="main__subtitle">Browse categories and log your workouts</p>
            </div>
            <button class="btn btn--primary" data-modal="add-exercise-modal">+ Add Exercise</button>
        </div>

        <?php if ($error !== null): ?>
            <div class="card card--flat animate-in" style="padding:16px;color:var(--accent-red);">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <!-- CATEGORY CARDS -->
        <div class="exercise-categories">
            <?php foreach ($categories as $category): ?>
                <?php $meta = $categoryMeta[$category] ?? $categoryMeta['Cardio']; ?>
                <?php $count = $categoryCounts[$category] ?? 0; ?>
                <?php $active = $selectedCategory === $category; ?>
                <a class="exercise-category-card animate-in<?= $active ? ' active' : '' ?>" href="<?= e('exercise.php?category=' . urlencode($category)) ?>" data-category="<?= e($category) ?>">
                    <div class="exercise-category-card__icon <?= e($meta['class']) ?>"><?= e($meta['icon']) ?></div>
                    <div class="exercise-category-card__name"><?= e($category) ?></div>
                    <div class="exercise-category-card__count"><?= e((string)$count) ?> exercises</div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- EXERCISE LIBRARY -->
        <div class="card card--flat animate-in">
            <div class="card__header">
                <div>
                    <h2 class="card__title">Choose Exercises</h2>
                    <p class="card__subtitle">Pick a category first, then log a workout from the list.</p>
                </div>
            </div>
            <?php if ($selectedCategory === null): ?>
                <div class="exercise-library__empty">Select a category above to see exercises.</div>
            <?php elseif (count($libraryExercises) === 0): ?>
                <div class="exercise-library__empty">No exercises found for <?= e($selectedCategory) ?>.</div>
            <?php else: ?>
                <div class="exercise-list">
                    <?php foreach ($libraryExercises as $exercise): ?>
                        <?php $meta = $categoryMeta[$exercise['category']] ?? $categoryMeta['Cardio']; ?>
                        <div class="exercise-list__item">
                            <div class="exercise-list__left">
                                <div class="exercise-list__icon" style="background:<?= e($meta['bg']) ?>">
                                    <?= e($meta['icon']) ?>
                                </div>
                                <div>
                                    <div class="exercise-list__name"><?= e($exercise['name']) ?></div>
                                    <div class="exercise-list__meta"><?= e($exercise['category']) ?></div>
                                    <div class="exercise-list__desc"><?= e($exercise['instructions']) ?></div>
                                </div>
                            </div>
                            <div class="exercise-list__action">
                                <button
                                    class="btn btn--secondary exercise-log-btn"
                                    type="button"
                                    data-exercise-id="<?= e($exercise['id']) ?>"
                                    data-exercise-name="<?= e($exercise['name']) ?>"
                                    data-exercise-category="<?= e($exercise['category']) ?>"
                                >Log</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- RECENT EXERCISES -->
        <div class="card card--flat animate-in">
            <div class="card__header">
                <div>
                    <h2 class="card__title">Recent Exercises</h2>
                    <p class="card__subtitle">Your latest logged activities</p>
                </div>
            </div>
            <div class="exercise-list" id="exercise-list">
                <?php if (count($recentExercises) === 0): ?>
                    <div id="exercise-empty" style="padding:16px;color:var(--gray-500);">
                        No exercises logged yet. Add your first workout.
                    </div>
                <?php endif; ?>
                <?php foreach ($recentExercises as $exercise): ?>
                    <?php $meta = $categoryMeta[$exercise['category']] ?? $categoryMeta['Cardio']; ?>
                    <div class="exercise-list__item">
                        <div class="exercise-list__left">
                            <div class="exercise-list__icon" style="background:<?= e($meta['bg']) ?>">
                                <?= e($meta['icon']) ?>
                            </div>
                            <div>
                                <div class="exercise-list__name"><?= e($exercise['name']) ?></div>
                                <div class="exercise-list__meta">
                                    <?= e($exercise['category']) ?> · <?= e((string)$exercise['duration_minutes']) ?> min ·
                                    <?= e(format_log_date($exercise['logged_at'] ?? null)) ?>
                                </div>
                            </div>
                        </div>
                        <div class="exercise-list__cals"><?= e(format_calories($exercise['calories_burned'])) ?> kcal</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- ADD EXERCISE MODAL -->
    <div class="modal-overlay" id="add-exercise-modal">
        <div class="modal">
            <div class="modal__header">
                <h2 class="modal__title">Add Exercise</h2>
                <button class="modal__close" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg></button>
            </div>
            <form class="modal__body" id="exercise-form" method="post" action="exercise.php">
                <input type="hidden" name="exercise_id" id="exercise-id" value="">
                <div class="form-group">
                    <label class="form-label" for="exercise-name">Exercise Name</label>
                    <input class="form-input" id="exercise-name" name="name" type="text" placeholder="e.g. Running, Push-ups" required maxlength="80">
                </div>
                <div class="form-group">
                    <label class="form-label" for="exercise-category">Category</label>
                    <select class="form-select" id="exercise-category" name="category" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category) ?>"><?= e($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="exercise-duration">Duration (minutes)</label>
                    <input class="form-input" id="exercise-duration" name="duration_minutes" type="number" placeholder="30" min="1" max="600" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="exercise-calories">Calories Burned</label>
                    <input class="form-input" id="exercise-calories" name="calories_burned" type="number" placeholder="200" min="0" max="5000" step="0.1" required>
                </div>
                <div class="modal__footer">
                    <button class="btn btn--secondary modal-cancel" type="button">Cancel</button>
                    <button class="btn btn--primary btn--save" type="submit">Save Exercise</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>

</html>
