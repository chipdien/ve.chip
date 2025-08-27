<?php // File: /teacher/pages/class_details.php ?>
<div>
    <template x-if="isLoading">
        <div class="space-y-4">
            <div class="skeleton h-8 w-1/3 rounded-md"></div>
            <div class="skeleton h-48 w-full rounded-xl"></div>
        </div>
    </template>

    <template x-if="error">
        <div class="text-center p-8 bg-white rounded-xl shadow-sm border border-red-200">
            <h2 class="text-xl font-bold text-red-600">Đã xảy ra lỗi</h2>
            <p class="text-gray-600 mt-2" x-text="error"></p>
            <button @click="navigate('classes')" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg">Về trang Lớp học</button>
        </div>
    </template>

    <template x-if="details && !isLoading && !error">
        <div>
            <button @click="navigate('classes')" class="text-green-600 mb-4 inline-flex items-center group">
                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Quay lại Danh sách lớp
            </button>
            <h1 class="text-2xl font-bold text-gray-800 mb-4" x-text="details.info.code"></h1>
            <!-- <p class="text-lg text-green-700 font-semibold mb-4" x-text="details.info.name"></p> -->
            <p class="text-md text-green-700 font-medium ">GV: <span x-text="details.info.friendly_class_teachers"></span></p>
            <p class="text-sm text-gray-700 font-medium mb-4" x-text="details.info.schedule"></p>

            <div class="border-b border-gray-200 mb-4">
                <nav class="-mb-px flex space-x-6">
                    <button @click="navigate('class_details', details.info.id, 'students'); activeTab = 'students'" :class="{'border-green-600 text-green-700': activeTab === 'students'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Học sinh</button>
                    <button @click="navigate('class_details', details.info.id, 'sessions'); activeTab = 'sessions'" :class="{'border-green-600 text-green-700': activeTab === 'sessions'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Ca học</button>
                    <button @click="navigate('class_details', details.info.id, 'documents'); activeTab = 'documents'" :class="{'border-green-600 text-green-700': activeTab === 'documents'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">Tài liệu</button>
                </nav>
            </div>

            <div x-show="activeTab === 'students'">
                <?php include __DIR__ . '/../components/class/students_panel.php'; ?>
            </div>
            <div x-show="activeTab === 'sessions'">
                <?php include __DIR__ . '/../components/class/sessions_panel.php'; ?>
            </div>
            <div x-show="activeTab === 'documents'">
                <?php include __DIR__ . '/../components/class/documents_panel.php'; ?>
            </div>
        </div>
    </template>
</div>
