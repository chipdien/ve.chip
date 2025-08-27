<?php
// File: /teacher/pages/weekly_schedule.php
// Giao diện cho trang "Lịch dạy trong tuần", được điều khiển bởi component `weeklySchedule.js`.
?>
<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Lịch dạy</h1>

    <!-- 1. Trạng thái Tải (Loading State) -->
    <template x-if="isLoading">
        <div class="space-y-3">
            <div class="skeleton h-20 w-full rounded-xl"></div>
            <div class="skeleton h-20 w-full rounded-xl"></div>
            <div class="skeleton h-20 w-full rounded-xl"></div>
            <div class="skeleton h-20 w-full rounded-xl"></div>
        </div>
    </template>

    <!-- 2. Trạng thái Lỗi (Error State) -->
    <template x-if="error">
        <div class="bg-white p-6 rounded-xl shadow-sm text-center border border-red-200">
            <p class="text-red-600" x-text="error"></p>
        </div>
    </template>

    <!-- 3. Trạng thái Thành công (Success State) -->
    <template x-if="!isLoading && !error">
        <div class="space-y-3">
            <template x-for="(day, index) in $store.app.data.weeklySchedule.items" :key="day.full_date">
                <div class="rounded-xl shadow-sm overflow-hidden transition-all duration-300"
                     :class="day.isToday ? 'bg-white border-2 border-green-600' : 'bg-white border border-gray-200'">
                    
                    <!-- Header của ngày -->
                    <div @click="toggleDay(day.full_date)" class="p-4 flex items-center cursor-pointer"
                         :class="day.isToday ? 'bg-green-50' : 'hover:bg-gray-50'">
                        <div class="text-center w-14 flex-shrink-0">
                            <p class="font-bold text-lg" :class="day.isToday ? 'text-green-700' : 'text-gray-700'" x-text="day.date.split('/')[0]"></p>
                            <p class="text-xs text-gray-500" x-text="`Thg ${day.date.split('/')[1]}`"></p>
                        </div>
                        <div class="ml-4 flex-grow">
                            <p class="font-bold" :class="day.isToday ? 'text-green-800' : 'text-gray-800'" x-text="day.day"></p>
                            <div x-show="expandedDate !== day.full_date" class="text-sm text-gray-500">
                                <span x-text="day.sessions.length"></span> ca học
                            </div>
                        </div>
                        <div class="transition-transform duration-300" :class="{'rotate-180': expandedDate === day.full_date}">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </div>
                    </div>
        
                    <!-- Nội dung chi tiết với giao diện 2 cột mới -->
                    <div x-show="expandedDate === day.full_date" x-collapse>
                        <div class="px-4 pb-4">
                            <template x-if="day.sessions.length === 0">
                                <p class="text-gray-500 italic py-2 border-t">Không có lịch dạy.</p>
                            </template>
                            <div class="space-y-3 border-t pt-3">
                                <template x-for="(session, sessionIndex) in day.sessions" :key="session.session_id"> <!-- day.full_date + '-' + sessionIndex -->
                                     <a href="#" @click.prevent="navigate('session_details', session.session_id)" 
                                        class="block p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="flex items-start space-x-3">
                                            <!-- Cột trái: Thời gian -->
                                            <div class="w-20 text-center flex-shrink-0 pt-1">
                                                <p class="font-semibold text-gray-700" x-text="formatTimeRange(session.from, session.to)"></p>
                                            </div>
                                            <!-- Cột phải: Thông tin chi tiết -->
                                            <div class="flex-grow border-l border-gray-200 pl-3">
                                                <!-- Dòng 1: Tên lớp và nội dung -->
                                                <div class="mb-1">
                                                    <p class="font-bold text-gray-800" x-text="session.class_code"></p>
                                                    <p class="text-sm text-gray-600" x-text="session.subject"></p>
                                                </div>
                                                <!-- Dòng 2: Địa điểm -->
                                                <div class="flex items-center text-sm text-gray-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                    <span x-text="session.center_name"></span>
                                                </div>
                                            </div>
                                        </div>
                                     </a>
                                </template>
                            </div>
                        </div>
                    </div>
        
                </div>
            </template>
        </div>

    </template>
</div>