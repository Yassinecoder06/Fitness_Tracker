<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

const FOOD_CATEGORIES = ['All', 'Fruits', 'Vegetables', 'Protein', 'Grains', 'Dairy', 'Snacks'];

function sanitize_food_query_params(array $query): array
{
    $search = isset($query['search']) ? trim((string)$query['search']) : '';
    $search = mb_substr($search, 0, 100);

    $category = isset($query['category']) ? trim((string)$query['category']) : 'All';
    if (!in_array($category, FOOD_CATEGORIES, true)) {
        $category = 'All';
    }

    $page = filter_var($query['page'] ?? 1, FILTER_VALIDATE_INT);
    $page = ($page !== false && $page > 0) ? $page : 1;

    $limit = filter_var($query['limit'] ?? 24, FILTER_VALIDATE_INT);
    $limit = ($limit !== false) ? $limit : 24;
    $limit = max(1, min(50, $limit));

    return [
        'search' => $search,
        'category' => $category,
        'page' => $page,
        'limit' => $limit,
    ];
}

function build_food_filters(array $params): array
{
    $where = [];
    $bind = [];

    if ($params['search'] !== '') {
        $where[] = 'lower(name) like :search';
        $bind[':search'] = '%' . mb_strtolower($params['search']) . '%';
    }

    if ($params['category'] !== 'All') {
        $where[] = 'category = :category';
        $bind[':category'] = $params['category'];
    }

    $whereSql = count($where) ? 'where ' . implode(' and ', $where) : '';
    return [$whereSql, $bind];
}

function fetch_foods(array $query): array
{
    $pdo = get_pdo();
    $params = sanitize_food_query_params($query);
    [$whereSql, $bind] = build_food_filters($params);

    $offset = ($params['page'] - 1) * $params['limit'];

    $countSql = "select count(*) from public.foods {$whereSql}";
    $countStmt = $pdo->prepare($countSql);
    foreach ($bind as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = (int)$countStmt->fetchColumn();

    $dataSql = "
        select id, name, category, calories, protein, carbs, fat, serving, image_url
        from public.foods
        {$whereSql}
        order by name asc
        limit :limit offset :offset
    ";

    $dataStmt = $pdo->prepare($dataSql);
    foreach ($bind as $key => $value) {
        $dataStmt->bindValue($key, $value);
    }
    $dataStmt->bindValue(':limit', $params['limit'], PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $dataStmt->execute();
    $rows = $dataStmt->fetchAll();

    return [
        'foods' => $rows,
        'meta' => [
            'search' => $params['search'],
            'category' => $params['category'],
            'page' => $params['page'],
            'limit' => $params['limit'],
            'total' => $total,
            'total_pages' => max(1, (int)ceil($total / $params['limit'])),
        ],
    ];
}
