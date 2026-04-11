<?php
function getDBConnection() {
    $host = 'localhost';
    $port = 5432;
    $dbname = 'fittrack';
    $user = 'postgres';
    $pass = '0000';

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("SET NAMES 'UTF8'");

    return $pdo;
}