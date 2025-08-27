<?php
// File: /teacher/components/organisms/favorite_functions.php
// Phiên bản tái cấu trúc theo quy chuẩn dự án
?>
<!-- Component này chỉ hiển thị nếu có dữ liệu trong data.favoriteFunctions -->
<template x-if="$store.app.favoriteFunctions && $store.app.favoriteFunctions.length > 0">
    <div class="mb-6">
         <h2 class="font-bold text-gray-700 mb-3">Chức năng ưa thích</h2>
         <div class="flex space-x-2 overflow-x-auto pb-3 no-scrollbar">
            <!-- Lặp qua dữ liệu từ state của AlpineJS -->
            <template x-for="func in $store.app.favoriteFunctions" :key="func.label">
                <!-- 
                    Sử dụng navigate() để điều hướng. 
                    Các route này là giả lập, bạn có thể thêm chúng vào validPages trong app.js sau này.
                -->
                <a href="#" @click.prevent="navigate(func.route)" 
                   class="flex-shrink-0 w-24 flex flex-col items-center justify-center p-3 bg-white rounded-xl shadow-sm text-center hover:bg-gray-50 transition-colors">
                    
                    <!-- Icon và màu sắc theo theme xanh lá -->
                    <div class="w-12 h-12 flex items-center justify-center bg-green-100 text-green-600 rounded-full mb-2" 
                         x-html="func.icon">
                    </div>
                    
                    <p class="text-xs font-medium text-gray-600" x-text="func.label"></p>
                </a>
            </template>
         </div>
    </div>
</template>