# 📋 FitTrack — Complete Project Documentation

## Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [Project Structure](#project-structure)
4. [Pages & Components](#pages--components)
5. [Technology Stack](#technology-stack)
6. [Design System](#design-system)
7. [JavaScript Functionality](#javascript-functionality)
8. [Color Palette](#color-palette)
9. [Getting Started](#getting-started)
10. [File Details](#file-details)
11. [License](#license)

---

## Overview

**FitTrack** is a modern, responsive fitness tracking dashboard built with vanilla HTML, CSS, and JavaScript. It provides a comprehensive fitness management application inspired by MyFitnessPal's user experience, featuring zero dependencies and a beautiful SaaS-style UI.

### Key Highlights
- **No build process required** — Open directly in browser or serve with `npx serve .`
- **Fully responsive** — Desktop, tablet, and mobile optimized
- **Beautiful UI** — Blue accent colors, card-based layout, smooth animations
- **Interactive** — Modals, progress animations, real-time feedback
- **Modern tech** — Vanilla JS with Intersection Observer API for performance
- **Inter font** — Professional typography via Google Fonts

---

## Features

### Core Features
✅ **Dashboard** — Daily calorie summary, macronutrient progress bars, calorie budget ring, meals overview, exercise table
✅ **Diary** — Daily food & exercise logs, interactive water intake tracker, personal notes
✅ **Food Database** — Searchable food cards with macro info, filter chips, nutrition detail modals
✅ **Exercise Tracker** — Category cards (Cardio, Strength, Flexibility, Sports), recent activity list, add exercise modal
✅ **Progress Analytics** — Weight & calorie bar charts, monthly goals progress bars, streak tracking
✅ **Goals Management** — Set target weight, daily calorie targets, weekly workout frequency
✅ **Notifications** — Bell icon placeholder with badge counter
✅ **User Profile** — Avatar with user initials (JD for John Doe)

### User Experience Features
- **Smooth animations** — Staggered fade-ins, scroll-triggered effects
- **Interactive elements** — Clickable water tracker glasses, modal popups, form inputs
- **Navigation** — Persistent sidebar with active state tracking
- **Mobile-friendly** — Hamburger menu for mobile devices
- **Toast notifications** — Feedback on save actions
- **Count-up animations** — Numbers animate when elements enter viewport

---

## Project Structure

```
Fitness_Tracker/
├── index.html              # Dashboard (Home page)
├── diary.html              # Daily food & exercise logs
├── food.html               # Food database & search
├── exercise.html           # Exercise categories & log
├── progress.html           # Weight & calorie charts
├── goals.html              # Goal settings & management
├── css/
│   └── style.css           # Complete design system (1000+ lines)
├── js/
│   └── main.js             # All interactions & animations (400+ lines)
├── README.md               # Project overview
├── PROJECT_DOCUMENTATION.md # This file
└── LICENSE                 # MIT License
```

### File Sizes Reference
- `style.css` — Large comprehensive design system with all components
- `main.js` — Medium-sized vanilla JS with Intersection Observer patterns
- `index.html` — ~400 lines with full dashboard layout
- Each page HTML — ~200-300 lines with consistent layout

---

## Pages & Components

### 1. **Dashboard (index.html)**
The main landing page with daily fitness summary.

**Sections:**
- **Stat Cards** — Display key metrics:
  - Calories Remaining (1250 kcal)
  - Calories Consumed (1450 kcal)
  - Active Streak (12 days)
  - Water Intake (6/8 glasses)

- **Calorie Ring** — Circular progress indicator using SVG
  - Shows percentage of calorie budget used
  - Animated stroke-dash offset
  - Data attribute: `data-percent`

- **Daily Summary** — Overview of meals and exercises:
  - Breakfast, lunch, dinner summaries
  - Recent exercises list
  - Time stamps for each item

- **Macronutrient Breakdown** — Protein, carbs, fat progress bars
  - Animated fill using `data-width` attribute
  - Color-coded (green for protein, orange for carbs, purple for fat)

---

### 2. **Diary (diary.html)**
Daily food and exercise logging interface.

**Sections:**
- **Date Navigation** — Previous/next day buttons
  - Current date display: "Wed, Feb 12, 2026"
  - Arrow buttons for navigation

- **Food Log** — List of meals for the day
  - Meal cards with food items
  - Calorie totals per meal
  - Add meal button (modal trigger)

- **Exercise Log** — Activities performed
  - Exercise cards with duration, calories burned
  - Category badges (Cardio, Strength, etc.)
  - Add exercise button (modal trigger)

- **Water Tracker** — Interactive glass icons
  - 8 clickable glasses
  - Toggle fill on click
  - Counter display
  - Visual feedback

- **Personal Notes** — Text area for daily notes
  - Save button with toast feedback

---

### 3. **Food Database (food.html)**
Searchable food catalog with nutritional information.

**Sections:**
- **Search Bar** — Filter foods by name
  - Icon: magnifying glass
  - Placeholder text for guidance

- **Filter Chips** — Category filtering
  - Options: All, Fruits, Vegetables, Protein, Grains, Dairy, Snacks
  - Active state tracking
  - Only one active chip at a time

- **Food Grid** — Card layout of foods
  - **Food Card Components:**
    - Emoji icon (🍌, 🍗, 🥕, etc.)
    - Food name & serving size
    - Macro breakdown (Cal, Protein, Carbs, Fat)
    - Clickable to show nutrition modal

- **Nutrition Modal** — Detailed food information
  - Large view of all macros
  - Detailed nutritional breakdown
  - Add to diary button
  - Close button

**Sample Foods Included:**
- Banana: 105 Cal, 1g Protein, 27g Carbs, 0g Fat
- Chicken Breast: 240 Cal, 45g Protein, 0g Carbs, 5g Fat
- Rice: 206 Cal, 4g Protein, 45g Carbs, 0g Fat
- Broccoli: 55 Cal, 4g Protein, 11g Carbs, 1g Fat

---

### 4. **Exercise (exercise.html)**
Exercise browsing and logging interface.

**Sections:**
- **Add Exercise Button** — Primary action button
  - Triggers add exercise modal
  - Data attribute: `data-modal="add-exercise-modal"`

- **Category Cards** — Exercise types (4 cards)
  - **Cardio** 🏃 — 24 exercises
  - **Strength** 🏋️ — 36 exercises
  - **Flexibility** 🧘 — 18 exercises
  - **Sports** ⚽ — 15 exercises
  - Each has icon, name, count, colored background

- **Recent Exercises** — Activity log
  - **Exercise Item Components:**
    - Icon (emoji in colored background)
    - Exercise name & category
    - Duration & date/time
    - Calories burned indicator
    - Delete option (optional)

**Sample Exercises:**
- Morning Run: 35 min, 450 cal, Today
- Weight Training: 50 min, 380 cal, Today
- Evening Yoga: 30 min, 180 cal, Yesterday

---

### 5. **Progress (progress.html)**
Analytics and trend tracking.

**Sections:**
- **Stats Overview** — Key metrics (4 stat cards)
  - Current Weight: 78 kg (↓ 2kg this month)
  - Avg Daily Calories: 2,150 (↑ On target)
  - Workouts This Week: 5 (↑ 1 more than last week)
  - Consecutive Days: (Streak tracking)

- **Weight Chart** — Bar chart visualization
  - Weekly weight trend
  - Bars animate on scroll
  - Data attributes: `data-height` for height percentage

- **Calorie Chart** — Bar chart visualization
  - Daily calorie intake trend
  - Compare against target line
  - Color-coded (green for under target, red for over)

- **Monthly Goals** — Progress bars
  - Goal name on left
  - Progress percentage
  - Target values displayed
  - Animated fill on scroll

---

### 6. **Goals (goals.html)**
Goal setting and management interface.

**Sections:**
- **Target Weight Card** — Weight goal setting
  - Current weight input
  - Target weight input
  - Timeline selector (1-6 months)
  - Save button (triggers toast)
  
- **Daily Calorie Target Card** — Calorie goal
  - Daily calorie goal input
  - Macronutrient split options
  - Save button

- **Weekly Workout Frequency Card** — Exercise goals
  - Target workouts per week dropdown
  - Exercise type preferences
  - Save button

Each goal card includes:
- Icon (emoji: ⚖️, 🔥, 💪)
- Title & description
- Current target display
- Form inputs with labels
- Primary save button

---

## Technology Stack

### Languages & Markup
- **HTML5** — Semantic markup with accessibility attributes
- **CSS3** — Custom properties (CSS variables), grid, flexbox
- **JavaScript (ES6+)** — Vanilla JS, no frameworks

### CSS Features Used
- **CSS Variables** — Comprehensive design token system
- **CSS Grid** — Multi-column layouts
- **Flexbox** — Component alignment & spacing
- **CSS Transitions & Animations** — Smooth visual feedback
- **Media Queries** — Responsive breakpoints for mobile/tablet/desktop
- **Intersection Observer** — Performance-optimized scroll detection

### JavaScript Features Used
- **Intersection Observer API** — Scroll-triggered animations
- **DOM Manipulation** — Event listeners, class toggling
- **RequestAnimationFrame** — Smooth count-up animations
- **Arrow Functions** — Modern ES6 syntax
- **Data Attributes** — HTML-to-JS communication (`data-*`)

### Typography
- **Font Family** — Inter (Google Fonts)
- **Weights** — 300, 400, 500, 600, 700, 800
- **Fallbacks** — System fonts: -apple-system, BlinkMacSystemFont, 'Segoe UI'

### Design Icons
- **Inline SVGs** — All icons are SVG code (no image files)
- **Emoji Icons** — Unicode emojis for food & exercise categories
- **Stroke Style** — Consistent 2-2.5px stroke width

---

## Design System

### Color Palette

#### Primary Colors
- `--primary` `#3B82F6` — Main blue brand color
- `--primary-dark` `#2563EB` — Darker blue for hover/active states
- `--primary-light` `#93C5FD` — Light blue for backgrounds
- `--primary-bg` `#EFF6FF` — Very light blue for card backgrounds

#### Accent Colors
- `--accent-green` `#10B981` — Success, positive metrics
- `--accent-green-bg` `#ECFDF5` — Light green background
- `--accent-red` `#EF4444` — Warning, negative metrics
- `--accent-red-bg` `#FEF2F2` — Light red background
- `--accent-orange` `#F59E0B` — Alert, medium priority
- `--accent-orange-bg` `#FFFBEB` — Light orange background
- `--accent-purple` `#8B5CF6` — Secondary accent
- `--accent-purple-bg` `#F5F3FF` — Light purple background
- `--accent-teal` `#14B8A6` — Tertiary accent
- `--accent-pink` `#EC4899` — Decorative accent

#### Neutral Colors
- `--white` `#FFFFFF` — Pure white for backgrounds
- `--gray-50` `#F9FAFB` — Almost white background
- `--gray-100` `#F3F4F6` — Light gray for hover states
- `--gray-200` `#E5E7EB` — Light borders
- `--gray-300` `#D1D5DB` — Medium borders
- `--gray-400` `#9CA3AF` — Medium gray text
- `--gray-500` `#6B7280` — Secondary text
- `--gray-600` `#4B5563` — Secondary text dark
- `--gray-700` `#374151` — Body text
- `--gray-800` `#1F2937` — Dark gray text
- `--gray-900` `#111827` — Near black text

### Typography Scale
- **Display** — Large titles (32px+)
- **Heading 1** — Page titles (24-28px)
- **Heading 2** — Section titles (20-24px)
- **Body** — Regular text (14-16px)
- **Small** — Secondary text, labels (12-13px)
- **Caption** — Meta information (11-12px)

### Spacing System
Based on 8px base unit:
- `8px` (1 unit)
- `12px` (1.5 units)
- `16px` (2 units)
- `24px` (3 units)
- `32px` (4 units)
- `40px` (5 units)
- `48px` (6 units)

### Border Radius
- `--radius-sm` `6px` — Small elements
- `--radius-md` `10px` — Standard components
- `--radius-lg` `14px` — Large components
- `--radius-xl` `20px` — Extra large elements
- `--radius-full` `9999px` — Fully rounded (pills, avatars)

### Shadow System
- `--shadow-xs` — Subtle shadow (1px)
- `--shadow-sm` — Small shadow
- `--shadow-md` — Medium shadow (default for cards)
- `--shadow-lg` — Large shadow (modals, dropdowns)
- `--shadow-xl` — Extra large shadow (featured elements)

### Transitions
- `--transition` `all 0.25s cubic-bezier(0.4, 0, 0.2, 1)` — Default easing
- `--transition-fast` `all 0.15s ease` — Fast feedback

### Layout Constants
- `--navbar-h` `64px` — Navigation bar height
- `--sidebar-w` `260px` — Sidebar width (desktop)
- `--sidebar-collapsed-w` `0px` — Sidebar width (mobile)

---

## JavaScript Functionality

### Core Function Groups

#### 1. **Sidebar & Navigation**
```javascript
initSidebar()
```
- Hamburger menu toggle
- Sidebar overlay click to close
- Mobile-first interaction

```javascript
initActiveNav()
```
- Detects current page from URL
- Highlights active navigation link
- Works with relative and absolute paths

#### 2. **Progress Bars & Charts**
```javascript
initProgressBars()
```
- Observes `.progress-bar__fill` elements
- Reads `data-width` attribute
- Animates width from 0% to target on scroll
- Uses Intersection Observer for performance

```javascript
initChartBars()
```
- Similar to progress bars
- Animates height instead of width
- Used for chart visualizations

#### 3. **Animations & Effects**
```javascript
initCalorieRing()
```
- SVG stroke-dash animation
- Circular progress indicator
- Reads `data-percent` attribute
- Calculates circumference: `2 * Math.PI * 80` (radius 80)

```javascript
initAnimateIn()
```
- Staggered fade-in effect
- Sets `opacity: 0` and `translateY(16px)` initially
- Animates to final state on scroll
- Uses Intersection Observer

```javascript
initCountUp()
```
- Smooth number animation
- Reads `data-count` attribute
- 1200ms duration with ease-out-cubic easing
- Formats numbers with `.toLocaleString()`

#### 4. **Interactive Elements**
```javascript
initWaterTracker()
```
- Click handler for `.water-tracker__glass` elements
- Toggle fill state
- Unfill if clicking last filled glass
- Updates counter display

```javascript
initModals()
```
- Open: `[data-modal]` triggers
- Close: `.modal__close`, `.modal-cancel` buttons
- Outside click to dismiss
- Prevents body scroll when active

#### 5. **Utility Functions**
```javascript
showToast(message)
```
- Creates floating notification
- Fixed position (bottom-right)
- Auto fade in/out
- 3000ms default visibility

#### 6. **Event Listeners**
```javascript
Food filter chips
```
- Active state management
- Only one chip active at a time
- Dynamic filtering (prepared for API integration)

### Intersection Observer Patterns
The entire animation framework uses Intersection Observer for:
- **Performance** — Only animate when visible
- **Efficiency** — Unobserve after animation
- **Threshold** — Different thresholds for different effects (0.1-0.3)

---

## Getting Started

### Option 1: Open Directly
1. Navigate to the `Fitness_Tracker` folder
2. Double-click `index.html`
3. Opens in default browser

### Option 2: Local Server (Recommended)
```bash
# Install globally (one-time)
npm install -g serve

# Or use npx directly (no installation needed)
npx -y serve .

# Then open http://localhost:3000
```

### Navigation
- **Sidebar** — Use link for page navigation
- **Hamburger** — Mobile menu toggle
- **Navbar** — Brand logo links to dashboard

### Interactive Elements
- Click any stat card, exercise card, food card
- Click "+" buttons to open modals
- Toggle water glasses by clicking them
- Fill progress bars appear on scroll
- Numbers count up when entering viewport

---

## File Details

### index.html (~400 lines)
**Key Content:**
- Navbar with logo, search, notifications
- Sidebar with main navigation
- Main header with title & date
- Stat cards grid (4 cards)
- Calorie ring SVG
- Daily meals section
- Exercise table
- Modals (add meal, add exercise)

**Data Attributes Used:**
- `data-count` — For count-up animations
- `data-percent` — For calorie ring
- `data-modal` — For modal triggers

---

### diary.html (~250 lines)
**Key Content:**
- Date navigation component
- Food log section with entries
- Exercise log section
- Water tracker (8 glasses)
- Daily notes textarea
- Modals for adding items

**Key Classes:**
- `.diary-header` — Date navigation wrapper
- `.diary-date-nav` — Arrow buttons & date
- `.water-tracker__glass` — Interactive glasses
- `.progress-bar__fill` — Animated bars

---

### food.html (~300 lines)
**Key Content:**
- Search input field
- Filter chip buttons
- Food card grid (responsive)
- Each food shows emoji, name, serving, macros
- Nutrition modal (detailed view)

**Key Classes:**
- `.food-search` — Search bar container
- `.food-filter-chip` — Filter buttons
- `.food-card` — Individual food item
- `.food-grid` — Grid container
- `.food-card__macros` — Macro display

---

### exercise.html (~280 lines)
**Key Content:**
- Add exercise button (primary)
- Category cards (4 types)
- Recent exercises list
- Add exercise modal
- Exercise filter options

**Key Classes:**
- `.exercise-category-card` — Category boxes
- `.exercise-list__item` — Exercise entries
- Icons with colored backgrounds

---

### progress.html (~320 lines)
**Key Content:**
- Stat cards (4 metrics)
- Weight chart (bar visualization)
- Calorie chart (bar visualization)
- Monthly goals progress
- Achievements badges (optional)

**Key Classes:**
- `.simple-chart__bar` — Chart bars (animated)
- `.stat-card` — Metric cards
- `.progress-bar` — Goal progress bars

---

### goals.html (~300 lines)
**Key Content:**
- Target weight card (form)
- Daily calorie target card (form)
- Weekly workout frequency card (form)
- Each has icon, title, description, inputs
- Save buttons trigger toast notifications

**Key Classes:**
- `.goal-card` — Goal container
- `.form-group` — Input wrappers
- `.form-input`, `.form-select` — Input elements
- `.btn` — Button variants

---

### style.css (~1000+ lines)
**Organization:**
1. Google Fonts import
2. CSS Custom Properties (variables)
3. Reset & base styles
4. Navbar component
5. Sidebar component
6. Main layout container
7. Card component
8. Stat card component
9. Progress bar component
10. Form elements
11. Button variants
12. Modal component
13. Animations & utilities
14. Responsive media queries

**Key Features:**
- Complete design token system
- Component-based architecture
- Utility classes (layout, spacing, text)
- Media queries at bottom

---

### main.js (~400+ lines)
**Organization:**
1. DOMContentLoaded entry point
2. Sidebar toggle
3. Active nav link detection
4. Progress bar animations
5. Calorie ring animation
6. Fade-in animations
7. Water tracker interaction
8. Modal system
9. Count-up animations
10. Chart bar animations
11. Filter chip management
12. Toast notification helper

**Performance Optimizations:**
- Intersection Observer (lazy animations)
- RequestAnimationFrame (smooth updates)
- Event delegation where possible
- Unobserve after animation complete

---

## Color Usage Examples

### Cards & Containers
- Primary background: `var(--primary-bg)` (#EFF6FF)
- Hover state: `var(--gray-100)` (#F3F4F6)
- Border: `var(--gray-200)` (#E5E7EB)

### Text
- Headings: `var(--gray-900)` (#111827)
- Body: `var(--gray-800)` (#1F2937)
- Secondary: `var(--gray-500)` (#6B7280)
- Disabled: `var(--gray-400)` (#9CA3AF)

### Status Indicators
- Success: `var(--accent-green)` (#10B981)
- Warning: `var(--accent-orange)` (#F59E0B)
- Error: `var(--accent-red)` (#EF4444)
- Info: `var(--primary)` (#3B82F6)

### Backgrounds
- Macro types use different accent backgrounds:
  - Protein: Green
  - Carbs: Orange
  - Fat: Purple
  - Calories: Blue

---

## Responsive Design

### Breakpoints Used
- **Mobile** — < 768px
  - Single column layouts
  - Hamburger menu (sidebar hidden)
  - Full-width cards
  - Optimized touch targets

- **Tablet** — 768px - 1024px
  - 2-column layouts for some sections
  - Sidebar collapsible
  - Adjusted font sizes

- **Desktop** — > 1024px
  - 3-4 column grids
  - Permanent sidebar
  - Full layout utilization

### Responsive Classes
- `.flex-between` — Flex with space-between
- `.grid` — CSS Grid container
- Media query blocks at bottom of CSS

---

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Requires ES6 support (all modern browsers)
- CSS Grid and Flexbox support required
- SVG support required
- Intersection Observer API (modern browsers)

---

## Future Enhancement Ideas

### Functionality
- Backend API integration for data persistence
- User authentication & accounts
- Real-time sync across devices
- Export data (CSV, PDF)
- Recipes & meal planning
- Social sharing features
- Achievements & badges system
- Exercise video tutorials

### UI/UX
- Dark mode toggle
- Custom theme colors
- Customizable dashboard layout
- More chart types (line, area, pie)
- Advanced filtering & search
- Favorite/bookmark foods & exercises
- Bulk import from other apps

### Performance
- Service Worker for offline support
- IndexedDB for local storage
- Lazy loading for images
- Code splitting
- Progressive Web App (PWA) support

---

## Debugging & Development

### Console Logging
Open browser DevTools to see:
- Modal open/close logs
- Animation timings
- DOM element selections

### Common Issues
1. **Animations not working** — Check Intersection Observer support
2. **Modal not closing** — Check event delegation on overlay
3. **Sidebar not closing** — Clear overflow on body element
4. **Count-up errors** — Verify data-count is valid integer

### Testing the Project
1. Open in browser
2. Navigate all pages using sidebar
3. Test mobile view (toggle device toolbar)
4. Open modals and test close buttons
5. Scroll to trigger animations
6. Fill water tracker glasses
7. Update form inputs
8. Toggle filter chips

---

## License

MIT License

Copyright (c) 2026 Yassinecoder06

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.

---

## Summary

**FitTrack** is a comprehensive fitness tracking dashboard that demonstrates modern web development best practices:
- ✅ Clean, semantic HTML
- ✅ Professional design system with CSS variables
- ✅ Performant vanilla JavaScript with modern APIs
- ✅ Fully responsive design
- ✅ Smooth animations and interactions
- ✅ Accessibility considerations
- ✅ No external dependencies
- ✅ Ready for production or further development

The project serves as an excellent foundation for frontend development learning and can be easily extended with backend integration for real-world fitness tracking functionality.

---

**Last Updated:** March 27, 2026  
**Total Lines of Code:** ~2000+ (HTML, CSS, JS combined)  
**Build Tools Required:** None  
**Dependencies:** None  
**License:** MIT
