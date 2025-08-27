// File: /teacher/assets/js/components/notificationSettings.js
import notificationManager from '../services/notificationManager.js';

export default () => ({
    isSupported: notificationManager.isSupported,
    permission: notificationManager.permission,

    get permissionText() {
        switch(this.permission) {
            case 'granted': return 'Đã cho phép';
            case 'denied': return 'Đã chặn';
            default: return 'Chưa cấp quyền';
        }
    },

    async requestPermission() {
        this.permission = await notificationManager.requestPermission();
    },

    sendTestNotification() {
        notificationManager.sendTestNotification();
    }
});
