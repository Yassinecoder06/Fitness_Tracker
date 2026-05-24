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

function insert_exercise(array $payload, int $userId): array
{
    $pdo = get_pdo();
    $data = sanitize_exercise_payload($payload);

    if ($data['name'] === '') {
        throw new RuntimeException('Exercise name is required.');
    }

    // Build column/value lists — user_id always exists per migration schema
    $columns = ['user_id', 'name', 'category', 'duration_minutes', 'calories_burned', 'logged_at'];
    $values  = [':user_id', ':name', ':category', ':duration_minutes', ':calories_burned', 'now()'];

    if (!empty($data['exercise_id'])) {
        $columns[] = 'exercise_id';
        $values[]  = ':exercise_id';
    }

    $sql = sprintf(
        "INSERT INTO public.exercises (%s) VALUES (%s) RETURNING id, name, category, duration_minutes, calories_burned, logged_at",
        implode(', ', $columns),
        implode(', ', $values)
    );

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':name', $data['name']);
    $stmt->bindValue(':category', $data['category']);
    $stmt->bindValue(':duration_minutes', $data['duration_minutes'], PDO::PARAM_INT);
    $stmt->bindValue(':calories_burned', $data['calories_burned']);
    if (!empty($data['exercise_id'])) {
        $stmt->bindValue(':exercise_id', $data['exercise_id']);
    }
    $stmt->execute();

    $row = $stmt->fetch();
    return $row ?: $data;
}

function fetch_recent_exercises(int $userId, int $limit = 5): array
{
    $pdo = get_pdo();
    $limit = max(1, min(20, $limit));

    $sql = "
        SELECT name, category, duration_minutes, calories_burned, logged_at
        FROM public.exercises
        WHERE user_id = :user_id
        ORDER BY logged_at DESC, created_at DESC
        LIMIT :limit
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function fetch_exercise_category_counts(): array
{
    $pdo = get_pdo();

    $sql = "
        SELECT category, count(*) AS total
        FROM public.exercises
        GROUP BY category
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
        SELECT category, count(*) AS total
        FROM public.exercise_library
        GROUP BY category
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
        SELECT id, name, category, instructions
        FROM public.exercise_library
        WHERE category = :category
        ORDER BY name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':category', $category);
    $stmt->execute();

    return $stmt->fetchAll();
}
