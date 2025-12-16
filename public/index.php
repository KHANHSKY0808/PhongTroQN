<?php
// D:\XAMPP\HTDOCS\PHONGTROQN\public\index.php

// Bật hiển thị lỗi khi develop (tắt khi lên host thật)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Require file functions (chứa toàn bộ hàm hỗ trợ + config + route)
require_once __DIR__ . '/../app/functions.php';