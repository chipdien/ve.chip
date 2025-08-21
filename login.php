<?php
session_start();

// Chuyển hướng nếu người dùng đã đăng nhập
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Tải các file cần thiết
require_once __DIR__ . '/config.php'; 
require_once __DIR__ . '/helpers.php'; 
require_once __DIR__ . '/vendor/autoload.php';

// Sử dụng các service cần thiết
use App\Services\ApiService;
use App\Services\AuthService;
use App\Services\AuthNocobaseService;

// --- [CẬP NHẬT] Khởi tạo các service theo đúng thứ tự phụ thuộc ---
$apiService = new ApiService(); // Khởi tạo ApiService trước
$authService = new AuthService();
$authNocobaseService = new AuthNocobaseService($apiService); // Truyền ApiService vào AuthNocobaseService

// Khai báo các biến
$message = '';
$message_type = ''; // 'success' hoặc 'error'
$active_tab = 'nocobase'; // Tab mặc định
$error_map = [
    'invalid_token' => 'Liên kết không hợp lệ.',
    'expired_token' => 'Liên kết đã hết hạn. Vui lòng yêu cầu một liên kết mới.'
];

$url_redirect = $_SESSION['return_to'] ?? 'index.php';

// 1. Xử lý đăng nhập bằng Magic Link (từ email)
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $result = $authService->loginWithToken($token);

    if (is_array($result)) {
        $_SESSION['user'] = $result;
        header('Location: ' . $url_redirect);
        exit;
    } else {
        $message = $error_map[$result] ?? 'Đã xảy ra lỗi không xác định. Vui lòng thử lại.';
        $message_type = 'error';
    }
}

// 2. Xử lý các yêu cầu POST từ form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = trim($_POST['email'] ?? '');

    // Xử lý đăng nhập bằng mật khẩu portal
    if ($action === 'login_password') {
        $active_tab = 'password';
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        if (empty($email) || empty($password)) {
            $message = 'Vui lòng nhập đầy đủ email và mật khẩu.';
            $message_type = 'error';
        } else {
            $result = $authService->loginWithPortalPassword($email, $password, $rememberMe);
            if (is_array($result)) {
                $_SESSION['user'] = $result;
                header('Location: ' . $url_redirect);
                exit;
            } else {
                $message = 'Email hoặc mật khẩu không chính xác.';
                $message_type = 'error';
            }
        }
    }
    // Xử lý đăng nhập bằng tài khoản Nocobase
    elseif ($action === 'login_nocobase') {
        $active_tab = 'nocobase';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $message = 'Vui lòng nhập đầy đủ email và mật khẩu Nocobase.';
            $message_type = 'error';
        } else {
            // Sử dụng AuthNocobaseService đã được cập nhật
            $result = $authNocobaseService->login($email, $password);
            if (is_array($result)) {
                // Lưu thông tin user và token vào session
                $_SESSION['user'] = $result['user'];
                $_SESSION['nocobase_token'] = $result['token'];

                header('Location: ' . $url_redirect);
                exit;
            } else {
                $message = 'Tài khoản hoặc mật khẩu Nocobase không chính xác.';
                $message_type = 'error';
            }
        }
    }
    // Xử lý yêu cầu gửi Magic Link
    elseif ($action === 'request_magic_link') {
        $active_tab = 'magic_link';
        if (empty($email)) {
             $message = 'Vui lòng nhập địa chỉ email.';
             $message_type = 'error';
        } else {
            $token = $authService->generateAndSendLoginToken($email);
            if ($token) {
                $message = 'Một liên kết đăng nhập đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.';
                $message_type = 'success';
            } else {
                $message = 'Email không tồn tại trong hệ thống.';
                $message_type = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Cổng thông tin Giáo viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .tab-button {
            transition: all 0.2s ease-in-out;
            border-bottom: 2px solid transparent;
        }
        .tab-button.active {
            border-bottom-color: #4f46e5; /* indigo-600 */
            color: #1f2937; /* gray-800 */
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen py-12">

<div class="w-full max-w-lg">
    <div class="bg-white shadow-lg rounded-xl px-8 pt-6 pb-8 mb-4">
        <div class="mb-6 text-center">
            <img src="https://vietelite.edu.vn/wp-content/uploads/2022/06/logo-vietelite-xanh-768x364.png" alt="VietElite Logo" class="w-48 mx-auto">
            <h1 class="text-2xl font-bold text-gray-700 mt-4">Cổng thông tin Giáo viên</h1>
        </div>

        <!-- Tab Buttons -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <button id="tab-nocobase" class="tab-button whitespace-nowrap py-3 px-1 text-base font-medium text-gray-500 hover:text-gray-700">
                    Tài khoản Nocobase
                </button>
                <button id="tab-password" class="tab-button whitespace-nowrap py-3 px-1 text-base font-medium text-gray-500 hover:text-gray-700">
                    Mật khẩu Portal
                </button>
                <button id="tab-magic-link" class="tab-button whitespace-nowrap py-3 px-1 text-base font-medium text-gray-500 hover:text-gray-700">
                    Magic Link
                </button>
            </nav>
        </div>

        <!-- Message Area -->
        <?php if (!empty($message)): ?>
            <?php $bgColor = $message_type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?>
            <div class="<?= $bgColor ?> border px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Container -->
        <div>
            <!-- Password Login Form -->
            <form id="form-password" method="POST" action="login.php" class="">
                <input type="hidden" name="action" value="login_password">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email-password">Email</label>
                    <input class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" id="email-password" name="email" type="email" placeholder="Nhập email của bạn" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Mật khẩu</label>
                    <input class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" id="password" name="password" type="password" placeholder="******************" required>
                </div>
                <div class="mb-6 flex items-center">
                    <input class="mr-2 leading-tight" type="checkbox" id="remember_me" name="remember_me">
                    <label class="text-sm text-gray-600" for="remember_me">
                        Ghi nhớ đăng nhập
                    </label>
                </div>
                <button class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline" type="submit">
                    Đăng nhập
                </button>
            </form>

            <!-- Nocobase Login Form -->
            <form id="form-nocobase" method="POST" action="login.php" class="hidden">
                <input type="hidden" name="action" value="login_nocobase">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email-nocobase">Email Nocobase</label>
                    <input class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" id="email-nocobase" name="email" type="email" placeholder="Nhập email Nocobase" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password-nocobase">Mật khẩu Nocobase</label>
                    <input class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" id="password-nocobase" name="password" type="password" placeholder="******************" required>
                </div>
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline" type="submit">
                    Đăng nhập Nocobase
                </button>
            </form>

            <!-- Magic Link Form -->
            <form id="form-magic-link" method="POST" action="login.php" class="hidden">
                 <input type="hidden" name="action" value="request_magic_link">
                 <p class="text-sm text-gray-600 mb-4">Chúng tôi sẽ gửi một liên kết đăng nhập tạm thời đến email của bạn. Bạn không cần nhập mật khẩu.</p>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email-magic">Email</label>
                    <input class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" id="email-magic" name="email" type="email" placeholder="Nhập email của bạn" required>
                </div>
                <button class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline" type="submit">
                    Gửi liên kết đăng nhập
                </button>
            </form>
        </div>
    </div>
    <p class="text-center text-gray-500 text-xs">
        &copy;<?= date('Y') ?> VietElite Education. All rights reserved.
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = {
            'password': {
                button: document.getElementById('tab-password'),
                form: document.getElementById('form-password')
            },
            'nocobase': {
                button: document.getElementById('tab-nocobase'),
                form: document.getElementById('form-nocobase')
            },
            'magic_link': {
                button: document.getElementById('tab-magic-link'),
                form: document.getElementById('form-magic-link')
            }
        };

        const activeTabFromPHP = '<?= $active_tab ?>';

        function switchTab(activeKey) {
            for (const key in tabs) {
                const isActive = key === activeKey;
                tabs[key].button.classList.toggle('active', isActive);
                tabs[key].form.classList.toggle('hidden', !isActive);
            }
        }

        for (const key in tabs) {
            tabs[key].button.addEventListener('click', () => switchTab(key));
        }

        switchTab(activeTabFromPHP);
    });
</script>

</body>
</html>
