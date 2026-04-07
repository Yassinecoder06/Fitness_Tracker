function showToast(message, type = 'success') {
  const toast = document.getElementById('successToast');
  if (!toast) {
    return;
  }

  toast.textContent = message;
  toast.style.background = type === 'success' ? 'var(--accent-green)' : '#EF4444';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

const goalsForm = document.getElementById('goalsForm');

if (goalsForm) {
  const targetWeightInput = goalsForm.querySelector('input[name="target_weight"]');
  const dailyCaloriesInput = goalsForm.querySelector('input[name="daily_calories"]');
  const weeklyWorkoutsInput = goalsForm.querySelector('input[name="weekly_workouts"]');

  goalsForm.addEventListener('submit', function (e) {
    let hasError = false;
    let firstErrorMessage = '';

    const validations = [
      {
        input: targetWeightInput,
        min: 20,
        max: 300,
        message: 'Target weight must be between 20 and 300.',
      },
      {
        input: dailyCaloriesInput,
        min: 500,
        max: 10000,
        message: 'Daily calories must be between 500 and 10000.',
      },
      {
        input: weeklyWorkoutsInput,
        min: 1,
        max: 21,
        message: 'Weekly workouts must be between 1 and 21.',
      },
    ];

    validations.forEach(({ input, min, max, message }) => {
      if (!input) {
        return;
      }

      input.classList.remove('input-error');
      const rawValue = input.value.trim();

      // Empty fields are allowed; validate only if user provided a value.
      if (rawValue === '') {
        return;
      }

      const value = Number(rawValue);
      if (Number.isNaN(value) || value < min || value > max) {
        input.classList.add('input-error');
        hasError = true;
        if (!firstErrorMessage) {
          firstErrorMessage = message;
        }
      }
    });

    if (hasError) {
      e.preventDefault();
      showToast(firstErrorMessage, 'error');
    }
  });
}

document.querySelectorAll('.form-input').forEach((input) => {
  input.addEventListener('input', function () {
    this.classList.remove('input-error');
  });
});
