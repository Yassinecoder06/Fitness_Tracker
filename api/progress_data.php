<?php
header('Content-Type: application/json');
require '../config/db_postgres.php';
$user_id = 1; // hardcoded until auth is integrated
try {
    $pdo = getDBConnection();
    // Fetch weight history
    $stmt = $pdo->prepare("SELECT weight, date FROM weight_logs WHERE user_id = ? ORDER BY date ASC");
    $stmt->execute([$user_id]);
    $weightLogs = $stmt->fetchAll();
    // Fetch current goals
    $stmt = $pdo->prepare("SELECT * FROM goals WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $goals = $stmt->fetch();
    $response = [
        'weight_logs' => $weightLogs,
        'goals' => $goals ?: null,
    ];
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
