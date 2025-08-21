<?php
// Luôn bắt đầu session trước khi thao tác với nó
session_start();

// --- BƯỚC 1: XÓA COOKIE "GHI NHỚ ĐĂNG NHẬP" ---
if (isset($_COOKIE['remember_me'])) {
    // Nạp các file cần thiết để có thể gọi Model
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/vendor/autoload.php';

    // Tách selector từ cookie
    list($selector, ) = explode(':', $_COOKIE['remember_me'], 2);
    
    if ($selector) {
        // Xóa token khỏi CSDL để vô hiệu hóa nó hoàn toàn.
        // Đây là bước bảo mật quan trọng.
        $userModel = new \App\Models\UserModel();
        $userModel->deleteTokenBySelector($selector);
    }

    // Xóa cookie trên trình duyệt bằng cách đặt thời gian hết hạn trong quá khứ.
    setcookie('remember_me', '', time() - 3600, '/');
}

// Hủy tất cả các biến session
$_SESSION = array();

// Nếu có cookie session, hãy xóa nó đi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Cuối cùng, hủy session
session_destroy();

// Chuyển hướng về trang đăng nhập với một thông báo
header("Location: login.php?logged_out=1");
exit;
