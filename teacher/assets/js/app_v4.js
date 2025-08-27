// File: /teacher/assets/js/app.js
// Vai trò: Nhạc trưởng - Khởi tạo và đăng ký các module của ứng dụng.

// Giai đoạn 1: Import Store và các Component
import appStore from './stores/appStore.js';
import userStore from './stores/userStore.js';
import dashboardComponent from './components/dashboard.js';
import classListComponent from './components/classList.js';
import classDetailsComponent from './components/classDetails.js';
import weeklyScheduleComponent from './components/weeklySchedule.js';
import sessionListComponent from './components/sessionList.js';
import sessionDetailsComponent from './components/sessionDetails.js';

import studentDetailsComponent from './components/studentDetails.js';



document.addEventListener('alpine:init', () => {

    console.log('Alpine initialized, registering components...');
    
    // Giai đoạn 1: Đăng ký Store để quản lý trạng thái toàn cục
    Alpine.store('app', appStore);
    Alpine.store('user', userStore);

    const initialData = JSON.parse(document.getElementById('initial-data-store').textContent || '{}');
    if (initialData.teacher) {
        Alpine.store('app').setInitialData(initialData);
        console.log('Initial data:', JSON.parse(JSON.stringify(Alpine.store('app'))));
    }

    // Giai đoạn 2: Đăng ký các component con
    Alpine.data('dashboard', dashboardComponent);
    Alpine.data('classList', classListComponent);
    Alpine.data('classDetails', classDetailsComponent);
    Alpine.data('weeklySchedule', weeklyScheduleComponent);
    Alpine.data('sessionList', sessionListComponent);
    Alpine.data('sessionDetails', sessionDetailsComponent);

    Alpine.data('studentDetails', studentDetailsComponent);

    /**
     * Component 'app' chính, gắn vào thẻ <body>.
     * Nhiệm vụ chính là xử lý routing dựa trên URL hash.
     * Nó đọc và thay đổi trạng thái trong store, các component con sẽ tự động phản ứng theo.
     */
    Alpine.data('app', () => ({
        init() {
            // Lắng nghe sự kiện back/forward của trình duyệt
            window.addEventListener('hashchange', () => this.route());
            // Xử lý route ban đầu khi tải trang
            this.route();
            console.log('Initial route:', window.location.hash);

            setTimeout(() => {
                this.$store.app.ui.isInitialLoading = false;
            }, 100);
        },

        route() {
            const hash = window.location.hash.substring(1);
            const [page, id, subPage] = hash.split('/');
            
            const validPages = ['dashboard', 'classes', 'class_details', 'weekly_schedule', 'session', 'session_details', 'student_details'];
            const currentPage = validPages.includes(page) ? page : 'dashboard';
            console.log('Routing to:', { currentPage, id, subPage });
            // Cập nhật trạng thái routing trong store
            this.$store.app.setRouting(currentPage, id, subPage);
            // console.log('Updated routing:', this.$store.app.routing);
        },

        /**
         * Hàm điều hướng toàn cục.
         * Bất kỳ component con nào cũng có thể gọi hàm này.
         */
        navigate(newPage, id = null, subPage = null) {
            let newHash = newPage;
            if (id) newHash += `/${id}`;
            if (subPage) newHash += `/${subPage}`;
            window.location.hash = newHash;
        },

        /**
         * (NÂNG CẤP) Computed property toàn cục để tạo nút "quay lại" động.
         * Bất kỳ component con nào cũng có thể truy cập thuộc tính này.
         */
        get backButtonTarget() {
            const pageMap = {
                'dashboard': 'Dashboard',
                'classes': 'Lớp học',
                'class_details': 'Chi tiết lớp học',
                'weekly_schedule': 'Lịch dạy',
                'session': 'Ca học'
            };
            const page = this.$store.app.routing.previousPage || 'dashboard';
            const id = this.$store.app.routing.previousId || null;
            const subPage = this.$store.app.routing.previousSubPage || null;
            const targetPage = page + (id ? `/${id}` : '') + (subPage ? `/${subPage}` : '');

            return {
                target: targetPage,
                label: `Quay lại ${pageMap[page] || 'trang trước'}`
            };
        },
    }));
});
