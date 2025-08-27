// File: /teacher/assets/js/components/dashboard.js
// Component quản lý logic và dữ liệu cho trang Dashboard.

// Giả định rằng các hàm này sẽ được tạo trong file services/api.js sau này
// import { fetchTodayScheduleAPI, fetchAttentionClassesAPI } from '../services/api.js';

export default () => ({
    /**
     * Trạng thái (state) nội bộ của component Dashboard.
     */
    todaySchedule: [],
    attentionClasses: [],
    favoriteFunctions: [], // Dữ liệu này thường ít thay đổi, có thể lấy từ store
    isLoading: true,
    error: null,

    /**
     * Hàm khởi tạo, được AlpineJS tự động gọi.
     * Nhiệm vụ chính là tải dữ liệu cần thiết cho trang Dashboard.
     */
    async init() {
        this.isLoading = true;
        this.error = null;
        
        // Lấy dữ liệu tĩnh từ store
        this.favoriteFunctions = this.$store.app.data.favoriteFunctions;

        try {
            // Sử dụng Promise.all để tải dữ liệu đồng thời, tăng hiệu suất
            const [scheduleData, attentionData] = await Promise.all([
                this.fetchTodaySchedule(this.$store.app.teacher.id),
                this.fetchAttentionClasses(this.$store.app.teacher.id)
            ]);

            this.todaySchedule = scheduleData;
            this.attentionClasses = attentionData;

        } catch (err) {
            this.error = 'Không thể tải dữ liệu cho Dashboard. Vui lòng thử lại.';
            console.error(err);
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Hàm giả lập việc gọi API để lấy lịch dạy hôm nay.
     * Trong tương lai, bạn sẽ thay thế nó bằng hàm thật trong api.js.
     * @param {number} teacherId 
     */
    async fetchTodaySchedule(teacherId) {
        // Đây là nơi bạn sẽ gọi fetchTodayScheduleAPI(teacherId)
        // Tạm thời, chúng ta lấy dữ liệu đã được tải sẵn trong store để demo
        return this.$store.app.data.todaySchedule;
    },

    /**
     * Hàm giả lập việc gọi API để lấy các lớp cần chú ý.
     * @param {number} teacherId 
     */
    async fetchAttentionClasses(teacherId) {
        // Đây là nơi bạn sẽ gọi fetchAttentionClassesAPI(teacherId)
        return this.$store.app.data.attentionClasses;
    }


});
