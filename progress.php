<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress — FitTrack</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container" style="padding-top: 48px; padding-bottom: 48px;">
        <h1 class="page-header">My Progress</h1>
        <div class="grid-2">
            <div class="card">
                <h2 style="margin-bottom: 16px; color: var(--text-primary);">Weight History</h2>
                <canvas id="weightChart"></canvas>
                <p id="weightEmpty" style="display:none; color: var(--text-secondary); text-align: center; padding: 40px 0;">No weight data yet. Start logging!</p>
            </div>
            <div class="card">
                <h2 style="margin-bottom: 16px; color: var(--text-primary);">Daily Calorie Target</h2>
                <canvas id="calorieChart"></canvas>
                <p id="calorieEmpty" style="display:none; color: var(--text-secondary); text-align: center; padding: 40px 0;">No calorie goal set yet.</p>
            </div>
        </div>
        <!-- Goals Summary Section (placeholder for TASK 8) -->
        <div id="goalsSummary" style="margin-top: 32px;"></div>
    </div>
    <script src="assets/js/progress.js"></script>
</body>
</html>
