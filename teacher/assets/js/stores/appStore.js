// stores/appStore.js
export default {
    // Dữ liệu chính, ít thay đổi
    teacher: {},
    classes: [],
    favoriteFunctions: [],

    /**
     * Dữ liệu có thể được tải động và lưu trữ (cache) để tái sử dụng.
     * Các component sẽ kiểm tra dữ liệu ở đây trước khi quyết định gọi API.
     */
    data: {
        todaySchedule: {
            items: [],
            lastFetched: null // Sẽ lưu trữ thời gian (timestamp) khi dữ liệu được lấy
        },
        attentionClasses: {
            items: [],
            lastFetched: null
        },
        weeklySchedule: {
            items: [],
            lastFetched: null
        },
    },
    
    // Trạng thái điều hướng
    routing: {
        page: 'dashboard',
        selectedId: null,
        subPage: null,
        previousPage: null,
        previousId: null,
        previousSubPage: null,
    },

    // Trạng thái giao diện người dùng
    ui: {
        isInitialLoading: true,
        profileMenuOpen: false,
    },

    /**
     * Hàm khởi tạo, nhận dữ liệu ban đầu từ PHP.
     * @param {object} initialData 
     */
    setInitialData(initialData) {
        if (!initialData) return;

        this.teacher = initialData.teacher || {};
        this.classes = Object.values(initialData.classes || {});
        this.favoriteFunctions = Object.values(initialData.favoriteFunctions || {});
        
        // Lưu dữ liệu ban đầu vào cache và ghi lại thời gian
        this.data.todaySchedule.items = Object.values(initialData.todaySchedule || {});
        this.data.todaySchedule.lastFetched = Date.now();

        this.data.attentionClasses.items = Object.values(initialData.attentionClasses || {});
        this.data.attentionClasses.lastFetched = Date.now();
    },

    /**
     * Cập nhật trạng thái điều hướng.
     * @param {string} page 
     * @param {string|null} id 
     * @param {string|null} subPage 
     */
    setRouting(page, id, subPage) {
        // Lưu lại trang trước đó để dùng cho nút "quay lại"
        console.log('setRouting: ', page, id, subPage);
        if (this.routing.page !== page) {
            this.routing.previousPage = this.routing.page;
        }
        if (this.routing.selectedId !== id) {
            this.routing.previousId = this.routing.selectedId;
        }
        if (this.routing.subPage !== subPage) {
            this.routing.previousSubPage = this.routing.subPage;
        }
        this.routing.page = page;
        this.routing.selectedId = id ? parseInt(id) : null;
        this.routing.subPage = subPage || null;
    }
}