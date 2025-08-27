// File: /teacher/assets/js/components/classList.js
// Component quản lý logic và dữ liệu cho trang "Lớp học của tôi".

export default () => ({
    /**
     * Trạng thái (state) nội bộ của component.
     */
    allClasses: [],
    isLoading: true,
    error: null,
    activeTab: '1', // Mặc định là tab 'Đang hoạt động'

    // Bảng màu cho các cơ sở
    centerColors: {
        'Default': 'bg-gray-100 text-gray-800',
        '17DQ': 'bg-blue-100 text-blue-800',
        'TDH': 'bg-blue-100 text-blue-800',
        '11DQ': 'bg-blue-100 text-blue-800',
        'PTT': 'bg-purple-100 text-purple-800',
        'HĐ': 'bg-pink-100 text-pink-800',
        'SMC': 'bg-teal-100 text-teal-800',
        'C5': 'bg-amber-100 text-amber-800',
        'C6': 'bg-cyan-100 text-cyan-800'
    },

    /**
     * Hàm khởi tạo, được AlpineJS tự động gọi.
     * Lấy danh sách lớp học từ store toàn cục.
     */
    init() {
        this.isLoading = true;
        this.error = null;
        try {
            this.allClasses = this.$store.app.classes;
            if (!Array.isArray(this.allClasses)) {
                this.allClasses = Object.values(this.allClasses || {});
            }
            console.log(this.allClasses);
        } catch (err) {
            this.error = 'Không thể tải danh sách lớp học.';
            console.error(err);
        } finally {
            setTimeout(() => { this.isLoading = false; }, 100);
        }
    },

    /**
     * Computed property để lọc danh sách lớp học dựa trên tab đang active.
     */
    get filteredClasses() {
        if (!this.allClasses) return [];
        return this.allClasses.filter(c => String(c.active) === this.activeTab);
    },

    /**
     * Hàm tiện ích để lấy màu cho từng cơ sở.
     * @param {string} centerCode - Mã của cơ sở.
     */
    getCenterColor(centerCode) {
        return this.centerColors[centerCode] || this.centerColors['Default'];
    }
});
