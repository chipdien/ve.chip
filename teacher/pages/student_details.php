<?php // File: /teacher/pages/student_details.php ?>
<div>
    <!-- ... (template loading và error tương tự các trang khác) ... -->

    <template x-if="details && !isLoading && !error">
        <div>
            <button @click="navigate(backButtonTarget.target)" class="text-green-600 mb-4 inline-flex items-center group">
                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span x-text="backButtonTarget.label"></span>
            </button>
            
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <!-- Phần Avatar và Tên -->
                <div class="flex flex-col items-center text-center border-b pb-6 mb-6">
                    <div class="relative">
                        <img :src="formatAvatarUrl(details.avatar,  details.full_name)" 
                             class="h-32 w-32 rounded-full object-cover border-4 border-gray-200">
                        <!-- (MỚI) Lớp phủ hiển thị khi đang tải -->
                        <div x-show="isUploadingAvatar" x-transition class="absolute inset-0 bg-white/80 rounded-full flex items-center justify-center">
                            <svg class="animate-spin h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>                   
                        <button @click="triggerAvatarUpload()" class="absolute bottom-0 right-0 bg-green-600 text-white p-2 rounded-full hover:bg-green-700">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                        </button>
                        <input type="file" x-ref="avatarInput" @change="handleAvatarUpload" class="hidden" accept="image/*">
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 mt-4" x-text="details.full_name"></h1>
                </div>

                <!-- Phần Thông tin chi tiết -->
                <div class="space-y-4">
                    <!-- Chế độ xem -->
                    <template x-if="!isEditing">
                        <div class="space-y-3">
                            <div><strong class="font-semibold">Ngày sinh:</strong> <span x-text="formatDate(details.dob) || 'Chưa cập nhật'"></span></div>
                            <div><strong class="font-semibold">Trường học:</strong> <span x-text="details.school || 'Chưa cập nhật'"></span></div>
                            <div><strong class="font-semibold">Mục tiêu học tập:</strong> <p class="text-gray-700" x-text="details.aspiration || 'Chưa cập nhật'"></p></div>
                            <div><strong class="font-semibold">Ghi chú:</strong> <p class="text-gray-700" x-text="details.note || 'Chưa có'"></p></div>
                            <button @click="isEditing = true" class="mt-4 w-full bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg">Chỉnh sửa</button>
                        </div>
                    </template>
                    <!-- Chế độ chỉnh sửa -->
                    <template x-if="isEditing">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium">Ngày sinh</label>
                                <input type="date" x-model="details.dob" class="mt-1 block w-full border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Trường học</label>
                                <input type="text" x-model="details.school" class="mt-1 block w-full border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Mục tiêu học tập</label>
                                <textarea x-model="details.aspiration" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Ghi chú</label>
                                <textarea x-model="details.note" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md"></textarea>
                            </div>
                            <div class="flex space-x-2">
                                <button @click="isEditing = false" class="w-1/2 bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg">Hủy</button>
                                <button @click="saveDetails()" class="w-1/2 bg-green-600 text-white font-bold py-2 px-4 rounded-lg" :disabled="saveStatus === 'saving'">
                                    <span x-show="saveStatus !== 'saving'">Lưu thay đổi</span>
                                    <span x-show="saveStatus === 'saving'">Đang lưu...</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
