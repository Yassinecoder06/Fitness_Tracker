<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$autoloadPath = $projectRoot . '/vendor/autoload.php';

if (!is_file($autoloadPath)) {
    throw new RuntimeException('Missing Composer autoload file. Run: composer require vlucas/phpdotenv');
}

require_once $autoloadPath;

$dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
$dotenv->safeLoad();

