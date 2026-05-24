<?php

require_once __DIR__ . '/backend/bootstrap.php';
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';

$pdo = get_pdo();
ensure_authenticated($pdo, '/diary.php');

$pdo->exec("SET TIME ZONE 'Africa/Tunis';");
$user_id = $_SESSION['user_id'];

$selected_date = $_GET['date'] ?? date('Y-m-d');
$display_date = date('l, F j Y', strtotime($selected_date));
$prev_date = date('Y-m-d', strtotime($selected_date . ' -1 day'));
$next_date = date('Y-m-d', strtotime($selected_date . ' +1 day'));
$meal_types = ['Breakfast','Lunch','Snack','Dinner','Pre-Workout','Post-Workout'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? null;

    
    if ($action === 'add_meal') {
        $stmt = $pdo->prepare("
            INSERT INTO meals (user_id, food_name, meal_type, calories, protein, carbs, fat, date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $_POST['food_name'],
            $_POST['meal_type'],
            $_POST['calories'],
            $_POST['protein'],
            $_POST['carbs'],
            $_POST['fat'],
            $_POST['date']
        ]);

        echo json_encode(['success' => true]);
        exit;
    }
    if ($action === 'delete_meal') {
      $stmt = $pdo->prepare("DELETE FROM meals WHERE id = ? AND user_id = ?");
      $stmt->execute([$_POST['id'], $user_id]);

        echo json_encode(['success' => true]);
        exit;
    }

    
    if ($action === 'add_exercise') {
        $stmt = $pdo->prepare("
            INSERT INTO exercise_logs (user_id, exercise_name, duration, calories_burned, date)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $_POST['exercise_name'],
            $_POST['duration'],
            $_POST['calories_burned'],
            $_POST['date']
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

    
    if ($action === 'delete_exercise') {
      $stmt = $pdo->prepare("DELETE FROM exercise_logs WHERE id = ? AND user_id = ?");
      $stmt->execute([$_POST['id'], $user_id]);

        echo json_encode(['success' => true]);
        exit;
    }
   if ($action === 'save_notes') {

    $stmt = $pdo->prepare("
        INSERT INTO diary_notes (user_id, date, note)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $_POST['date'],
        $_POST['notes']
    ]);

    echo json_encode(['success' => true]);
    exit;
}

    if ($action === 'update_water') {

    $stmt = $pdo->prepare("
        INSERT INTO water_intake (user_id, date, glasses)
        VALUES (?, ?, ?)
        ON CONFLICT (user_id, date)
        DO UPDATE SET glasses = EXCLUDED.glasses
    ");

    $stmt->execute([
        $user_id,
        $_POST['date'],
        $_POST['water']
    ]);

    echo json_encode(['success' => true]);
    exit;
}
}

$stmt = $pdo->prepare("
    SELECT * FROM meals
    WHERE user_id = ? AND date = ?
");
$stmt->execute([$user_id, $selected_date]);
$meals = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT * FROM exercise_logs
    WHERE user_id = ? AND date = ?
");
$stmt->execute([$user_id, $selected_date]);
$exercises = $stmt->fetchAll();
$stmt = $pdo->prepare("
    SELECT note, created_at
    FROM diary_notes
    WHERE user_id = ?
    AND date = ?
    ORDER BY created_at DESC
");

$stmt->execute([$user_id, $selected_date]);

$notes = $stmt->fetchAll();
$stmt = $pdo->prepare("
    SELECT glasses
    FROM water_intake
    WHERE user_id = ?
    AND date = ?
");

$stmt->execute([$user_id, $selected_date]);

$water = $stmt->fetch();
$water_glasses = $water['glasses'] ?? 0;

$total_cal = array_sum(array_column($meals,'calories')) ?? 0;
$total_pro = array_sum(array_column($meals,'protein')) ?? 0;
$total_carbs = array_sum(array_column($meals,'carbs')) ?? 0;
$total_fat = array_sum(array_column($meals,'fat')) ?? 0;

$total_burned = array_sum(array_column($exercises,'calories_burned')) ?? 0;

$goal_cal = 2200;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FitTrack Diary — Log your daily food, exercises, water intake, and notes.">
    <title>FitTrack | Diary</title>
    <link rel="stylesheet" href="css/navbar.css">
     <link rel="stylesheet" href="css/diary.css">
     <!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
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
                <div class="navbar__logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 20V10" />
                        <path d="M12 20V4" />
                        <path d="M6 20v-6" />
                    </svg>
                </div>
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
            <a href="index.php" class="sidebar__link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                </svg>
                Dashboard
            </a>
            <a href="diary.php" class="sidebar__link active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                </svg>
                Diary
            </a>
            <a href="food.php" class="sidebar__link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                    <line x1="6" y1="1" x2="6" y2="4" />
                    <line x1="10" y1="1" x2="10" y2="4" />
                    <line x1="14" y1="1" x2="14" y2="4" />
                </svg>
                Food
            </a>
            <a href="exercise.php" class="sidebar__link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M14.4 14.4L9.6 9.6" />
                    <path
                        d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767-1.768a2 2 0 1 1-2.829-2.829l-1.767-1.767a2 2 0 1 1-2.829-2.829L4.869 7.697a2 2 0 1 1 2.828-2.829l1.768 1.768a2 2 0 1 1 2.828 2.829l1.768 1.767a2 2 0 1 1 2.828 2.829l1.768 1.767a2 2 0 1 1-2.828 2.829z" />
                </svg>
                Exercise
            </a>
            <a href="logout.php" class="sidebar__link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                  <path d="M16 16l4-4m0 0l-4-4m4 4H9"/>
                </svg>
            Logout <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
             </a>
            <span class="sidebar__section-title">Analytics</span>
            <a href="progress.php" class="sidebar__link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10" />
                    <line x1="12" y1="20" x2="12" y2="4" />
                    <line x1="6" y1="20" x2="6" y2="14" />
                </svg>
                Progress
            </a>
            <a href="goals.php" class="sidebar__link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <circle cx="12" cy="12" r="6" />
                    <circle cx="12" cy="12" r="2" />
                </svg>
                Goals
            </a>
        </nav>
    </aside>
<!--
-->
    <!-- DIARY HEADER -->
<header class="diary-header">
  <div class="container-xl">
    <div class="row align-items-center gy-3" style="position:relative;z-index:1">
      <div class="col-md-6">
        <p class="mb-1" style="opacity:.75;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em">Daily Diary</p>
        <h1 class="mb-0" style="font-size:1.7rem;font-weight:800;letter-spacing:-.5px"><?= $display_date ?></h1>
      </div>
      <div class="col-md-6 d-flex justify-content-md-end">
        <div class="date-nav">
          <a href="?date=<?= $prev_date ?>" class="date-nav-btn"><i class="bi bi-chevron-left"></i></a>
          <input type="date" id="datePicker" value="<?= $selected_date ?>"
            onchange="window.location='?date='+this.value"
            style="background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.35);
                   color:#fff;border-radius:var(--r-sm);padding:.35rem .7rem;
                   font-family:var(--font-base);font-size:.875rem;font-weight:600;cursor:pointer;">
          <a href="?date=<?= $next_date ?>" class="date-nav-btn"><i class="bi bi-chevron-right"></i></a>
          <a href="?date=<?= date('Y-m-d') ?>" class="date-nav-btn date-today-btn">Today</a>
        </div>
      </div>
    </div>
  </div>
</header>
 

<!-- BODY -->
<div class="diary-body">
<div class="container-xl">
 
  <!-- Summary Row -->
  <div class="summary-grid mb-4">
    <div class="summary-card">
      <div class="summary-value" style="color:var(--primary)"><?= number_format($total_cal) ?></div>
      <div class="summary-label">Eaten</div>
      <div class="progress-bar-wrap"><div class="progress-bar" style="width:<?= min(100,round($total_cal/$goal_cal*100)) ?>%;background:var(--primary)"></div></div>
      <div class="summary-sub"><?= number_format($goal_cal-$total_cal) ?> kcal left</div>
    </div>
    <div class="summary-card">
      <div class="summary-value" style="color:var(--accent-green)"><?= number_format($total_burned) ?></div>
      <div class="summary-label">Burned</div>
      <div class="progress-bar-wrap"><div class="progress-bar" style="width:<?= min(100,round($total_burned/800*100)) ?>%;background:var(--accent-green)"></div></div>
      <div class="summary-sub">Goal 800 kcal</div>
    </div>
    <div class="summary-card">
      <?php $net=$total_cal-$total_burned; ?>
      <div class="summary-value" style="color:var(--accent-orange)"><?= number_format($net) ?></div>
      <div class="summary-label">Net Calories</div>
      <div class="progress-bar-wrap"><div class="progress-bar" style="width:<?= min(100,round($net/$goal_cal*100)) ?>%;background:var(--accent-orange)"></div></div>
      <div class="summary-sub">Eaten − Burned</div>
    </div>
    <div class="summary-card">
      <div class="summary-value" id="waterGlasses" style="color:var(--accent-teal)"><?= $water_glasses ?><small style="font-size:1rem;color:var(--gray-400)">/8</small></div>
      <div class="summary-label">Water</div>
      <div class="progress-bar-wrap"><div class="progress-bar" style="width:<?= $water_glasses/8*100 ?>%;background:var(--accent-teal)"></div></div>
      <div class="summary-sub" id="bilanwater"><?= 8-$water_glasses ?> glasses to go</div>
    </div>
  </div>
 
  <div class="row g-4">
 
    <!-- LEFT -->
    <div class="col-lg-8">
 
      <!-- Meals Card -->
      <div class="ft-card mb-4">
        <div class="ft-card-header">
          <h2 class="ft-card-title"><i class="bi bi-egg-fried" style="color:var(--accent-orange)"></i> Food Log</h2>
          <button class="btn btn-add btn-add-meal" data-bs-toggle="modal" data-bs-target="#addMealModal">
            <i class="bi bi-plus-lg me-1"></i>Add Meal
          </button>
        </div>
        <div class="ft-card-body">
          <?php
          $type_styles=[
            'Breakfast'=>['bg'=>'#FFFBEB','color'=>'#D97706','icon'=>'☀️'],
            'Lunch'    =>['bg'=>'#EFF6FF','color'=>'#2563EB','icon'=>'🌤️'],
            'Snack'    =>['bg'=>'#ECFDF5','color'=>'#059669','icon'=>'🍎'],
            'Dinner'   =>['bg'=>'#F5F3FF','color'=>'#7C3AED','icon'=>'🌙'],
            'Pre-Workout'=>['bg'=>'#FFF7ED','color'=>'#EA580C','icon'=>'⚡'],
            'Post-Workout'=>['bg'=>'#FDF2F8','color'=>'#DB2777','icon'=>'💪'],
          ];
          $grouped=[];
          foreach($meals as $m) $grouped[$m['meal_type']][]=$m;
          foreach($grouped as $type=>$items):
            $ts=$type_styles[$type]??['bg'=>'var(--gray-100)','color'=>'var(--gray-600)','icon'=>'🍽'];
          ?>
          <div class="mb-1">
            <div class="d-flex align-items-center gap-2 mt-2 mb-1">
              <span class="meal-badge" style="background:<?=$ts['bg']?>;color:<?=$ts['color']?>"><?=$ts['icon']?> <?=$type?></span>
              <span style="font-size:.72rem;color:var(--gray-400);font-weight:600"><?=array_sum(array_column($items,'calories'))?> kcal</span>
            </div>
            <?php foreach($items as $m): ?>
            <div class="diary-entry">
              <div class="entry-icon" style="background:<?=$ts['bg']?>"><?=$ts['icon']?></div>
              <div>
                <div class="entry-name"><?=htmlspecialchars($m['food_name'])?></div>
                <div class="entry-meta">P <?=$m['protein']?>g · C <?=$m['carbs']?>g · F <?=$m['fat']?>g</div>
              </div>
              <div class="entry-cal">
                <div class="cal-value"><?=$m['calories']?></div>
                <div class="cal-unit">kcal</div>
              </div>
              <button class="delete-btn" onclick="deleteMeal(<?=$m['id']?>)"><i class="bi bi-trash3"></i></button>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endforeach; ?>
          <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top:2px solid var(--gray-100)">
            <span style="font-size:.875rem;font-weight:700;color:var(--gray-600)">Total Intake</span>
            <span style="font-size:1.1rem;font-weight:800;font-family:var(--font-mono)"><?=$total_cal?> kcal</span>
          </div>
        </div>
      </div>
 
      <!-- Exercise Card -->
      <div class="ft-card mb-4">
        <div class="ft-card-header">
          <h2 class="ft-card-title"><i class="bi bi-lightning-fill" style="color:var(--accent-green)"></i> Exercise Log</h2>
          <button class="btn btn-add btn-add-exercise" data-bs-toggle="modal" data-bs-target="#addExerciseModal">
            <i class="bi bi-plus-lg me-1"></i>Add Exercise
          </button>
        </div>
        <div class="ft-card-body">
          <?php foreach($exercises as $ex): ?>
          <div class="diary-entry">
            <div class="entry-icon" style="background:var(--accent-green-bg)">🏃</div>
            <div>
              <div class="entry-name"><?=htmlspecialchars($ex['exercise_name'])?></div>
              <div class="entry-meta"><i class="bi bi-clock me-1"></i><?=$ex['duration']?> min</div>
            </div>
            <div class="entry-cal">
              <div class="cal-value" style="color:var(--accent-green)">−<?=$ex['calories_burned']?></div>
              <div class="cal-unit">kcal</div>
            </div>
            <button class="delete-btn" onclick="deleteExercise(<?=$ex['id']?>)"><i class="bi bi-trash3"></i></button>
          </div>
          <?php endforeach; ?>
          <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top:2px solid var(--gray-100)">
            <span style="font-size:.875rem;font-weight:700;color:var(--gray-600)">Total Burned</span>
            <span style="font-size:1.1rem;font-weight:800;font-family:var(--font-mono);color:var(--accent-green)">−<?=$total_burned?> kcal</span>
          </div>
        </div>
      </div>
 
      <!-- Notes Card -->
      <div class="ft-card">
        <div class="ft-card-header">
          <h2 class="ft-card-title"><i class="bi bi-pencil-square" style="color:var(--accent-purple)"></i> Daily Notes</h2>
          <button class="btn btn-add" style="background:var(--accent-purple-bg);color:var(--accent-purple);border:none" onclick="saveNotes()">
            <i class="bi bi-floppy me-1"></i>Save
          </button>
        </div>
        <div class="ft-card-body">
          <textarea class="notes-area" id="notesArea" placeholder="How are you feeling today? Any observations about your meals or workouts?"></textarea>
        </div>
        <div class="notes-container">
    <?php if (!empty($notes)): ?>
    <?php foreach ($notes as $n): ?>
        <div class="note-item card mb-2 p-2 shadow-sm">
            
            <div class="note-text">
                <?= htmlspecialchars($n['note']) ?>
            </div>

            <div class="note-time text-muted small">
                <?= date('H:i', strtotime($n['created_at'])) ?>
            </div>

        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-muted small">No notes for this day</div>
<?php endif; ?>
</div>
      </div>
 
    </div><!-- /col-lg-8 -->
 
    <!-- RIGHT -->
    <div class="col-lg-4">
 
      <!-- Macros Card -->
      <div class="ft-card mb-4">
        <div class="ft-card-header">
          <h2 class="ft-card-title"><i class="bi bi-pie-chart-fill" style="color:var(--primary)"></i> Macros</h2>
          <span class="net-pill" style="background:var(--primary-bg);color:var(--primary-dark)"><?=$total_cal?> kcal</span>
        </div>
        <div class="ft-card-body">
          <div class="macro-row">
            <span class="macro-label" style="color:var(--primary)">Protein</span>
            <div class="flex-grow-1"><div class="progress-bar-wrap"><div class="progress-bar" style="width:<?=min(100,round($total_pro/150*100))?>%;background:var(--primary)"></div></div></div>
            <span class="macro-val"><?=$total_pro?>g</span>
          </div>
          <div class="macro-row">
            <span class="macro-label" style="color:var(--accent-orange)">Carbs</span>
            <div class="flex-grow-1"><div class="progress-bar-wrap"><div class="progress-bar" style="width:<?=min(100,round($total_carbs/250*100))?>%;background:var(--accent-orange)"></div></div></div>
            <span class="macro-val"><?=$total_carbs?>g</span>
          </div>
          <div class="macro-row mb-0">
            <span class="macro-label" style="color:var(--accent-purple)">Fat</span>
            <div class="flex-grow-1"><div class="progress-bar-wrap"><div class="progress-bar" style="width:<?=min(100,round($total_fat/70*100))?>%;background:var(--accent-purple)"></div></div></div>
            <span class="macro-val"><?=$total_fat?>g</span>
          </div>
 
          <!-- Donut chart (SVG CSS) -->
          <?php
          $tot=$total_pro*4+$total_carbs*4+$total_fat*9;
          $pP=$tot?round($total_pro*4/$tot*100):33;
          $pC=$tot?round($total_carbs*4/$tot*100):33;
          $pF=$tot?100-$pP-$pC:34;
          ?>
          <div class="d-flex flex-column align-items-center mt-4">
            <svg width="120" height="120" viewBox="0 0 42 42" style="transform:rotate(-90deg)">
              <circle cx="21" cy="21" r="15.915" fill="none" stroke="var(--gray-100)" stroke-width="5.5"/>
              <circle cx="21" cy="21" r="15.915" fill="none" stroke="var(--primary)" stroke-width="5.5"
                stroke-dasharray="<?=$pP?> <?=100-$pP?>" stroke-dashoffset="0"/>
              <circle cx="21" cy="21" r="15.915" fill="none" stroke="var(--accent-orange)" stroke-width="5.5"
                stroke-dasharray="<?=$pC?> <?=100-$pC?>" stroke-dashoffset="-<?=$pP?>"/>
              <circle cx="21" cy="21" r="15.915" fill="none" stroke="var(--accent-purple)" stroke-width="5.5"
                stroke-dasharray="<?=$pF?> <?=100-$pF?>" stroke-dashoffset="-<?=$pP+$pC?>"/>
            </svg>
            <div class="d-flex gap-3 mt-2 flex-wrap justify-content-center">
              <span style="font-size:.7rem;font-weight:700;color:var(--primary)">● P <?=$pP?>%</span>
              <span style="font-size:.7rem;font-weight:700;color:var(--accent-orange)">● C <?=$pC?>%</span>
              <span style="font-size:.7rem;font-weight:700;color:var(--accent-purple)">● F <?=$pF?>%</span>
            </div>
          </div>
        </div>
      </div>
 
      <!-- Water Tracker -->
      <div class="ft-card mb-4">
        <div class="ft-card-header">
          <h2 class="ft-card-title"><i class="bi bi-droplet-fill" style="color:var(--accent-teal)"></i> Water</h2>
          <span class="water-count" id="waterCount"><?=$water_glasses?></span>
        </div>
        <div class="ft-card-body text-center">
          <p style="font-size:.75rem;color:var(--gray-400);margin-bottom:.9rem">Tap glasses to track &middot; Goal: 8 / day</p>
          <div class="water-grid" id="waterGrid">
            <?php for($i=1;$i<=8;$i++): ?>
            <div class="glass <?=$i<=$water_glasses?'filled':''?>" onclick="toggleGlass(this)" data-index="<?=$i?>">
              <div class="glass-fill"></div>
            </div>
            <?php endfor; ?>
          </div>
          <p class="mt-3 mb-0" style="font-size:.75rem;color:var(--gray-400)">
            <span id="waterLitres"><?=number_format($water_glasses*.25,2)?></span> L / 2.00 L
          </p>
        </div>
      </div>
 
      <!-- Calorie Balance -->
      <div class="ft-card">
        <div class="ft-card-header">
          <h2 class="ft-card-title"><i class="bi bi-calculator" style="color:var(--accent-green)"></i> Balance</h2>
        </div>
        <div class="ft-card-body">
          <?php
          $rows=[
            ['Goal',number_format($goal_cal).' kcal','var(--gray-700)'],
            ['Food','+ '.number_format($total_cal).' kcal','var(--primary)'],
            ['Exercise','− '.number_format($total_burned).' kcal','var(--accent-green)'],
          ];
          foreach($rows as $r): ?>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span style="font-size:.8rem;color:var(--gray-500);font-weight:600"><?=$r[0]?></span>
            <span style="font-family:var(--font-mono);font-weight:700;color:<?=$r[2]?>"><?=$r[1]?></span>
          </div>
          <?php endforeach; ?>
          <div style="height:1px;background:var(--gray-200);margin:.75rem 0"></div>
          <?php $remaining=$goal_cal-$total_cal+$total_burned; ?>
          <div class="d-flex justify-content-between align-items-center">
            <span style="font-size:.875rem;font-weight:700">Remaining</span>
            <span class="net-pill" style="background:<?=$remaining>=0?'var(--accent-green-bg)':'var(--accent-red-bg)'?>;color:<?=$remaining>=0?'var(--accent-green)':'var(--accent-red)'?>;font-size:.9375rem">
              <?=$remaining>=0?'+':''?><?=number_format($remaining)?> kcal
            </span>
          </div>
          <div class="progress-bar-wrap mt-3" style="height:9px">
            <div class="progress-bar" style="width:<?=min(100,round($total_cal/$goal_cal*100))?>%;background:<?=$total_cal>$goal_cal?'var(--accent-red)':'var(--primary)'?>"></div>
          </div>
          <div class="text-end mt-1" style="font-size:.68rem;color:var(--gray-400)"><?=round($total_cal/$goal_cal*100)?>% of daily goal</div>
        </div>
      </div>
 
    </div><!-- /col-lg-4 -->
  </div><!-- /row -->
</div></div><!-- /container /diary-body -->
 
 
<!-- MODAL: Add Meal -->
<div class="modal fade" id="addMealModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-egg-fried me-2" style="color:var(--accent-orange)"></i>Log a Meal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-8">
            <label class="form-label">Food Name</label>
            <input type="text" class="form-control" id="mealFoodName" placeholder="e.g. Grilled Chicken">
          </div>
          <div class="col-4">
            <label class="form-label">Meal Type</label>
            <select class="form-select" id="mealType">
              <?php foreach($meal_types as $t): ?><option><?=$t?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Calories (kcal)</label>
            <input type="number" class="form-control" id="mealCalories" placeholder="e.g. 450" min="0">
          </div>
          <div class="col-12">
            <label class="form-label">Macros (g)</label>
            <div class="row g-2 macro-inputs">
              <div class="col-4"><input type="number" class="form-control" id="mealProtein" placeholder="Protein" min="0"></div>
              <div class="col-4"><input type="number" class="form-control" id="mealCarbs" placeholder="Carbs" min="0"></div>
              <div class="col-4"><input type="number" class="form-control" id="mealFat" placeholder="Fat" min="0"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light fw-600" data-bs-dismiss="modal">Cancel</button>
        <button class="btn fw-700 px-4" style="background:var(--primary);color:#fff;border:none" onclick="submitMeal()">
          <i class="bi bi-plus-lg me-1"></i>Add Meal
        </button>
      </div>
    </div>
  </div>
</div>
 
<!-- MODAL: Add Exercise -->
<div class="modal fade" id="addExerciseModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-lightning-fill me-2" style="color:var(--accent-green)"></i>Log an Exercise</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Exercise Name</label>
            <input type="text" class="form-control" id="exName" placeholder="e.g. Morning Run">
          </div>
          <div class="col-6">
            <label class="form-label">Duration (min)</label>
            <input type="number" class="form-control" id="exDuration" placeholder="30" min="1">
          </div>
          <div class="col-6">
            <label class="form-label">Calories Burned</label>
            <input type="number" class="form-control" id="exCalories" placeholder="kcal" min="0">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light fw-600" data-bs-dismiss="modal">Cancel</button>
        <button class="btn fw-700 px-4" style="background:var(--accent-green);color:#fff;border:none" onclick="submitExercise()">
          <i class="bi bi-plus-lg me-1"></i>Add Exercise
        </button>
      </div>
    </div>
  </div>
</div>
 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* Water Tracker */
function toggleGlass(el){
  const glasses=document.querySelectorAll('.glass');
  const idx=parseInt(el.dataset.index);
  const isFilled=el.classList.contains('filled');
  glasses.forEach(g=>{
    const gi=parseInt(g.dataset.index);
    if(isFilled) { if(gi>=idx) g.classList.remove('filled'); }
    else { if(gi<=idx) g.classList.add('filled'); }
  });
  const n=document.querySelectorAll('.glass.filled').length;
  document.getElementById('waterCount').textContent=n;

  document.getElementById('waterLitres').textContent=(n*.25).toFixed(2);
 const pourcentage = (n / 8) * 100;
            const waterProgressBar = document.querySelector('.summary-card .progress-bar[style*="accent-teal"]');
                if (waterProgressBar) {
                    waterProgressBar.style.width = pourcentage + '%';
                }
                document.getElementById("bilanwater").textContent = 8-n+" glasses to go";
  document.getElementById('waterGlasses').innerHTML =
  `${n}<small style="font-size:1rem;color:var(--gray-400)">/8</small>`;
saveWater();
}
 
/* Add Meal */
function submitMeal(){
  const name=document.getElementById('mealFoodName').value.trim();
  const cal=document.getElementById('mealCalories').value;
  if(!name||!cal){alert('Please fill in food name and calories.');return;}
  const params=new URLSearchParams({
    action:'add_meal',
    date:new URLSearchParams(location.search).get('date')||new Date().toISOString().split('T')[0],
    food_name:name,
    meal_type:document.getElementById('mealType').value,
    calories:cal,
    protein:document.getElementById('mealProtein').value||0,
    carbs:document.getElementById('mealCarbs').value||0,
    fat:document.getElementById('mealFat').value||0,
  });
  fetch('diary.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:params})
    .then(r=>r.json()).then(d=>{if(d.success)location.reload();else alert(d.error||'Error adding meal');})
    .catch(()=>{bootstrap.Modal.getInstance(document.getElementById('addMealModal')).hide();showToast('Meal added! (demo mode)');});
}
 
function deleteMeal(id){
  if(!confirm('Delete this meal?'))return;
  fetch('diary.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'delete_meal',id})})
    .then(r=>r.json()).then(d=>{if(d.success)location.reload();})
    .catch(()=>showToast('Meal not deleted (demo mode)'));
}

function submitExercise(){
  const name=document.getElementById('exName').value.trim();
  const dur=document.getElementById('exDuration').value;
  const cal=document.getElementById('exCalories').value;
  if(!name||!dur||!cal){alert('Please fill all fields.');return;}
  const params=new URLSearchParams({
    action:'add_exercise',
    date:new URLSearchParams(location.search).get('date')||new Date().toISOString().split('T')[0],
    exercise_name:name,duration:dur,calories_burned:cal
  });
  fetch('diary.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:params})
    .then(r=>r.json()).then(d=>{if(d.success)location.reload();else alert(d.error||'Error adding exercise');})
    .catch(()=>{bootstrap.Modal.getInstance(document.getElementById('addExerciseModal')).hide();showToast('Exercise not logged (demo mode)');});
}
 
function deleteExercise(id){
  if(!confirm('Delete this exercise?'))return;
  fetch('diary.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'delete_exercise',id})})
    .then(r=>r.json()).then(d=>{if(d.success)location.reload();})
    .catch(()=>showToast('Exercise not deleted (demo mode)'));
}
 
/* Notes */
function saveNotes(){
  const notes=document.getElementById('notesArea').value;
  fetch('diary.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:new URLSearchParams({action:'save_notes',notes,date:new URLSearchParams(location.search).get('date')||new Date().toLocaleDateString('en-CA')})})
    .then(r=>r.json()).then(d=>{if(d.success)location.reload();}).then(()=>showToast('Notes saved!'))
    .catch(()=>showToast('Notes not saved (demo mode)'));
}
 
/* Toast */
function showToast(msg,type='success'){
  const t=document.createElement('div');
  const colors={success:'var(--accent-green)',error:'var(--accent-red)'};
  t.style.cssText=`position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;
    background:${colors[type]||colors.success};color:#fff;padding:.75rem 1.25rem;
    border-radius:var(--r-md);font-weight:700;font-size:.875rem;
    box-shadow:var(--shadow-lg);animation:slideUp .3s ease`;
  t.textContent=msg;
  document.body.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(()=>t.remove(),300);},2500);
}
 function saveWater() {
  const n = document.querySelectorAll('.glass.filled').length;

  const params = new URLSearchParams({
    action: 'update_water',
    water: n,
    date: new URLSearchParams(location.search).get('date')
      || new Date().toISOString().split('T')[0]
  });
  console.log('Saving water:', n);
  console.log(params.get('date'));


  fetch('diary.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) {
      showToast('Water updated 💧');
    } else {
      showToast(d.error || 'Error updating water', 'error');
    }
  })
  .catch(() => {
    showToast('Water not saved (offline mode)', 'error');
  });
}
/* Animate bars on load */
document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('.progress-bar').forEach(bar=>{
    const w=bar.style.width; bar.style.width='0';
    setTimeout(()=>bar.style.width=w,150);
  });
});
</script>
</body>

</html>