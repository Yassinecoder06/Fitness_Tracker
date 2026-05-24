<?php
require_once __DIR__ . '/backend/bootstrap.php';
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';

$pdo = get_pdo();
ensure_authenticated($pdo, '/progress.php');

$user_id = $_SESSION['user_id'];
$name = $_SESSION['user_name'] ?? 'User';

$avatarInitials = 'U';
$nameParts = preg_split('/\s+/', trim((string)$name));
if (!empty($nameParts)) {
  $first = $nameParts[0] ?? '';
  $last = $nameParts[count($nameParts) - 1] ?? '';
  $avatarInitials = strtoupper(substr($first, 0, 1) . substr($last, 0, 1));
}

$weight_error = null;
$weight_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['weight'])) {
  $weight = filter_var($_POST['weight'], FILTER_VALIDATE_FLOAT);
  $logDate = $_POST['log_date'] ?? date('Y-m-d');

  if ($weight === false || $weight <= 0) {
    $weight_error = 'Please enter a valid weight.';
  } else {
    $stmt = $pdo->prepare(
      'INSERT INTO weight_logs (user_id, weight, date) VALUES (?, ?, ?)'
    );
    $stmt->execute([$user_id, $weight, $logDate]);
    $weight_success = true;

    header('Location: progress.php');
    exit;
  }
}

// Fetch current goals
$stmt = $pdo->prepare("SELECT * FROM goals WHERE user_id = ?");
$stmt->execute([$user_id]);
$goals = $stmt->fetch() ?: [];
$target_weight = $goals['target_weight'] ?? 0;
$daily_calories = $goals['daily_calories'] ?? 0;
$weekly_workouts = $goals['weekly_workouts'] ?? 0;

// Fetch last 7 weight logs
$stmt = $pdo->prepare("
    SELECT weight, date 
    FROM weight_logs 
    WHERE user_id = ? 
    ORDER BY date ASC 
    LIMIT 7
");
$stmt->execute([$user_id]);
$weight_logs = $stmt->fetchAll();

// Get current weight
$current_weight = !empty($weight_logs) ? end($weight_logs)['weight'] : null;

// Calories history from meals (last 7 days)
$stmt = $pdo->prepare("
  select date, sum(calories) as total_calories
  from meals
  where user_id = ?
    and date >= current_date - interval '6 days'
  group by date
  order by date asc
");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll();

$totalsByDate = [];
foreach ($rows as $row) {
  $totalsByDate[$row['date']] = (int)($row['total_calories'] ?? 0);
}

$calorie_history = [];
for ($i = 6; $i >= 0; $i--) {
  $day = date('Y-m-d', strtotime("-{$i} days"));
  $calorie_history[] = [
    'date' => $day,
    'total' => $totalsByDate[$day] ?? 0,
  ];
}

$avg_cals = count($calorie_history) > 0
  ? array_sum(array_column($calorie_history, 'total')) / count($calorie_history)
  : 0;

// Calculate progress percentages
$weight_progress_pct = 0;
if ($target_weight > 0 && $current_weight) {
    // Assuming starting weight was e.g. target + 10 for display purposes
    $start_weight = $target_weight + 10;
    if ($current_weight <= $target_weight) $weight_progress_pct = 100;
    else $weight_progress_pct = min(100, max(0, (($start_weight - $current_weight) / ($start_weight - $target_weight)) * 100));
}

$workout_progress_pct = $weekly_workouts > 0 ? (5 / $weekly_workouts) * 100 : 0;
$workout_progress_pct = min(100, $workout_progress_pct);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="FitTrack Progress — Monitor your weight, calories, and fitness trends.">
  <title>FitTrack | Progress</title>
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
        <div class="navbar__logo-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
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
      <a href="progress.php" class="sidebar__link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
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
    <div class="main__header">
      <h1 class="main__title">Progress</h1>
      <p class="main__subtitle">Monitor your fitness trends and achievements</p>
    </div>

    <!-- STATS OVERVIEW -->
    <div class="stats-grid">
      <div class="stat-card animate-in">
        <div class="stat-card__icon stat-card__icon--blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
          </svg></div>
        <div class="stat-card__info">
          <div class="stat-card__label">Current Weight</div>
          <div class="stat-card__value"><?= $current_weight ? $current_weight : '--' ?> <span style="font-size:16px;color:var(--gray-500)">kg</span></div>
          <div class="stat-card__change stat-card__change--down">Target: <?= $target_weight ?: '--' ?> kg</div>
        </div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card__icon stat-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
          </svg></div>
        <div class="stat-card__info">
          <div class="stat-card__label">Avg Daily Calories</div>
          <div class="stat-card__value" data-count="<?= round($avg_cals) ?>">0</div>
          <div class="stat-card__change stat-card__change--up">Target: <?= $daily_calories ?: '--' ?> kcal</div>
        </div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card__icon stat-card__icon--orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
          </svg></div>
        <div class="stat-card__info">
          <div class="stat-card__label">Workouts This Week</div>
          <div class="stat-card__value" data-count="5">0</div>
          <div class="stat-card__change stat-card__change--up">Target: <?= $weekly_workouts ?: '--' ?>x</div>
        </div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card__icon stat-card__icon--purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2">
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 6 12 12 16 14" />
          </svg></div>
        <div class="stat-card__info">
          <div class="stat-card__label">Active Streak</div>
          <div class="stat-card__value" data-count="12">0</div>
          <div class="stat-card__change stat-card__change--up">🔥 days</div>
        </div>
      </div>
    </div>

    <!-- CHART CARDS -->
    <div class="progress-grid">
      <!-- Weight Progress -->
      <div class="card card--flat animate-in">
        <div class="card__header">
          <div>
            <h2 class="card__title">⚖️ Weight Progress</h2>
            <p class="card__subtitle">Last logs</p>
          </div>
        </div>
        <form method="post" action="progress.php" style="margin-bottom:16px;">
          <div class="form-group" style="max-width:280px;">
            <label class="form-label" for="weight">Current Weight (kg)</label>
            <input class="form-input" type="number" step="0.1" min="1" id="weight" name="weight" placeholder="72.5" required>
          </div>
          <div class="form-group" style="max-width:280px;">
            <label class="form-label" for="log_date">Date</label>
            <input class="form-input" type="date" id="log_date" name="log_date" value="<?= date('Y-m-d') ?>">
          </div>
          <?php if ($weight_error): ?>
            <div style="color: var(--accent-red); margin-bottom: 8px;">
              <?= htmlspecialchars($weight_error) ?>
            </div>
          <?php endif; ?>
          <button class="btn btn--primary" type="submit">Add Weight</button>
        </form>
        <div class="simple-chart">
        <?php if (empty($weight_logs)): ?>
             <p style="color:var(--gray-500); padding: 20px 0;">No weight data logged yet.</p>
        <?php else: 
            $max_w = max(array_column($weight_logs, 'weight'));
            foreach ($weight_logs as $log): 
                $h = ($log['weight'] / max(1, $max_w)) * 100;
        ?>
          <div class="simple-chart__bar-wrapper">
            <span class="simple-chart__value"><?= $log['weight'] ?></span>
            <div class="simple-chart__bar simple-chart__bar--blue" data-height="<?= round($h) ?>" style="height:0%"></div>
            <span class="simple-chart__label" style="font-size: 10px;"><?= date('M d', strtotime($log['date'])) ?></span>
          </div>
        <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- Calories History -->
      <div class="card card--flat animate-in">
        <div class="card__header">
          <div>
            <h2 class="card__title">🔥 Calories History</h2>
            <p class="card__subtitle">Last 7 days</p>
          </div>
        </div>
        <div class="simple-chart">
        <?php
            $max_c = max(array_column($calorie_history, 'total')) ?: 1;
            foreach ($calorie_history as $entry):
                $cal = (int)$entry['total'];
                $h = ($cal / $max_c) * 100;
        ?>
          <div class="simple-chart__bar-wrapper">
            <span class="simple-chart__value"><?= $cal ?></span>
            <div class="simple-chart__bar simple-chart__bar--green" data-height="<?= round($h) ?>" style="height:0%"></div>
            <span class="simple-chart__label"><?= date('D', strtotime($entry['date'])) ?></span>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- PROGRESS BARS -->
    <div class="card card--flat animate-in">
      <div class="card__header">
        <div>
          <h2 class="card__title">📊 Monthly Goals Progress</h2>
          <p class="card__subtitle"><?= date('F Y') ?></p>
        </div>
      </div>
      <div class="progress-bar-group">
        <div class="progress-bar__header">
          <span class="progress-bar__label">Weight Loss Progress</span>
          <span class="progress-bar__value"><?= $current_weight ?: '--' ?> / <?= $target_weight ?: '--' ?> kg</span>
        </div>
        <div class="progress-bar">
          <div class="progress-bar__fill progress-bar__fill--blue" data-width="<?= round($weight_progress_pct) ?>"></div>
        </div>
      </div>
      <div class="progress-bar-group">
        <div class="progress-bar__header">
          <span class="progress-bar__label">Workout Frequency</span>
          <span class="progress-bar__value">5 / <?= $weekly_workouts ?: '--' ?> sessions</span>
        </div>
        <div class="progress-bar">
          <div class="progress-bar__fill progress-bar__fill--green" data-width="<?= round($workout_progress_pct) ?>"></div>
        </div>
      </div>
    </div>
  </main>

  <script src="js/main.js"></script>
</body>

</html>
