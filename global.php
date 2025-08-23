<?php
require_once __DIR__ . '/env.php';

$hostName   = getenv('DB_HOST');
$userName   = getenv('DB_USER');
$password   = getenv('DB_PASS');
$databaseName = getenv('DB_NAME');

