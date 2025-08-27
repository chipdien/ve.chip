<?php // File: /teacher/pages/notification_settings.php ?>
<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Cài đặt Thông báo</h1>
    <div class="bg-white p-6 rounded-xl shadow-sm">
        <div x-show="!isSupported" class="p-4 bg-yellow-100 text-yellow-800 rounded-md">
            Trình duyệt của bạn không hỗ trợ tính năng thông báo đẩy.
        </div>

        <div x-show="isSupported" class="space-y-4">
            <div>
                <h3 class="font-semibold">Trạng thái quyền</h3>
                <p :class="{ 'text-green-600': permission === 'granted', 'text-red-600': permission === 'denied' }"
                   class="capitalize" x-text="permissionText"></p>
            </div>
            
            <div x-show="permission === 'default'">
                <button @click="requestPermission()" class="px-4 py-2 bg-green-600 text-white rounded-lg">
                    Cho phép nhận thông báo
                </button>
            </div>

            <div x-show="permission === 'granted'">
                <button @click="sendTestNotification()" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                    Gửi thông báo thử
                </button>
                <p class="text-sm text-gray-500 mt-2">Bạn sẽ nhận được một thông báo mẫu trong vài giây.</p>
            </div>

             <div x-show="permission === 'denied'">
                <p class="text-sm text-gray-500">Bạn đã chặn thông báo. Vui lòng vào phần cài đặt của trình duyệt để cho phép lại.</p>
            </div>
        </div>
    </div>
</div>
