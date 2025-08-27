<?php
// File: /teacher/pages/session_list.php
// Giao diện cho trang "Danh sách Ca học", được điều khiển bởi component `sessionList.js`.
?>
<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Danh sách Ca học</h1>

    <!-- Giao diện Bộ lọc -->
    <div class="mb-4">
        <label for="class-filter" class="block text-sm font-medium text-gray-700">Lọc theo lớp:</label>
        <select id="class-filter"
                x-model="sessions[sessions.activeTab].filterClassId"
                @change="applyClassFilter()"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
            <!-- Tùy chọn để xem tất cả -->
            <option :value="null">-- Tất cả các lớp --</option>
            <!-- Lặp qua danh sách lớp của giáo viên -->
            <template x-for="classItem in $store.app.classes" :key="classItem.id">
                <option :value="classItem.id" x-text="`${classItem.code} - ${classItem.name}`"></option>
            </template>
        </select>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-4">
        <nav class="-mb-px flex space-x-6">
            <button @click="changeSessionTab('past')" 
                    :class="{'border-green-600 text-green-700': sessions.activeTab === 'past', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': sessions.activeTab !== 'past'}" 
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                Đã & Đang diễn ra
            </button>
            <button @click="changeSessionTab('upcoming')" 
                    :class="{'border-green-600 text-green-700': sessions.activeTab === 'upcoming', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': sessions.activeTab !== 'upcoming'}" 
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                Sắp diễn ra
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div>
        <!-- Tab 1: Ca học đã & đang diễn ra -->
        <div x-show="sessions.activeTab === 'past'">
            
            <!-- Skeleton Loader -->
            <template x-if="sessions.past.isLoading && sessions.past.items.length === 0">
                <div class="space-y-3">
                    <div class="skeleton h-16 w-full rounded-lg"></div>
                    <div class="skeleton h-16 w-full rounded-lg"></div>
                </div>
            </template>

            <!-- Danh sách -->
            <div class="space-y-3">
                <template x-for="session in sessions.past.items" :key="session.id">
                    <a :href="`#session_details/${session.id}`" @click.prevent="navigate('session_details', session.id)" class="block p-3 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-start space-x-3">
                            <div class="w-28 text-center flex-shrink-0 pt-1">
                                <p class="font-bold text-xl text-green-700" x-text="session.date.split('-')[2] + '/' + session.date.split('-')[1]"></p>
                                <p class="font-medium text-gray-600" x-text="formatTimeRange(session.from, session.to)"></p>
                            </div>
                            <div class="flex-grow border-l border-gray-200 pl-3">
                                <div class="mb-1">
                                    <p class="font-bold text-gray-800" x-text="session.class_code"></p>
                                    <p class="text-sm text-gray-600 pt-2" x-text="session.content"></p>
                                </div>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
            <!-- Infinite Scroll Trigger -->
            <div x-show="sessions.past.hasMore" x-intersect:enter="fetchSessions('past')" class="text-center p-4">
                <span x-show="sessions.past.isLoading && sessions.past.items.length > 0">Đang tải thêm...</span>
            </div>
        </div>

        <!-- Tab 2: Ca học sắp diễn ra -->
        <div x-show="sessions.activeTab === 'upcoming'">

            <!-- Skeleton Loader -->
            <template x-if="sessions.upcoming.isLoading && sessions.upcoming.items.length === 0">
                 <div class="space-y-3">
                    <div class="skeleton h-16 w-full rounded-lg"></div>
                    <div class="skeleton h-16 w-full rounded-lg"></div>
                </div>
            </template>

            <!-- Danh sách -->
            <div class="space-y-3">
                <template x-for="session in sessions.upcoming.items" :key="session.id">
                    <a :href="`#session_details/${session.id}`" @click.prevent="navigate('session_details', session.id)" class="block p-3 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-start space-x-3">
                            <div class="w-28 text-center flex-shrink-0 pt-1">
                                <p class="font-bold text-xl text-green-700" x-text="session.date.split('-')[2] + '/' + session.date.split('-')[1]"></p>
                                <p class="font-medium text-gray-600" x-text="formatTimeRange(session.from, session.to)"></p>
                            </div>
                            <div class="flex-grow border-l border-gray-200 pl-3">
                                <div class="mb-1">
                                    <p class="font-bold text-gray-800" x-text="session.class_code"></p>
                                    <p class="text-sm text-gray-600 pt-2" x-text="session.content"></p>
                                </div>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
            <!-- Infinite Scroll Trigger -->
            <div x-show="sessions.upcoming.hasMore" x-intersect:enter="fetchSessions('upcoming')" class="text-center p-4">
                 <span x-show="sessions.upcoming.isLoading && sessions.upcoming.items.length > 0">Đang tải thêm...</span>
            </div>
        </div>
    </div>
</div>
