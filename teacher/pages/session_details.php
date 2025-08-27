<?php
// File: /teacher/pages/session_details.php
// Phiên bản tái cấu trúc theo quy chuẩn dự án
?>
<div>
    <!-- 1. Trạng thái Tải (Loading State) -->
    <template x-if="isLoading">
        <div class="space-y-4">
            <div class="skeleton h-8 w-1/3 rounded-md"></div>
            <div class="skeleton h-32 w-full rounded-xl"></div>
            <div class="skeleton h-48 w-full rounded-xl"></div>
        </div>
    </template>

    <!-- 2. Trạng thái Lỗi (Error State) -->
    <template x-if="error">
        <div class="text-center p-8 bg-white rounded-xl shadow-sm border border-red-200">
            <h2 class="text-xl font-bold text-red-600">Đã xảy ra lỗi</h2>
            <p class="text-gray-600 mt-2" x-text="error"></p>
            <button @click="navigate(backButtonTarget.target)" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <span x-text="backButtonTarget.label"></span>
            </button>
        </div>
    </template>

    <!-- 3. Trạng thái Thành công (Success State) -->
    <template x-if="details && !isLoading && !error">
        <div>
            <!-- Nút quay lại động -->
            <button @click="navigate(backButtonTarget.target)" 
                    class="text-green-600 mb-4 inline-flex items-center group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span x-text="backButtonTarget.label"></span>
            </button>
            
            <!-- Header thông tin ca học -->
            <div class="bg-white p-4 rounded-xl shadow-sm mb-6">
                <h1 class="text-2xl font-bold text-gray-800" x-text="details.class_code"></h1>
                <!-- <p class="text-lg text-green-700 font-semibold" x-text="details.content"></p> -->
                <div class="mt-2 border-t pt-3 text-gray-600 text-sm space-y-2">
                    <p><strong>Ngày:</strong> <span x-text="`${details.day}, ${formatDate(details.date)}`"></span></p>
                    <p><strong>Thời gian:</strong> <span x-text="formatTimeRange(details.from, details.to)"></span></p>
                    <p><strong>Địa điểm:</strong> <span x-text="details.center_name"></span></p>
                    <p><strong>ID ca học:</strong> <span x-text="details.session_id"></span></p>
                </div>
            </div>

            <!-- Khu vực Accordion cho các nhiệm vụ -->
            <div class="space-y-2">
                <!-- Section 1: Điểm danh & Feedback -->
                <?php include __DIR__ . '/../components/organisms/panel_attendance.php' ?> 

                <!-- Section 2: Nội dung buổi học -->
                <?php include __DIR__ . '/../components/organisms/panel_lesson_content.php' ?> 
                
                <!-- Section 3: Bài tập về nhà -->
                <?php include __DIR__ . '/../components/organisms/panel_lesson_exercice.php' ?> 

                <!-- Section 4: Chốt ca & Đánh giá -->
                <?php include __DIR__ . '/../components/session/finalize_session_panel.php'; ?> 

            </div>
        </div>
    </template>
</div>
