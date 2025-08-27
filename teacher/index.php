<?php 
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/check_permission.php';

use App\Services\TeacherDataService;
use App\Services\TeacherDashboardService;

// Khởi tạo DataService để lấy dữ liệu
$dataService = new TeacherDataService();

// Tải tất cả dữ liệu cần thiết để khởi tạo ứng dụng
$initialAppData = $dataService->getInitialAppData();

if ($initialAppData === null) {
    die("Không thể tải dữ liệu. Người dùng không phải là giáo viên hoặc có lỗi xảy ra.");
}
// dump($initialAppData); die(); 
// Dữ liệu này vẫn cần thiết cho component header.php được render bởi server
$teacher = $initialAppData['teacher'];
$initialUserData = [
    'id' => $user['id'],
    'username' => $user['username'],
    'nickname' => $user['nickname'],
    'email' => $user['email'],
    'phone' => $user['phone'],
    'opt_school_year' => $user['opt_school_year'],
    'status' => $user['status'],
    'roles' => $user['roles'],
];
// $favoriteFunctions = $initialAppData['favoriteFunctions'];
// $todaySchedule = $initialAppData['todaySchedule'];
// $attentionClasses = $initialAppData['attentionClasses'];

// Chuyển đổi toàn bộ dữ liệu sang JSON cho AlpineJS
$initialDataJson = json_encode($initialAppData);



?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng thông tin Giáo viên</title>

    <!-- Cấu hình cho Progressive Web App (PWA) -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#16a34a">
    <!-- Cho iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="GV App">
    <!-- --------------------------------------------- -->

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tải thư viện Vditor -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vditor/dist/index.css" />
    <script src="https://cdn.jsdelivr.net/npm/vditor/dist/index.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<!-- Khởi tạo component `app` chính, đóng vai trò điều phối routing -->
<body class="bg-gray-100" x-data="app" x-cloak>

    <!-- Header (Organism) -->
    <?php include __DIR__ . '/components/organisms/header.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-4 mt-16 mb-16">

        <!-- Skeleton UI chung: Hiển thị khi ứng dụng đang tải dữ liệu ban đầu -->
        <div x-show="$store.app.ui.isInitialLoading" x-transition>
            <div class="space-y-6">
                <div class="skeleton h-8 w-1/2 rounded-md"></div>
                <div class="skeleton h-32 w-full rounded-xl"></div>
                <div class="skeleton h-24 w-full rounded-xl"></div>
            </div>
        </div>
        
        <!-- Container chính của ứng dụng: Hiển thị sau khi tải xong -->
        <div x-show="!$store.app.ui.isInitialLoading" x-transition.opacity.duration.500ms>
            
            <template x-if="$store.app.routing.page === 'dashboard'">
                <div x-data="dashboard">
                    <?php include __DIR__ . '/pages/dashboard.php'; ?>
                </div>
            </template>

            <template x-if="$store.app.routing.page === 'classes'">
                <div x-data="classList">
                    <?php include __DIR__ . '/pages/class_list.php'; ?>
                </div>
            </template>

            <template x-if="$store.app.routing.page === 'class_details'">
                <div x-data="classDetails">
                    <?php include __DIR__ . '/pages/class_details.php'; ?>
                </div>
            </template>


            <template x-if="$store.app.routing.page === 'weekly_schedule'">
                <div x-data="weeklySchedule">
                    <?php include __DIR__ . '/pages/weekly_schedule.php'; ?>
                </div>
            </template>
            
            <template x-if="$store.app.routing.page === 'session'">
                <div x-data="sessionList">
                    <?php include __DIR__ . '/pages/session_list.php'; ?>
                </div>
            </template>
            
            <template x-if="$store.app.routing.page === 'session_details'">
                <div x-data="sessionDetails">
                    <?php include __DIR__ . '/pages/session_details.php'; ?>
                </div>
            </template>

            <template x-if="$store.app.routing.page === 'student_details'">
                <div x-data="studentDetails">
                    <?php include __DIR__ . '/pages/student_details.php'; ?>
                </div>
            </template>
        </div>

    </main>

    <!-- Bottom Navigation (Organism) -->
    <?php include __DIR__ . '/components/organisms/bottom_nav.php'; ?>

        
    <!-- Truyền dữ liệu đầy đủ vào một tag ẩn để app.js có thể đọc -->
    <script id="initial-data-store" type="application/json">
        <?= json_encode($initialAppData) ?>
    </script>
    <script id="initial-user-store" type="application/json">
        <?= json_encode($initialUserData) ?>
    </script>

    <!-- AlpineJS & App Scripts (sử dụng type="module") -->
    <script src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script src="assets/js/helpers.js"></script>
    <script src="assets/js/app_v4.js" type="module" ></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>