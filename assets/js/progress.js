if (typeof Chart !== 'undefined') {
  Chart.defaults.color = '#94a3b8';
  Chart.defaults.font.family = "'Inter', sans-serif";
}

let weightChartInstance = null;
let calorieChartInstance = null;

const weightChartCanvas = document.getElementById('weightChart');
const weightEmpty = document.getElementById('weightEmpty');
const calorieChartCanvas = document.getElementById('calorieChart');
const calorieEmpty = document.getElementById('calorieEmpty');

function toggleChartEmptyState(chartCanvas, emptyElement, hasData) {
  if (!chartCanvas || !emptyElement) {
    return;
  }

  chartCanvas.style.display = hasData ? 'block' : 'none';
  emptyElement.style.display = hasData ? 'none' : 'block';
}

function renderWeightChart(weightLogs) {
  if (!weightChartCanvas || !weightEmpty || typeof Chart === 'undefined') {
    return;
  }

  if (weightChartInstance) {
    weightChartInstance.destroy();
    weightChartInstance = null;
  }

  if (!Array.isArray(weightLogs) || weightLogs.length === 0) {
    toggleChartEmptyState(weightChartCanvas, weightEmpty, false);
    return;
  }

  toggleChartEmptyState(weightChartCanvas, weightEmpty, true);

  const labels = weightLogs.map((entry) => entry.date);
  const values = weightLogs.map((entry) => Number(entry.weight));

  weightChartInstance = new Chart(weightChartCanvas, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          data: values,
          borderColor: '#3B82F6',
          backgroundColor: 'rgba(59,130,246,0.1)',
          fill: true,
          tension: 0.3,
          pointRadius: 4,
          pointBackgroundColor: '#3B82F6',
          pointBorderColor: '#3B82F6',
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        x: {
          grid: {
            color: 'rgba(148,163,184,0.1)',
          },
        },
        y: {
          grid: {
            color: 'rgba(148,163,184,0.1)',
          },
          title: {
            display: true,
            text: 'Weight (kg)',
          },
        },
      },
    },
  });
}

function renderCalorieChart(goals) {
  if (!calorieChartCanvas || !calorieEmpty || typeof Chart === 'undefined') {
    return;
  }

  if (calorieChartInstance) {
    calorieChartInstance.destroy();
    calorieChartInstance = null;
  }

  const dailyCalories = Number(goals?.daily_calories ?? 0);
  if (!goals || !Number.isFinite(dailyCalories) || dailyCalories === 0) {
    toggleChartEmptyState(calorieChartCanvas, calorieEmpty, false);
    return;
  }

  toggleChartEmptyState(calorieChartCanvas, calorieEmpty, true);

  const maxCalories = 10000;
  const remaining = Math.max(0, maxCalories - dailyCalories);
  const rootStyles = getComputedStyle(document.documentElement);
  const centerTextColor = rootStyles.getPropertyValue('--text-primary').trim() || '#f1f5f9';
  const centerSubtextColor = rootStyles.getPropertyValue('--text-secondary').trim() || '#94a3b8';

  const centerTextPlugin = {
    id: 'calorieCenterText',
    afterDraw(chart, args, pluginOptions) {
      if (!chart.chartArea) {
        return;
      }

      const value = pluginOptions?.value ?? '';
      const { ctx, chartArea } = chart;
      const centerX = (chartArea.left + chartArea.right) / 2;
      const centerY = (chartArea.top + chartArea.bottom) / 2;

      ctx.save();
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';

      ctx.fillStyle = centerTextColor;
      ctx.font = "600 24px 'Inter', sans-serif";
      ctx.fillText(String(value), centerX, centerY - 8);

      ctx.fillStyle = centerSubtextColor;
      ctx.font = "500 12px 'Inter', sans-serif";
      ctx.fillText('kcal', centerX, centerY + 14);
      ctx.restore();
    },
  };

  calorieChartInstance = new Chart(calorieChartCanvas, {
    type: 'doughnut',
    data: {
      labels: ['Target', 'Remaining'],
      datasets: [
        {
          data: [dailyCalories, remaining],
          backgroundColor: ['#10B981', '#334155'],
          borderWidth: 0,
        },
      ],
    },
    options: {
      responsive: true,
      cutout: '72%',
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          enabled: false,
        },
        calorieCenterText: {
          value: dailyCalories,
        },
      },
    },
    plugins: [centerTextPlugin],
  });
}

function renderGoalsSummary(goals) {
  const container = document.getElementById('goalsSummary');
  if (!container) {
    return;
  }

  if (!goals) {
    container.innerHTML = `
      <div class="card" style="text-align: center; padding: 40px;">
        <p style="color: var(--text-secondary);">No goals set yet.
        <a href="goals.php" style="color: var(--primary);">Set your goals</a> to get started!</p>
      </div>`;
    return;
  }

  const summaryCards = [
    {
      label: 'Target Weight',
      value: goals.target_weight ? goals.target_weight + ' kg' : '—',
      color: 'var(--primary)',
    },
    {
      label: 'Daily Calories',
      value: goals.daily_calories ? goals.daily_calories + ' kcal' : '—',
      color: 'var(--accent-green)',
    },
    {
      label: 'Weekly Workouts',
      value: goals.weekly_workouts ? goals.weekly_workouts + 'x' : '—',
      color: 'var(--accent-purple)',
    },
  ];

  container.innerHTML = `
    <h2 style="margin-bottom: 16px; color: var(--text-primary);">Goals Summary</h2>
    <div class="grid-3">
      ${summaryCards
        .map(
          (card) => `
        <div class="stat-card" style="border-top-color: ${card.color};">
          <div class="stat-label">${card.label}</div>
          <div class="stat-number">${card.value}</div>
        </div>
      `
        )
        .join('')}
    </div>`;
}

async function loadProgressData() {
  try {
    const response = await fetch('api/progress_data.php');
    if (!response.ok) {
      throw new Error('Failed to fetch progress data');
    }

    const data = await response.json();
    renderWeightChart(data.weight_logs);
    renderCalorieChart(data.goals);
    renderGoalsSummary(data.goals);
  } catch (error) {
    renderWeightChart([]);
    renderCalorieChart(null);
    renderGoalsSummary(null);
  }
}

loadProgressData();
