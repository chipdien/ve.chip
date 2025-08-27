// File: /teacher/assets/js/services/notificationManager.js

// IMPORTANT: Replace this with your own VAPID public key
const VAPID_PUBLIC_KEY = 'BKeb5Y8vm0Acek86jYxqYvymFzoAEWPArjTxCr9sf1QdxO2JKMyBUyHEXteN-xE63lEFCVgD1xos-L9RjocXaZY'; 

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

export default {
    isSupported: ('serviceWorker' in navigator && 'PushManager' in window),
    permission: Notification.permission,

    /**
     * Khởi tạo Service Worker và kiểm tra quyền.
     */
    async init() {
        if (!this.isSupported) {
            console.warn('Push notifications are not supported in this browser.');
            return;
        }
        await navigator.serviceWorker.register('/sw.js');
        this.permission = Notification.permission;
    },

    /**
     * Yêu cầu người dùng cấp quyền nhận thông báo.
     */
    async requestPermission() {
        if (!this.isSupported) return;
        this.permission = await Notification.requestPermission();
        if (this.permission === 'granted') {
            await this.subscribeUser();
        }
        return this.permission;
    },

    /**
     * Đăng ký người dùng để nhận thông báo đẩy.
     */
    async subscribeUser() {
        if (!this.isSupported || this.permission !== 'granted') return;

        const swRegistration = await navigator.serviceWorker.ready;
        try {
            const subscription = await swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
            });
            
            // Gửi thông tin subscription lên server để lưu lại
            await this.saveSubscription(subscription);
            console.log('User is subscribed.');

        } catch (err) {
            console.error('Failed to subscribe the user: ', err);
        }
    },

    /**
     * Gửi thông tin subscription lên server.
     */
    async saveSubscription(subscription) {
        const formData = new FormData();
        formData.append('action', 'teacherapp_save_subscription');
        formData.append('subscription', JSON.stringify(subscription));

        await fetch('/ajax.php', { method: 'POST', body: formData });
    },

    /**
     * Gửi yêu cầu test thông báo.
     */
    async sendTestNotification() {
        const formData = new FormData();
        formData.append('action', 'teacherapp_send_test_notification');
        await fetch('/ajax.php', { method: 'POST', body: formData });
    }
}
