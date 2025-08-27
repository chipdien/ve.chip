<?php
// File: /teacher/components/organisms/header.php
// Component này là một "Organism"
// Nó nhận vào biến $teacher từ file cha (index.php)
?>
<header class="fixed top-0 left-0 right-0 z-30 bg-white shadow-sm">
    <div class="container mx-auto px-4 h-16 flex justify-between items-center">

        <!-- Logo, click để về trang Dashboard -->
        <a href="#dashboard" @click.prevent="navigate('dashboard')">
            <img src="https://vietelite.s3.dualstack.ap-southeast-1.amazonaws.com/public/VietEliteLogo.png" alt="VietElite Logo" class="h-12">
        </a>

        <!-- Hiển thị chỉ khi đã có dữ liệu giáo viên trong store -->
        <template x-if="$store.app.teacher.id">
            <div class="flex items-center space-x-4">
                <!-- Nút thông báo -->
                <button class="relative text-gray-500 hover:text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-5-5.917V5a2 2 0 10-4 0v.083A6 6 0 004 11v3.159c0 .538-.214 1.055-.595 1.436L2 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <!-- Chấm thông báo (giả lập) -->
                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                </button>
            
                <!-- Menu Profile -->
                <div class="relative">
                    <button @click="$store.app.ui.profileMenuOpen = !$store.app.ui.profileMenuOpen" 
                            class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-200 hover:border-indigo-500">
                        <img src="<?= format_avatar_url($teacher['avatar'], $teacher['name']) ?>" alt="<?= htmlspecialchars($teacher['name']) ?>" class="w-full h-full object-cover">
                    </button>
                    
                    <!-- Dropdown menu được điều khiển bởi store -->
                    <div x-show="$store.app.ui.profileMenuOpen" @click.away="$store.app.ui.profileMenuOpen = false"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-40"
                        x-transition x-cloak>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Hồ sơ</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cài đặt</a>
                        <div class="border-t border-gray-100"></div>
                        <a href="/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Đăng xuất</a>
                    </div>
                </div>
            </div>
        </template>
    </div>
</header>
