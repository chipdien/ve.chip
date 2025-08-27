<?php
// File: /teacher/pages/class_list.php
// Giao diện cho trang "Lớp học của tôi", được điều khiển bởi component `classList.js`.
?>
<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Lớp học của tôi</h1>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-4">
        <nav class="-mb-px flex space-x-6 overflow-x-auto">
            <button @click="activeTab = '1'" :class="{'border-green-600 text-green-700': activeTab === '1'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Đang hoạt động</button>
            <button @click="activeTab = '-1'" :class="{'border-orange-500 text-orange-600': activeTab === '-1'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Dự kiến</button>
            <button @click="activeTab = '2'" :class="{'border-indigo-500 text-indigo-600': activeTab === '2'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Hoàn thành</button>
            <button @click="activeTab = '0'" :class="{'border-gray-500 text-gray-600': activeTab === '0'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Dừng/Hủy</button>
        </nav>
    </div>

    <!-- 1. Trạng thái Tải (Loading State) -->
    <template x-if="isLoading">
        <div class="space-y-4"><div class="skeleton h-24 w-full rounded-xl"></div><div class="skeleton h-24 w-full rounded-xl"></div></div>
    </template>

    <!-- 2. Trạng thái Lỗi (Error State) -->
    <template x-if="error">
        <div class="bg-white p-6 rounded-xl shadow-sm text-center border border-red-200">
            <p class="text-red-600" x-text="error"></p>
        </div>
    </template>

    <!-- 3. Trạng thái Thành công (Success State) -->
    <template x-if="!isLoading && !error">
        <div class="space-y-4">
            <!-- Xử lý trường hợp không có lớp học nào -->
            <template x-if="!allClasses || allClasses.length === 0">
                <div class="bg-white p-6 rounded-xl shadow-sm text-center">
                    <p class="text-gray-600">Bạn hiện không phụ trách lớp học nào.</p>
                </div>
            </template>
            
            <!-- Lặp và hiển thị danh sách lớp học đã được lọc -->
            <div class="space-y-4">
                <template x-for="classItem in filteredClasses" :key="classItem.id">
                    <a :href="`#class_details/${classItem.id}`" @click.prevent="navigate('class_details', classItem.id)" 
                       class="block p-4 bg-white rounded-xl shadow-sm cursor-pointer hover:shadow-md hover:bg-gray-50 transition-all">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg text-gray-800" x-text="classItem.code"></h3>
                            <!-- Hiển thị mã cơ sở với màu riêng -->
                            <span class="px-2 py-1 text-xs font-bold rounded"
                                  :class="getCenterColor(classItem.center_code)"
                                  x-text="classItem.center_code || 'N/A'">
                            </span>
                        </div>
                        <p class="text-gray-600 font-bold" x-text="classItem.friendly_class_teachers"></p>
                        <p class="text-gray-600" x-text="classItem.schedule"></p>
                    </a>
                </template>
            </div>

            <!-- Lặp và hiển thị danh sách lớp học -->
            <!-- <template x-for="classItem in classes" :key="classItem.id">
                <a :href="`#classes/${classItem.id}`" @click.prevent="navigate('classes', classItem.id)" 
                   class="block p-4 bg-white rounded-xl shadow-sm cursor-pointer hover:shadow-md hover:bg-gray-50 transition-all">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="font-bold text-lg text-gray-800" x-text="classItem.code"></h3>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full" 
                              :class="{ 
                                  'text-green-800 bg-green-100': classItem.status_color === 'green', 
                                  'text-blue-800 bg-blue-100': classItem.status_color === 'blue' 
                              }" 
                              x-text="classItem.status">
                        </span>
                    </div>
                    <p class="text-gray-600" x-text="classItem.name"></p>
                </a>
            </template> -->
        </div>
    </template>
</div>
