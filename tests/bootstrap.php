<?php
// tests/bootstrap.php - PHPUnit Test Bootstrap

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/functions.php';

