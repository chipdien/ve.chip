// File: /teacher/assets/js/app.js

// Sửa lỗi: Sử dụng phương pháp 'alpine:init' để đăng ký component một cách an toàn.
// Điều này đảm bảo AlpineJS đã sẵn sàng trước khi component của chúng ta được định nghĩa.
document.addEventListener('alpine:init', () => {
    // Dùng Alpine.data() để đăng ký component tên là 'app'.
    Alpine.data('app', (initialData) => ({
        
        // --- Dữ liệu được truyền từ PHP ---
        teacher: initialData.teacher || {},
        todaySchedule: initialData.todaySchedule || [],
        favoriteFunctions: initialData.favoriteFunctions || [],
        attentionClasses: initialData.attentionClasses || [],
        classes: initialData.classes || [],
        weeklySchedule: initialData.weeklySchedule || [],
        
        // --- Trạng thái Ứng dụng ---
        isLoading: true,
        profileMenuOpen: false,
        page: 'dashboard', // Trang mặc định
        expandedDate: null,
        selectedId: null,
        selectedSessionId: null,  
        selectedSessionDetails: null,  

        // --- Khởi tạo ---
        // Hàm init() sẽ tự động được AlpineJS gọi khi component được gắn vào trang.
        init() {

            if (this.weeklySchedule && typeof this.weeklySchedule === 'object' && !Array.isArray(this.weeklySchedule)) {
                this.weeklySchedule = Object.values(this.weeklySchedule);
            }

            // Lắng nghe sự kiện hash thay đổi (nút back/forward của trình duyệt)
            window.addEventListener('hashchange', () => this.route());
            // Xử lý route ban đầu khi tải trang
            this.route(); 
            
            // Tìm ngày hôm nay trong lịch và tự động mở rộng nó
            if (Array.isArray(this.weeklySchedule)) {
                const today = this.weeklySchedule.find(day => day.isToday);
                if (today) {
                    this.expandedDate = today.full_date;
                }
            } else {
                console.error("Dữ liệu weeklySchedule không phải là một mảng:", this.weeklySchedule);
            }

            setTimeout(() => {
                this.isLoading = false;
            }, 300); 
        },

        // --- Logic Điều hướng ---
        route() {
            // Lấy hash từ URL, ví dụ: #classes
            const hash = window.location.hash.substring(1);
            const [page, id] = hash.split('/'); // Tách chuỗi, ví dụ: "session/301"
            
            // Các trang hợp lệ
            const validPages = ['dashboard', 'classes', 'weekly_schedule', 'session'];

            if (validPages.includes(page)) {
                this.page = page;
                this.selectedId = id ? parseInt(id) : null;
                
                // Nếu đang ở trang ca học, tìm thông tin chi tiết
                if (this.page === 'session' && this.selectedId) {
                    this.selectedSessionId = this.selectedId;
                    this.findSessionDetails();
                }
            } else {
                // Nếu hash không hợp lệ hoặc rỗng, mặc định về dashboard
                this.page = 'dashboard';
                this.selectedId = null;
            }
        },

        navigate(newPage, inputId = null) {
            let newHash = newPage;
            if (inputId) {
                newHash += `/${inputId}`;
            }
            window.location.hash = newHash;
        },

        // --- (MỚI) Hàm tìm thông tin chi tiết ca học ---
        findSessionDetails() {
            if (!this.selectedSessionId) {
                this.selectedSessionDetails = null;
                return;
            }
            // Tìm trong toàn bộ lịch dạy để lấy ra ca học có ID tương ứng
            for (const day of this.weeklySchedule) {
                const foundSession = day.sessions.find(s => s.session_id === this.selectedSessionId);
                if (foundSession) {
                    this.selectedSessionDetails = foundSession;
                    // Gắn thêm thông tin về ngày vào để hiển thị
                    this.selectedSessionDetails.dayInfo = { day: day.day, date: day.date };
                    return;
                }
            }
            // Nếu không tìm thấy, gán là null
            this.selectedSessionDetails = null;
            console.warn(`Không tìm thấy ca học với ID ${this.selectedSessionId}`);
        },


        // --- Logic cho trang Lịch dạy ---
        toggleDay(date) {
            // Nếu ngày đang click đã được mở, thì đóng nó lại.
            // Ngược lại, mở ngày đang click.
            this.expandedDate = this.expandedDate === date ? null : date;
        },

        // --- Function hỗ trợ ---
        formatTimeRange(from, to) {
            const date1 = new Date(from);
            const date2 = new Date(to);
            if (isNaN(date1.getTime()) || isNaN(date2.getTime())) {
                console.error("Invalid date format");
                return '';
            }
            const time1 = String(date1.getHours()).padStart(2, '0') + ':' + String(date1.getMinutes()).padStart(2, '0');
            const time2 = String(date2.getHours()).padStart(2, '0') + ':' + String(date2.getMinutes()).padStart(2, '0');
            return `${time1} - ${time2}`;
        }
    }));
});
