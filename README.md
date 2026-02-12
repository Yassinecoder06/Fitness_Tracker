# 🏋️ FitTrack

A modern, responsive fitness tracking dashboard built with **HTML**, **CSS**, and **Vanilla JavaScript**. Inspired by MyFitnessPal's layout and user experience — no frameworks, no dependencies.

## ✨ Features

- **Dashboard** — Daily calorie summary, macronutrient progress bars, calorie budget ring, meals overview, exercise table
- **Diary** — Daily food & exercise logs, interactive water intake tracker, personal notes
- **Food Database** — Searchable food cards with macro info, filter chips, nutrition detail modal
- **Exercise** — Category cards (Cardio, Strength, Flexibility, Sports), recent activity list, add exercise modal
- **Progress** — Weight & calorie bar charts, monthly goals progress bars, streak tracking
- **Goals** — Set target weight, daily calorie target, and weekly workout frequency

## 🎨 Design

- Clean SaaS dashboard UI with blue accent colors
- Card-based layout with soft shadows and rounded corners
- Smooth hover animations and scroll-triggered effects
- Inter font via Google Fonts
- Fully responsive: Desktop → Tablet → Mobile (sidebar collapses to hamburger)

## 📁 Project Structure

```
Fitness_Tracker/
├── index.html          # Dashboard
├── diary.html          # Daily diary
├── food.html           # Food database
├── exercise.html       # Exercise tracker
├── progress.html       # Progress charts
├── goals.html          # Goal settings
├── css/
│   └── style.css       # Complete design system
├── js/
│   └── main.js         # All interactions
└── assets/
    ├── images/
    └── icons/
```

## 🚀 Getting Started

```bash
# Serve locally (no build step needed)
npx -y serve .

# Then open http://localhost:3000
```

Or simply open `index.html` directly in your browser.

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Structure | Semantic HTML5 |
| Styling | Vanilla CSS (Grid, Flexbox, Custom Properties) |
| Interactivity | Vanilla JavaScript (ES6+) |
| Icons | Inline SVG |
| Fonts | [Inter](https://fonts.google.com/specimen/Inter) via Google Fonts |

## 📱 Responsive Breakpoints

| Viewport | Behavior |
|----------|----------|
| > 1024px | Full sidebar + multi-column grids |
| 768–1024px | Adapted grids, narrower search |
| < 768px | Hamburger menu, single-column layout |

## 🗺️ Roadmap

- **Phase 2 — JavaScript**: BMI calculator, workout timer, form validation, localStorage persistence
- **Phase 3 — PHP**: User auth, database-backed data, per-user dashboards
- **Phase 4 — Symfony**: Full MVC with entities (User, Workout, Exercise, Goal), CRUD, API endpoints

## 📄 License

See [LICENSE](LICENSE) for details.