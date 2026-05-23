<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

const EXERCISE_CATEGORIES = ['Cardio', 'Strength', 'Calisthenics', 'Sports'];

function normalize_exercise_category(?string $value): ?string
{
    if ($value === null) {
        return null;
    }

    $value = trim($value);
    if ($value === '') {
        return null;
    }

    return in_array($value, EXERCISE_CATEGORIES, true) ? $value : null;
}

function sanitize_exercise_payload(array $input): array
{
    $name = isset($input['name']) ? trim((string)$input['name']) : '';
    $name = mb_substr($name, 0, 80);

    $category = normalize_exercise_category(isset($input['category']) ? (string)$input['category'] : null) ?? 'Cardio';

    $duration = filter_var($input['duration_minutes'] ?? null, FILTER_VALIDATE_INT);
    $duration = ($duration !== false && $duration > 0) ? $duration : 1;
    $duration = max(1, min(600, $duration));

    $calories = filter_var($input['calories_burned'] ?? null, FILTER_VALIDATE_FLOAT);
    $calories = ($calories !== false) ? $calories : 0.0;
    $calories = max(0.0, min(5000.0, (float)$calories));

    $exerciseId = isset($input['exercise_id']) ? trim((string)$input['exercise_id']) : '';
    if ($exerciseId === '') {
        $exerciseId = null;
    }

    return [
        'name' => $name,
        'category' => $category,
        'duration_minutes' => $duration,
        'calories_burned' => $calories,
        'exercise_id' => $exerciseId,
    ];
}

function insert_exercise(array $payload): array
{
    $pdo = get_pdo();
    $data = sanitize_exercise_payload($payload);

    if ($data['name'] === '') {
        throw new RuntimeException('Exercise name is required.');
    }

    $sql = "
        insert into public.exercises (name, category, duration_minutes, calories_burned, logged_at, exercise_id)
        values (:name, :category, :duration_minutes, :calories_burned, now(), :exercise_id)
        returning id, name, category, duration_minutes, calories_burned, logged_at
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $data['name']);
    $stmt->bindValue(':category', $data['category']);
    $stmt->bindValue(':duration_minutes', $data['duration_minutes'], PDO::PARAM_INT);
    $stmt->bindValue(':calories_burned', $data['calories_burned']);
    $stmt->bindValue(':exercise_id', $data['exercise_id']);
    $stmt->execute();

    $row = $stmt->fetch();
    return $row ?: $data;
}

function fetch_recent_exercises(int $limit = 5): array
{
    $pdo = get_pdo();
    $limit = max(1, min(20, $limit));

    $sql = "
        select name, category, duration_minutes, calories_burned, logged_at
        from public.exercises
        order by logged_at desc, created_at desc
        limit :limit
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function fetch_exercise_category_counts(): array
{
    $pdo = get_pdo();

    $sql = "
        select category, count(*) as total
        from public.exercises
        group by category
    ";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();

    $counts = array_fill_keys(EXERCISE_CATEGORIES, 0);
    foreach ($rows as $row) {
        $category = (string)$row['category'];
        if (array_key_exists($category, $counts)) {
            $counts[$category] = (int)$row['total'];
        }
    }

    return $counts;
}

function fetch_exercise_library_counts(): array
{
    $pdo = get_pdo();

    $sql = "
        select category, count(*) as total
        from public.exercise_library
        group by category
    ";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();

    $counts = array_fill_keys(EXERCISE_CATEGORIES, 0);
    foreach ($rows as $row) {
        $category = (string)$row['category'];
        if (array_key_exists($category, $counts)) {
            $counts[$category] = (int)$row['total'];
        }
    }

    return $counts;
}

function fetch_exercise_library(?string $category): array
{
    $pdo = get_pdo();
    $category = normalize_exercise_category($category);

    if ($category === null) {
        return [];
    }

    $sql = "
        select id, name, category, instructions
        from public.exercise_library
        where category = :category
        order by name asc
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':category', $category);
    $stmt->execute();

    return $stmt->fetchAll();
}
