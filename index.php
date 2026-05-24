<?php
  session_start();
  require_once __DIR__ . '/backend/bootstrap.php';
  require_once __DIR__ . '/backend/db.php';
  require_once __DIR__ . '/backend/auth.php';
  require_once __DIR__ . '/backend/dashboard_repository.php';



  // ======================== just for test
  if(isset($_SESSION['username'])!==false) : // badel el test ki tabda 3ana el auth
    header("Location: login.php"); // =====> change it to the log in page
    exit;
  endif;

  $name= $_SESSION["username"] ?? "Unknowen";
  $id = 1; // hehdi suppose $_SESSION["id"]
  // ======================== just for test
  $date = new DateTime();
  $date_formated = $date->format("Y-m-d");
  $date_formated = "2026-05-20"; // will be removed just for test
  
  
  $dashboard = new Dashboard();
  $calories_consumed = $dashboard->caloriesConsumed($date_formated,$id);
  $calories_burned = $dashboard->caloriesBurned($date_formated,$id) ?? 2000;
  $steps = $dashboard->stepsToday($date_formated,$id);
  $calurie_budget = $dashboard->caloriesBudget($date_formated,$id);
  $prot = $dashboard->ProteinAmount($date_formated,$id);
  $carb = $dashboard->CarbsAmount($date_formated,$id);
  $fat = $dashboard->FatAmount($date_formated,$id);
  $fiber = $dashboard->FiberAmount($date_formated,$id);
  $yesterday = date('Y-m-d', strtotime('-1 day'));
  $remaining_calories = 2700 - $calories_consumed;
  $calories_consumed_yesterday = $dashboard->caloriesConsumed($yesterday,$id);
  $pourcentage_steps = $steps * 100 / 10000; 
  $pourcentage_today_yesterday_consumed_calories = 100;
  // calcul de calorie consumed w choix de couleur de label
if($calories_consumed_yesterday != 0){
  if($calories_consumed - $calories_consumed_yesterday >0){
    $label_color = "green";
      $pourcentage_today_yesterday_consumed_calories = $calories_consumed*100 / $calories_consumed_yesterday;
  }else{
    $label_color="red";
    $pourcentage_today_yesterday_consumed_calories = -($calories_consumed_yesterday - $calories_consumed)*100 / $calories_consumed_yesterday; 
  }
}
  // lista mta3 les exercice ne9esha el type !!!
  $array_of_exercice = $dashboard->exercice_today($date_formated,$id);
  $morning_meals  = $dashboard->morningMeals($date_formated,$id);
  $launch_meals = $dashboard->launchMeals($date_formated,$id);
  $dinner_meal = $dashboard->dinnerMeals($date_formated,$id);
  $snack = $dashboard->snackMeals($date_formated,$id);

  
  $sum_cal_mroning =0 ;
  $sum_cal_snack =0 ;
  $sum_cal_dinner =0 ;
  $sum_cal_launch =0 ;

  foreach($morning_meals as $meal){
    $sum_cal_mroning += $meal['calories'];
  }
  foreach($launch_meals as $meal){
    $sum_cal_launch += $meal['calories'];
  }
  foreach($snack as $meal){
    $sum_cal_snack += $meal['calories'];
  }
  foreach($dinner_meal as $meal){
    $sum_cal_dinner += $meal['calories'];
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="FitTrack Dashboard — Track your daily calories, meals, exercises, and nutrition goals.">
  <title>FitTrack | Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar">
    <div class="navbar__left">
      <button class="navbar__hamburger" aria-label="Toggle menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <a href="index.php" class="navbar__logo">
        <div class="navbar__logo-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
        </div>
        FitTrack
      </a>
    </div>
    <div class="navbar__search">
      <svg class="navbar__search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" placeholder="Search food, exercises, goals...">
    </div>
    <div class="navbar__right">
      <button class="navbar__icon-btn" aria-label="Notifications">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
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
      <a href="index.php" class="sidebar__link active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
      </a>
      <a href="diary.php" class="sidebar__link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        Diary
      </a>

      <a href="food.php" class="sidebar__link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
        Food
      </a>
      <a href="exercise.php" class="sidebar__link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.4 14.4L9.6 9.6"/><path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767-1.768a2 2 0 1 1-2.829-2.829l-1.767-1.767a2 2 0 1 1-2.829-2.829L4.869 7.697a2 2 0 1 1 2.828-2.829l1.768 1.768a2 2 0 1 1 2.828 2.829l1.768 1.767a2 2 0 1 1 2.828 2.829l1.768 1.767a2 2 0 1 1-2.828 2.829z"/></svg>
        Exercise
      </a>
     <?php if (isset($_SESSION['user_id'])): ?>
  <a href="logout.php" class="sidebar__link">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
      <path d="M16 16l4-4m0 0l-4-4m4 4H9"/>
    </svg>
    Logout
  </a>
<?php endif; ?>

      <span class="sidebar__section-title">Analytics</span>
      <a href="progress.php" class="sidebar__link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Progress
      </a>
      <a href="goals.php" class="sidebar__link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
        Goals
      </a>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main">
    <div class="main__header">
      <h1 class="main__title">Dashboard   <?= $calories_consumed ?></h1>
      <?= "<p class='main__subtitle'>" . $date->format("l, F d, Y") . " — Welcome back, " . $name . "! </p>" ?>
    </div>

    <!-- STAT CARDS -->
    <div class="stats-grid">
      <div class="stat-card animate-in">
        <div class="stat-card__icon stat-card__icon--blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
        </div>
        <div class="stat-card__info">
          <div class="stat-card__label">Calories Remaining</div>
          <div class="stat-card__value" data-count="<?= $remaining_calories ?>">0</div>
          <div class="stat-card__change stat-card__change--up">↑ On track</div>
        </div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card__icon stat-card__icon--green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
        </div>
        <div class="stat-card__info">
          <div class="stat-card__label">Calories Consumed</div>
          <div class="stat-card__value" data-count="<?= $calories_consumed ?>">0</div>
          <div class="stat-card__change stat-card__change--up" style="color: <?= $label_color ?>;">
            ↑ <?= $pourcentage_today_yesterday_consumed_calories ?>% vs yesterday
          </div>
        </div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card__icon stat-card__icon--red">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        </div>
        <div class="stat-card__info">
          <div class="stat-card__label">Calories Burned</div>
          <div class="stat-card__value" data-count="<?= $calories_burned ?>">0</div>
          <div class="stat-card__change stat-card__change--up">↑ Great pace!</div>
        </div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card__icon stat-card__icon--orange">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <div class="stat-card__info">
          <div class="stat-card__label">Steps Today</div>
          <div class="stat-card__value" data-count="<?= $steps ?>">0</div>
          <div class="stat-card__change stat-card__change--up">↑ <?= $pourcentage_steps ?>% of goal</div>
        </div>
      </div>
    </div>

    <!-- PROGRESS SECTION -->
    <div class="progress-section">
      <!-- Calorie Ring -->
      <div class="card card--flat animate-in">
        <div class="card__header">
          <div>
            <h2 class="card__title">Calorie Budget</h2>
            <p class="card__subtitle">Daily target: 2,700 kcal</p>
          </div>
        </div>
        <div class="calorie-ring">
          <svg class="calorie-ring__svg" viewBox="0 0 180 180">
            <circle class="calorie-ring__bg" cx="90" cy="90" r="80"/>
            <circle class="calorie-ring__fill" <?= $remaining_calories>2700 ? 'style="stroke:#1fb382";':'' ?> cx="90" cy="90" r="80" data-percent="<?= $remaining_calories > 2700 ? 100 : $remaining_calories * 100 / 2700 ?>"/>
          </svg>
          <div class="calorie-ring__center">
            <div class="calorie-ring__number"><?= $remaining_calories ?></div>
            <div class="calorie-ring__text">
              <?php

                if ($remaining_calories > 2700){echo "done ✔️";}
                else{echo "remaining";}
              ?>
            </div>
          </div>
          <div class="calorie-ring__footer">
            <div class="calorie-ring__item">
              <span class="calorie-ring__item-value">2,700</span>
              <span class="calorie-ring__item-label">Goal</span>
            </div>
            <div class="calorie-ring__item">
              <span class="calorie-ring__item-value">1,450</span>
              <span class="calorie-ring__item-label">Food</span>
            </div>
            <div class="calorie-ring__item">
              <span class="calorie-ring__item-value">680</span>
              <span class="calorie-ring__item-label">Exercise</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Macros -->
      <div class="card card--flat animate-in">
        <div class="card__header">
          <div>
            <h2 class="card__title">Macronutrients</h2>
            <p class="card__subtitle">Daily breakdown</p>
          </div>
        </div>
        <div class="progress-bar-group">
          <div class="progress-bar__header">
            <span class="progress-bar__label">🥩 Protein</span>
            <span class="progress-bar__value"><?= $prot ?>g / 150g</span>
          </div>
          <div class="progress-bar">
            <div class="progress-bar__fill progress-bar__fill--blue" data-width="<?= $prot * 100 / 150 ?>"></div>
          </div>
        </div>
        <div class="progress-bar-group">
          <div class="progress-bar__header">
            <span class="progress-bar__label">🍞 Carbs</span>
            <span class="progress-bar__value"><?= $carb ?>g / 300g</span>
          </div>
          <div class="progress-bar">
            <div class="progress-bar__fill progress-bar__fill--orange" data-width="<?= $carb*100/300 ?>"></div>
          </div>
        </div>
        <div class="progress-bar-group">
          <div class="progress-bar__header">
            <span class="progress-bar__label">🥑 Fat</span>
            <span class="progress-bar__value"><?= $fat ?>g / 80g</span>
          </div>
          <div class="progress-bar">
            <div class="progress-bar__fill progress-bar__fill--red" data-width="<?= $fat * 100 / 80 ?>"></div>
          </div>
        </div>
        <div class="progress-bar-group">
          <div class="progress-bar__header">
            <span class="progress-bar__label">💧 Fiber</span>
            <span class="progress-bar__value"><?= $fiber ?>g / 30g</span>
          </div>
          <div class="progress-bar">
            <div class="progress-bar__fill progress-bar__fill--green" data-width="<?= $fiber * 100 / 30 ?>"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- MEALS -->
    <div class="card__header mb-8">
      <h2 class="card__title" style="font-size:20px;">Today's Meals</h2>
    </div>
    <div class="meals-grid">
      <div class="meal-card animate-in">
        <div class="meal-card__header">
          <div class="meal-card__type">
            <span class="meal-card__emoji">🌅</span>
            <div>
              <div class="meal-card__name">Breakfast</div>
              <div class="meal-card__cals"><?= $sum_cal_mroning ?> kcal</div>
            </div>
          </div>
          <button class="meal-card__add-btn" title="Add food">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
        </div>
          <?php 
          if(count($morning_meals)==0){
            echo "<div class='meal-card__empty'>No snacks logged yet</div>";
          }else{
          foreach($morning_meals as $meal){
            echo <<<TEXT
            <div class="meal-card__items">
              <div class="meal-card__item">
                <span class="meal-card__item-name">{$meal['food_name']}</span>
                <span class="meal-card__item-cal">{$meal['calories']} kcal</span>
              </div>
            </div>
            TEXT;
          }
          }
          ?>
      </div>
      <div class="meal-card animate-in">
        <div class="meal-card__header">
          <div class="meal-card__type">
            <span class="meal-card__emoji">☀️</span>
            <div>
              <div class="meal-card__name">Lunch</div>
              <div class="meal-card__cals"><?= $sum_cal_launch ?> kcal</div>
            </div>
          </div>
          <button class="meal-card__add-btn" title="Add food">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
        </div>
        <div class="meal-card__items">
          
          <?php 
          if(count($launch_meals)==0){
            echo "<div class='meal-card__empty'>No snacks logged yet</div>";
          }else{
          foreach($launch_meals as $meal){
            echo <<<TEXT
            <div class="meal-card__items">
              <div class="meal-card__item">
                <span class="meal-card__item-name">{$meal['food_name']}</span>
                <span class="meal-card__item-cal">{$meal['calories']} kcal</span>
              </div>
            </div>
            TEXT;
          }
          }
          ?>
        </div>
      </div>
      <div class="meal-card animate-in">
        <div class="meal-card__header">
          <div class="meal-card__type">
            <span class="meal-card__emoji">🌙</span>
            <div>
              <div class="meal-card__name">Dinner</div>
              <div class="meal-card__cals"><?= $sum_cal_dinner ?> kcal</div>
            </div>
          </div>
          <button class="meal-card__add-btn" title="Add food">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
        </div>
                  <?php 
          if(count($dinner_meal)==0){
            echo "<div class='meal-card__empty'>No snacks logged yet</div>";
          }else{
          foreach($dinner_meal as $meal){
            echo <<<TEXT
            <div class="meal-card__items">
              <div class="meal-card__item">
                <span class="meal-card__item-name">{$meal['food_name']}</span>
                <span class="meal-card__item-cal">{$meal['calories']} kcal</span>
              </div>
            </div>
            TEXT;
          }
          }
          ?>
      </div>
      <div class="meal-card animate-in">
        <div class="meal-card__header">
          <div class="meal-card__type">
            <span class="meal-card__emoji">🍎</span>
            <div>
              <div class="meal-card__name">Snacks</div>
              <div class="meal-card__cals"><?= $sum_cal_snack ?> kcal</div>
            </div>
          </div>
          <button class="meal-card__add-btn" title="Add food">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
        </div>
                  <?php 
          if(count($snack)==0){
            echo "<div class='meal-card__empty'>No snacks logged yet</div>";
          }else{
          foreach($snack as $meal){
            echo <<<TEXT
            <div class="meal-card__items">
              <div class="meal-card__item">
                <span class="meal-card__item-name">{$meal['food_name']}</span>
                <span class="meal-card__item-cal">{$meal['calories']} kcal</span>
              </div>
            </div>
            TEXT;
          }
          }
          ?>
      </div>
    </div>

    <!-- EXERCISE SUMMARY TABLE -->
    <div class="card card--flat animate-in mb-28" style="margin-top: 28px;">
      <div class="card__header">
        <div>
          <h2 class="card__title">Exercise Summary</h2>
          <p class="card__subtitle">Today's activities</p>
        </div>
  <a href="exercise.php" class="card__action">View All →</a>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>Exercise</th>
            <th>Type</th>
            <th>Duration</th>
            <th>Calories</th>
          </tr>
        </thead>
        <tbody>
          <!-- fill table content with exercise_logs entries -->
          <?php
            foreach($array_of_exercice as $exercice ){
              echo <<<TEXT
                <tr>
                  <td><strong>{$exercice["exercise_name"]}</strong></td>
                  <td><span class="table-tag table-tag--cardio">{$dashboard->exerciceType($exercice["exercise_name"])}</span></td>
                  <td>{$exercice["duration"]} min</td>
                  <td><strong>{$exercice["calories_burned"]} kcal</strong></td>
                </tr>
              TEXT;
              }
          ?>

          <!--<tr>
            <td><strong>Morning Run</strong></td>
            <td><span class="table-tag table-tag--cardio">Cardio</span></td>
            <td>35 min</td>
            <td><strong>380 kcal</strong></td>
          </tr>
          <tr>
            <td><strong>Weight Training</strong></td>
            <td><span class="table-tag table-tag--strength">Strength</span></td>
            <td>45 min</td>
            <td><strong>220 kcal</strong></td>
          </tr>
          <tr>
            <td><strong>Yoga</strong></td>
            <td><span class="table-tag table-tag--flexibility">Flexibility</span></td>
            <td>20 min</td>
            <td><strong>80 kcal</strong></td>
          </tr> -->
        </tbody>
      </table>
    </div>
  </main>

  <script src="js/main.js"></script>
</body>
</html>
