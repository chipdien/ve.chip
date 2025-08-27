// File: /teacher/assets/js/components/weeklySchedule.js
// Component quản lý logic và dữ liệu cho trang "Lịch dạy trong tuần".

export default () => ({
    /**
     * Trạng thái (state) nội bộ, chuyên dụng cho trang này.
     */
    schedule: [],
    isLoading: true,
    error: null,
    expandedDate: null,

    // Thời gian cache (tính bằng mili giây). Ví dụ: 30 phút.
    CACHE_DURATION: 30 * 60 * 1000, 

    /**
     * Hàm khởi tạo, được AlpineJS tự động gọi.
     * Kiểm tra cache trước khi quyết định gọi API.
     */
    async init() {
        if (this.isCacheValid()) {
            // Nếu cache hợp lệ, lấy dữ liệu từ store
            console.log("Sử dụng dữ liệu lịch dạy đã có (cached).");
            this.schedule = this.$store.app.data.weeklySchedule.items;
            this.findAndExpandToday();
            this.isLoading = false;
        } else {
            // Nếu cache không hợp lệ, gọi API để lấy dữ liệu mới
            console.log("Dữ liệu lịch dạy đã cũ hoặc không tồn tại, tiến hành fetch.");
            await this.fetchWeeklySchedule();
        }
    },

    /**
     * Kiểm tra xem dữ liệu trong store có còn hợp lệ hay không.
     * @returns {boolean}
     */
    isCacheValid() {
        const cache = this.$store.app.data.weeklySchedule;
        if (!cache || !cache.lastFetched || cache.items.length === 0) {
            return false; // Không có cache
        }
        // Kiểm tra xem cache có quá hạn không
        const isExpired = (Date.now() - cache.lastFetched) > this.CACHE_DURATION;
        return !isExpired;
    },

    /**
     * Lấy dữ liệu lịch dạy trong tuần từ API và cập nhật store.
     */
    async fetchWeeklySchedule() {
        this.isLoading = true;
        this.error = null;
        try {
            const teacherId = this.$store.app.teacher.id;
            if (!teacherId) throw new Error("Không tìm thấy ID của giáo viên.");

            const response = await fetch(`/ajax.php?action=teacherapp_get_schedule&teacher_id=${teacherId}`);
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            
            const scheduleData = await response.json();
            const scheduleArray = Object.values(scheduleData.data || {});

            // Cập nhật state của component
            this.schedule = scheduleArray;
            // Cập nhật store với dữ liệu mới và timestamp mới
            this.$store.app.data.weeklySchedule.items = scheduleArray;
            this.$store.app.data.weeklySchedule.lastFetched = Date.now();

            this.findAndExpandToday();

        } catch (err) {
            this.error = "Không thể tải dữ liệu lịch dạy.";
            console.error(err);
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Tìm và mở rộng ngày hôm nay trong lịch.
     */
    findAndExpandToday() {
        if (Array.isArray(this.schedule)) {
            const today = this.schedule.find(day => day.isToday);
            if (today) {
                this.expandedDate = today.full_date;
            }
        }
    },

    /**
     * Xử lý việc đóng/mở chi tiết của một ngày.
     * @param {string} date - Ngày cần đóng/mở (định dạng Y-m-d).
     */
    toggleDay(date) {
        this.expandedDate = (this.expandedDate === date) ? null : date;
    }
});
