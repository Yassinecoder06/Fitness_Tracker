<?php
declare(strict_types=1);

require_once __DIR__ . '/backend/bootstrap.php';
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';

$pdo = get_pdo();
ensure_authenticated($pdo, '/food.php');

require_once __DIR__ . '/backend/food_repository.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$result = fetch_foods($_GET);
$foods = $result['foods'];
$meta = $result['meta'];
$categories = FOOD_CATEGORIES;

function build_query(array $overrides): string
{
    $query = array_merge($_GET, $overrides);
    return '?' . http_build_query($query);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FitTrack Food — Search and browse foods with nutrition details.">
    <title>FitTrack | Food</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 20V10" />
                        <path d="M12 20V4" />
                        <path d="M6 20v-6" />
                    </svg>
                </div>
                FitTrack
            </a>
        </div>
        <div class="navbar__search">
            <svg class="navbar__search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" placeholder="Search food, exercises, goals...">
        </div>
        <div class="navbar__right">
            <button class="navbar__icon-btn" aria-label="Notifications">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                </svg>
                <span class="navbar__badge"></span>
            </button>
            <div class="navbar__avatar" title="User">JD</div>
        </div>
    </nav>

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
            <a href="food.php" class="sidebar__link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
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
            <a href="goals.php" class="sidebar__link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <circle cx="12" cy="12" r="6" />
                    <circle cx="12" cy="12" r="2" />
                </svg> Goals</a>
        </nav>
    </aside>

    <main class="main" id="food-root" aria-busy="true">
        <div class="main__header">
            <h1 class="main__title">Food Database</h1>
            <p class="main__subtitle">Search and browse foods to add to your diary</p>
            <p class="main__subtitle" style="margin-top: 4px;"><?= e((string)$meta['total']) ?> foods found</p>
        </div>

        <form method="get" action="food.php" class="food-search animate-in">
            <svg class="food-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input
                type="text"
                name="search"
                value="<?= e((string)$meta['search']) ?>"
                placeholder="Search for foods... (e.g. chicken breast, banana, rice)"
                aria-label="Search foods"
            >
            <input type="hidden" name="category" value="<?= e((string)$meta['category']) ?>">
            <input type="hidden" name="limit" value="<?= e((string)$meta['limit']) ?>">
        </form>

        <div class="food-filters animate-in">
            <?php foreach ($categories as $category): ?>
                <?php $active = $meta['category'] === $category; ?>
                <a href="<?= e(build_query(['category' => $category, 'page' => 1])) ?>" class="food-filter-chip<?= $active ? ' active' : '' ?>">
                    <?= e($category) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (count($foods) === 0): ?>
            <div class="card animate-in" style="padding:24px;color:var(--gray-600);">
                No foods found. Try a different search or category.
            </div>
        <?php else: ?>
            <div class="food-grid" aria-live="polite">
                <?php foreach ($foods as $food): ?>
                    <div class="food-card animate-in">
                        <div class="food-card__image" style="padding:0;overflow:hidden;">
                            <img
                                src="<?= e((string)($food['image_url'] ?: 'https://placehold.co/600x400?text=No+Image')) ?>"
                                alt="<?= e((string)$food['name']) ?>"
                                loading="lazy"
                                style="width:100%;height:100%;object-fit:cover;"
                            >
                        </div>
                        <div class="food-card__body">
                            <div class="food-card__name"><?= e((string)$food['name']) ?></div>
                            <div class="food-card__serving">Serving: <?= e((string)$food['serving']) ?></div>
                            <div class="food-card__serving" style="margin-top:4px;color:var(--primary-dark);font-weight:600;">
                                <?= e((string)$food['category']) ?>
                            </div>
                            <div class="food-card__macros">
                                <div class="food-card__macro"><span class="food-card__macro-value"><?= e((string)$food['calories']) ?></span><span class="food-card__macro-label">Cal</span></div>
                                <div class="food-card__macro"><span class="food-card__macro-value"><?= e((string)$food['protein']) ?>g</span><span class="food-card__macro-label">Protein</span></div>
                                <div class="food-card__macro"><span class="food-card__macro-value"><?= e((string)$food['carbs']) ?>g</span><span class="food-card__macro-label">Carbs</span></div>
                                <div class="food-card__macro"><span class="food-card__macro-value"><?= e((string)$food['fat']) ?>g</span><span class="food-card__macro-label">Fat</span></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($meta['total_pages'] > 1): ?>
            <div class="food-filters" style="margin-top:20px;">
                <?php if ($meta['page'] > 1): ?>
                    <a class="food-filter-chip" href="<?= e(build_query(['page' => $meta['page'] - 1])) ?>">Previous</a>
                <?php endif; ?>

                <span class="food-filter-chip active">Page <?= e((string)$meta['page']) ?> of <?= e((string)$meta['total_pages']) ?></span>

                <?php if ($meta['page'] < $meta['total_pages']): ?>
                    <a class="food-filter-chip" href="<?= e(build_query(['page' => $meta['page'] + 1])) ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="js/main.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var root = document.getElementById('food-root');
            if (root) {
                root.setAttribute('aria-busy', 'false');
            }
        });
    </script>
</body>

</html>
