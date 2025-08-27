// stores/appStore.js
export default {
    // Dữ liệu chính, ít thay đổi
    user_id: null,
    username: null,
    nickname: null,
    email: null,
    phone: null,
    opt_school_year: null,
    status: null,
    roles: null,


    /**
     * Dữ liệu có thể được tải động và lưu trữ (cache) để tái sử dụng.
     * Các component sẽ kiểm tra dữ liệu ở đây trước khi quyết định gọi API.
     */
    data: {

    },
    


    /**
     * Hàm khởi tạo, nhận dữ liệu ban đầu từ PHP.
     */
    init() {
        const initialData = JSON.parse(document.getElementById('initial-user-store').textContent || '{}');
        if (!initialData) return;

        this.user_id = initialData.id || null;
        this.username = initialData.username || null;
        this.nickname = initialData.nickname || null;
        this.email = initialData.email || null;
        this.phone = initialData.phone || null;
        this.opt_school_year = initialData.opt_school_year || null;
        this.status = initialData.status || null;
        this.roles = initialData.roles || null;

       
    },
}