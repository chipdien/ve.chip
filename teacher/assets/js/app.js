document.addEventListener('alpine:init', () => {
    // Đăng ký thành phần appState. Giờ đây nó là một hàm nhận VAPID key làm tham số.
    Alpine.data('appState', () => ({
        // State cho Sidebar
        sidebarOpen: false,

        // State cho Pop-up Thông báo
        showPrompt: false,
        VAPID_PUBLIC_KEY: window.VAPID_PUBLIC_KEY || '',

        // Hàm init() sẽ được Alpine.js tự động gọi khi thành phần được khởi tạo.
        init() {
            // Chờ 3 giây sau khi trang tải xong để không làm phiền người dùng ngay lập tức
            setTimeout(() => {
                this.checkPermission();
            }, 3000);
        },

        checkPermission() {
            const lastReminded = localStorage.getItem('notification_reminded_at');
            if (lastReminded && (Date.now() - lastReminded < 7 * 24 * 60 * 60 * 1000)) {
                return; // Nếu đã hỏi gần đây, không hiển thị lại
            }

            if ('Notification' in window && 'serviceWorker' in navigator && 'PushManager' in window) {
                // Trạng thái 'default' nghĩa là chưa được hỏi
                if (Notification.permission === 'default') {
                    this.showPrompt = true;
                }
            }
        },

        remindLater() {
            this.showPrompt = false;
            // Ghi nhớ lựa chọn trong 7 ngày
            localStorage.setItem('notification_reminded_at', Date.now());
        },

        async requestPermission() {
            this.showPrompt = false;
            try {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    console.log('Đã được cấp quyền gửi thông báo.');
                    await this.subscribeUser();
                } else {
                    console.log('Người dùng đã từ chối cấp quyền.');
                    // Ghi nhớ lựa chọn để không hỏi lại
                    localStorage.setItem('notification_reminded_at', Date.now());
                }
            } catch (error) {
                console.error('Lỗi khi yêu cầu quyền:', error);
            }
        },

        async subscribeUser() {
            try {
                const swReg = await navigator.serviceWorker.ready;
                const subscription = await swReg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(this.VAPID_PUBLIC_KEY)
                });
                
                console.log('Đã đăng ký nhận thông báo:', subscription);
                // Gửi thông tin subscription về server
                await fetch(`${window.APP_BASE_URL}/ajax.php`, {
                    method: 'POST',
                    body: JSON.stringify({ action: 'save_push_subscription', subscription: subscription }),
                    headers: { 'Content-Type': 'application/json' }
                });
            } catch (error) {
                console.error('Lỗi khi đăng ký nhận thông báo:', error);
            }
        },

        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    }));
});

// Đăng ký Service Worker (đoạn code này giữ nguyên bên ngoài)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(registration => {
            console.log('ServiceWorker đã được đăng ký với scope:', registration.scope);
        }).catch(error => {
            console.log('Đăng ký ServiceWorker thất bại:', error);
        });
    });
}
