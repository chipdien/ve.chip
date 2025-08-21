<?php
session_start();

// Nạp các file cần thiết để có thể gọi Service
require_once __DIR__ . '/config.php';  
require_once __DIR__ . '/helpers.php';  
require_once __DIR__ . '/vendor/autoload.php';

// Nếu chưa có session, thử đăng nhập bằng cookie
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
    $authService = new \App\Services\AuthService();
    $user = $authService->loginWithCookie();
    if ($user) {
        $_SESSION['user'] = $user;
    } else {
        // Xóa cookie không hợp lệ
        setcookie('remember_me', '', time() - 3600, '/');
    }
}

// Kiểm tra lại lần nữa, nếu vẫn chưa đăng nhập thì chuyển hướng
if (!isset($_SESSION['user'])) {
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    header('Location: /login.php');
    exit;
}

// Biến $user sẽ có sẵn cho tất cả các trang
$user = $_SESSION['user'];

// Kết nối với CSDL
$db = get_db_connection();
function get_db_connection() {
    // Chỉ tạo kết nối một lần
    static $db = null;
    if ($db === null) {
        $db = new Medoo\Medoo([
            'type'      => 'mysql',
            'host'      => \DB_HOST,
            'database'  => \DB_NAME,
            'username'  => \DB_USER,
            'password'  => \DB_PASS,
            'charset'   => \DB_CHARSET,
            'collation' => \DB_COLLATION,
            'port'      => \DB_PORT,
        ]);
    }
    return $db;
} 