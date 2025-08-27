// File: /teacher/assets/js/components/sessionList.js
// Component quản lý logic cho trang Danh sách Ca học.

// Giả định các hàm API sẽ được định nghĩa trong services/api.js
// import { fetchSessionsAPI } from '../services/api.js';

export default () => ({
    /**
     * Trạng thái (state) nội bộ, chuyên dụng cho trang này.
     */
    sessions: {
        activeTab: 'past',
        past: {
            items: [],
            page: 1,
            hasMore: true,
            isLoading: false,
            filterClassId: null
        },
        upcoming: {
            items: [],
            page: 1,
            hasMore: true,
            isLoading: false,
            filterClassId: null
        }
    },

    /**
     * Hàm khởi tạo, được AlpineJS tự động gọi khi người dùng truy cập trang.
     */
    init() {
        // Tải dữ liệu cho cả hai tab nếu chúng chưa có dữ liệu
        if (this.sessions.past.items.length === 0) {
            this.fetchSessions('past');
        }
        if (this.sessions.upcoming.items.length === 0) {
            this.fetchSessions('upcoming');
        }
    },

    /**
     * Lấy dữ liệu ca học cho một tab cụ thể từ API.
     * @param {string} tabName - 'past' hoặc 'upcoming'.
     */
    async fetchSessions(tabName) {
        const tabState = this.sessions[tabName];
        // Ngăn việc gọi API nếu đang tải hoặc đã hết dữ liệu
        if (!tabState.hasMore || tabState.isLoading) return;

        tabState.isLoading = true;
        try {
            const teacherId = this.$store.app.teacher.id;
            const classFilter = tabState.filterClassId ? `&class_id=${tabState.filterClassId}` : '';
            const url = `/ajax.php?action=teacherapp_get_sessions&teacher_id=${teacherId}&type=${tabName}&page=${tabState.page}${classFilter}`;
            
            const response = await fetch(url);
            if (!response.ok) throw new Error("Lỗi mạng khi tải danh sách ca học.");
            
            const responseData = await response.json();
            const result = await responseData.data;

            const newItems = (result && result.items) ? result.items : result;
            const hasMore = (result && result.hasMore !== undefined) ? result.hasMore : (newItems.length > 0);

            if (!Array.isArray(newItems)) {
                throw new TypeError("Dữ liệu trả về từ API không phải là một mảng.");
            }

            // Nối dữ liệu mới vào danh sách hiện tại
            tabState.items.push(...newItems);
            tabState.hasMore = hasMore;
            tabState.page++;

        } catch (error) {
            console.error(`Lỗi khi tải ca học [${tabName}]:`, error);
            tabState.hasMore = false; // Dừng việc tải thêm nếu có lỗi
        } finally {
            tabState.isLoading = false;
        }
    },

    /**
     * Xử lý khi người dùng thay đổi bộ lọc lớp học.
     */
    applyClassFilter() {
        const activeTab = this.sessions.activeTab;
        const tabState = this.sessions[activeTab];
        
        // Reset lại trạng thái của tab hiện tại để bắt đầu lại từ đầu
        tabState.items = [];
        tabState.page = 1;
        tabState.hasMore = true;
        
        // Tải lại dữ liệu với bộ lọc mới
        this.fetchSessions(activeTab);
    },

    /**
     * Xử lý khi người dùng chuyển tab.
     * @param {string} tabName - 'past' hoặc 'upcoming'.
     */
    changeSessionTab(tabName) {
        this.sessions.activeTab = tabName;
        // Chỉ tải dữ liệu cho tab mới nếu nó chưa từng được tải
        if (this.sessions[tabName].items.length === 0) {
            this.fetchSessions(tabName);
        }
    }
});
