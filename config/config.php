<?php
$servername = "localhost"; 
$username = "root";     
$password = "";            
$dbname = "phongtroqn";         

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);
if (!defined('DB_CONFIG_INCLUDED')) {
    define('DB_CONFIG_INCLUDED', true);
// ==== CẤU HÌNH DATABASE ====
define('DB_HOST', 'localhost');
define('DB_NAME', 'phongtroqn');
define('DB_USER', 'root');
define('DB_PASS', '');

// ==== CẤU HÌNH URL ====
define('BASE_URL', '/PhongTroQN/public');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', 'http://localhost/PhongTroQN');
define('UPLOADS_PATH', __DIR__ . '/../uploads');
define('ROOT', dirname(__DIR__));
// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
}
?>
