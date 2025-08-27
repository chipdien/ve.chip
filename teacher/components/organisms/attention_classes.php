<?php
// File: /teacher/components/organisms/attention_classes.php
// Phiên bản tái cấu trúc theo quy chuẩn dự án
?>
<!-- Component chỉ hiển thị nếu có dữ liệu trong data.attentionClasses -->
<template x-if="$store.app.data.attentionClasses && $store.app.data.attentionClasses.items && $store.app.data.attentionClasses.items.length > 0">
    <div class="mb-6">
        <h2 class="font-bold text-gray-700 mb-3">Lớp cần chú ý</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Lặp qua dữ liệu từ state của AlpineJS -->
            <template x-for="classItem in $store.app.data.attentionClasses.items" :key="classItem.id">
                <!-- Mỗi lớp là một link điều hướng đến trang chi tiết -->
                <a href="#" @click.prevent="navigate('classes', classItem.id)"
                   class="bg-white p-4 rounded-xl shadow-sm cursor-pointer hover:shadow-md transition-shadow border-l-4 border-red-500">
                    
                    <p class="font-bold text-red-600" x-text="classItem.code"></p>
                    <p class="text-sm text-gray-500 mt-1">
                        Chuyên cần: 
                        <span class="font-medium" x-text="`${classItem.attendance_rate}%`"></span>
                    </p>
                </a>
            </template>
        </div>
    </div>
</template>
