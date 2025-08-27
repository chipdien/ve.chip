<!-- File: /teacher/components/session/organisms/finalize_session_panel.php -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div @click="toggleSection('finalize')" class="p-4 flex justify-between items-center cursor-pointer hover:bg-gray-50">
        <h2 class="font-bold text-lg text-gray-700">Chốt ca & Đánh giá</h2>
        <svg class="h-5 w-5 text-gray-400" :class="{'rotate-180': activeSection === 'finalize'}" viewBox="0 0 20 20" fill="currentColor"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
    </div>
    <div x-show="activeSection === 'finalize'" x-collapse class="px-4 pb-4 border-t">
        <div class="pt-3 space-y-4">
            <!-- Dòng 1: Chốt sĩ số -->
            <div class="flex justify-between items-center">
                <label class="text-sm font-medium text-gray-700">Sĩ số có mặt (tự động)</label>
                <input type="text" :value="presentStudentCount" class="w-20 text-center bg-gray-100 border-gray-300 rounded-md" disabled>
            </div>

            <!-- Dòng 2: Feedback vận hành -->
            <div class="space-y-3 pt-3 border-t">
                <h4 class="font-semibold text-gray-800">Feedback Vận hành</h4>
                <div class="flex justify-between items-center">
                    <label class="text-sm font-medium text-gray-700">Vệ sinh lớp học, trung tâm</label>
                    <?php $modelName = 'ratingHygiene'; include __DIR__ . '/../molecules/star_rating.php'; ?>
                </div>
                <div class="flex justify-between items-center">
                    <label class="text-sm font-medium text-gray-700">Trang thiết bị, cơ sở vật chất</label>
                    <?php $modelName = 'ratingFacilities'; include __DIR__ . '/../molecules/star_rating.php'; ?>
                </div>
                <div class="flex justify-between items-center">
                    <label class="text-sm font-medium text-gray-700">Công tác quản lý và vận hành</label>
                    <?php $modelName = 'ratingOperations'; include __DIR__ . '/../molecules/star_rating.php'; ?>
                </div>
            </div>
            
            <!-- Dòng 3: Feedback cá nhân -->
            <div class="space-y-3 pt-3 border-t">
                <h4 class="font-semibold text-gray-800">Feedback Cá nhân</h4>
                <div x-show="details.admin_name" class="flex justify-between items-center">
                    <label class="text-sm font-medium text-gray-700">Nhân viên giáo vụ (<span x-text="details.admin_name"></span>)</label>
                    <?php $modelName = 'ratingAdmin'; include __DIR__ . '/../molecules/star_rating.php'; ?>
                </div>
                <div x-show="details.assistant_name" class="flex justify-between items-center">
                    <label class="text-sm font-medium text-gray-700">Trợ giảng (<span x-text="details.assistant_name"></span>)</label>
                    <?php $modelName = 'ratingAssistant'; include __DIR__ . '/../molecules/star_rating.php'; ?>
                </div>
            </div>

            <!-- Nút Chốt ca -->
            <button @click="submitFinalizeFeedback()" :disabled="isSubmittingFeedback" class="mt-4 w-full bg-green-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-green-700 disabled:bg-gray-400">
                <span x-show="!isSubmittingFeedback">Chốt ca học & Gửi Feedback</span>
                <span x-show="isSubmittingFeedback">Đang gửi...</span>
            </button>
        </div>
    </div>
</div>
