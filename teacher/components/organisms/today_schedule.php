<?php
// File: /teacher/components/organisms/today_schedule.php
// Phiên bản tái cấu trúc theo quy chuẩn dự án
?>
<!-- Component chỉ hiển thị nếu có dữ liệu trong data.todaySchedule -->
<template x-if="$store.app.data.todaySchedule && $store.app.data.todaySchedule.items && $store.app.data.todaySchedule.items.length > 0">
    <div class="bg-white p-4 rounded-xl shadow-sm mb-6">
        <h2 class="font-bold text-gray-700 mb-3">Lịch dạy hôm nay</h2>
        <div class="space-y-3">
            <!-- Lặp qua dữ liệu từ state của AlpineJS -->
            <template x-for="session in $store.app.data.todaySchedule.items" :key="session.session_id">
                <!-- Mỗi ca học là một link điều hướng đến trang chi tiết ca học -->
                <a href="`#session_details/${session.id}`" @click.prevent="navigate('session_details', session.session_id)" 
                   class="flex items-center p-3 bg-green-50 rounded-lg cursor-pointer hover:bg-green-100 transition-colors">
                    
                    <!-- Cột thời gian -->
                    <div class="w-20 text-center">
                        <p class="font-bold text-green-700" x-text="formatTimeRange(session.from, session.to)"></p>
                    </div>

                    <!-- Cột thông tin chi tiết -->
                    <div class="flex-1 ml-3">
                        <p class="font-semibold text-gray-800" x-text="`${session.class_code}`"></p>
                        <p class="text-sm text-gray-700" x-text="`${session.subject || ''}`"></p>
                        <p class="text-sm text-gray-500" x-text="`${session.center_name} ${session.room ? ' - '+session.room : ''}`"></p>
                    </div>

                    <!-- Icon mũi tên -->
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </template>
        </div>
    </div>
</template>

<!-- Hiển thị thông báo nếu không có lịch dạy -->
<template x-if="!$store.app.data.todaySchedule || !$store.app.data.todaySchedule.items || $store.app.data.todaySchedule.items.length === 0">
     <div class="bg-white p-4 rounded-xl shadow-sm mb-6">
        <h2 class="font-bold text-gray-700 mb-3">Lịch dạy hôm nay</h2>
        <p class="text-gray-500 italic">Hôm nay bạn không có lịch dạy.</p>
    </div>
</template>
