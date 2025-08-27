<?php
// File: /teacher/pages/dashboard.php
// Phiên bản tái cấu trúc theo quy chuẩn dự án.
// Trang này giờ đây chỉ đóng vai trò sắp xếp các "Organism" con.
?>

<!-- NÂNG CẤP: Đọc tên giáo viên trực tiếp từ `data.teacher.name` của AlpineJS -->
<h1 class="text-2xl font-bold text-gray-800 mb-6">Chào buổi sáng, <span x-text="$store.app.teacher.name"></span>!</h1>

<!-- 
    Các component "Organism" dưới đây đã được chuẩn hóa để tự render bằng AlpineJS.
    Trang dashboard chỉ cần include chúng vào đúng vị trí.
-->
<?php include __DIR__ . '/../components/organisms/favorite_functions.php'; ?>

<?php include __DIR__ . '/../components/organisms/today_schedule.php'; ?>

<?php include __DIR__ . '/../components/organisms/attention_classes.php'; ?>

