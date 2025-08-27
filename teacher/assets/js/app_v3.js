// File: /teacher/assets/js/app.js
// Phiên bản tái cấu trúc theo quy chuẩn dự án

import appStore from './stores/appStore.js';

document.addEventListener('alpine:init', () => {
    // Khởi tạo dữ liệu hệ thống
    Alpine.store('app', appStore);

    Alpine.data('app', (initialTeacherData) => ({
        

        
        // ====================================================================
        // == BƯỚC 3: TÁI CẤU TRÚC TRẠNG THÁI (STATE) =========================
        // ====================================================================

        /**
         * Quản lý trạng thái điều hướng của ứng dụng.
         */
        routing: {
            page: 'dashboard',
            selectedId: null,
            subPage: null,
            previousPage: null, // Lưu trang trước đó cho nút "quay lại"
        },

        /**
         * Quản lý toàn bộ dữ liệu động của ứng dụng.
         */
        data: {
            teacher: initialTeacherData || {},
            favoriteFunctions: Object.values(initialData.favoriteFunctions || {}),
            todaySchedule: Object.values(initialData.todaySchedule || {}),
            classes: Object.values(initialData.activeClasses || {}),
            attentionClasses: Object.values(initialData.attentionClasses || {}),
            // Dữ liệu chi tiết cho từng tài nguyên, sẽ được tải "lười" (lazy-loaded)
            selectedClass: {
                details: null,
                isLoading: false,
                error: null
            },
            selectedSession: {
                details: null,
                isLoading: false,
                error: null
            },
            // Dữ liệu danh sách
            weeklySchedule: [], // Sẽ được tải bởi hàm fetchWeeklySchedule
            
        },

        /**
         * Quản lý trạng thái giao diện người dùng (UI).
         */
        ui: {
            isInitialLoading: true, // Trạng thái tải ban đầu của toàn bộ ứng dụng
            profileMenuOpen: false,
            expandedDate: null, // Ngày đang được mở rộng trong lịch
            isScheduleLoading: false,
            isSessionLoading: false,
            activeSessionSection: 'finalize',
            // Page Session_Details
            isSavingContent: false,
            isUploadingFile: false,
            saveStatus: '' // 'saving', 'saved', 'error'
        },

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

        // ====================================================================
        // == KHỞI TẠO ỨNG DỤNG ==============================================
        // ====================================================================

        async init() {
            // Tải dữ liệu cần thiết ban đầu
            this.loadInitialData();

            // Thiết lập listener cho việc điều hướng bằng nút back/forward của trình duyệt
            window.addEventListener('hashchange', () => this.route());
            
            // Xử lý route ban đầu khi tải trang
            this.route(); 

            console.log(JSON.parse(JSON.stringify(this.data)));
            // console.log(JSON.parse(JSON.stringify(initialData)));
        },

        /**
         * Tải các dữ liệu cần thiết cho toàn bộ ứng dụng khi khởi động.
         * Trong tương lai, hàm này sẽ gọi đến api/init.php.
         */
        async loadInitialData() {
            // Hiện tại, chúng ta vẫn dùng dữ liệu được truyền từ PHP.
            // Sau này, bạn có thể thay thế bằng một lệnh fetch() ở đây.
            // Ví dụ: const response = await fetch('/api/init.php'); 
            //       const data = await response.json();
            //       this.data.weeklySchedule = data.weeklySchedule; ...
            
            // Tạm thời lấy dữ liệu từ initialData (đã bị xóa khỏi state chính)
            // const fullInitialData = JSON.parse(document.body.getAttribute('x-data').match(/\((.*)\)/)[1]);
            // this.data.weeklySchedule = Object.values(fullInitialData.weeklySchedule || {});
            this.data.classes = Object.values(initialData.classes || {});

            // Tự động mở rộng ngày hôm nay trong lịch
            // if (Array.isArray(this.data.weeklySchedule)) {
            //     const today = this.data.weeklySchedule.find(day => day.isToday);
            //     if (today) {
            //         this.ui.expandedDate = today.full_date;
            //     }
            // }
            
            // Hoàn tất tải ban đầu
            setTimeout(() => {
                this.ui.isInitialLoading = false;
            }, 300); 
        },

        // ====================================================================
        // == BƯỚC 1: HỆ THỐNG ROUTING ========================================
        // ====================================================================

        route() {
            const hash = window.location.hash.substring(1);
            const [page, id, subPage] = hash.split('/');

            const validPages = ['dashboard', 'classes', 'weekly_schedule', 'session', 'session_details', 'class_details'];
            
            this.routing.page = validPages.includes(page) ? page : 'dashboard';
            this.routing.selectedId = id ? parseInt(id) : null;
            this.routing.subPage = subPage || null;

            // Kích hoạt tải dữ liệu "lười" dựa trên route mới
            if (this.routing.page === 'session_details' && this.routing.selectedId) {
                this.fetchSessionDetails(this.routing.selectedId);
            }
            if (this.routing.page === 'class_details' && this.routing.selectedId) {
                this.fetchClassDetails(this.routing.selectedId);
            }
        },

        navigate(newPage, id = null, subPage = null) {
            console.log(`Navigate to ${newPage} with id ${id} and subPage ${subPage}`);
            if (this.routing.page !== newPage) {
                this.routing.previousPage = this.routing.page;
            }

            // if (newPage === 'weekly_schedule') {
            //     this.fetchWeeklySchedule(this.data.teacher.id);
            // }

            let newHash = newPage;
            if (id) newHash += `/${id}`;
            if (subPage) newHash += `/${subPage}`;
            
            window.location.hash = newHash;
        },

        // ====================================================================
        // == BƯỚC 2: LAZY LOADING & API CALLS ================================
        // ====================================================================

        // (MỚI) Hàm khởi tạo dữ liệu cho trang ca học
        initSessionList() {
            // Chỉ tải nếu danh sách rỗng (tránh tải lại khi chuyển tab)
            if (this.sessions.past.items.length === 0) {
                this.fetchSessions('past');
            }
            if (this.sessions.upcoming.items.length === 0) {
                this.fetchSessions('upcoming');
            }
        },

        initWeeklySchedule() {
            if (this.data.weeklySchedule.length === 0) {
                this.fetchWeeklySchedule(this.data.teacher.id);
            }
            if (Array.isArray(this.data.weeklySchedule)) {
                const today = this.data.weeklySchedule.find(day => day.isToday);
                if (today) {
                    this.ui.expandedDate = today.full_date;
                }
            }
        },

        /**
         * (MỚI) Lấy dữ liệu ca học cho một tab cụ thể
         * @param {string} tabName - 'past' hoặc 'upcoming'
         */
        async fetchSessions(tabName) {
            const tabState = this.sessions[tabName];
            if (!tabState.hasMore || tabState.isLoading) return;

            tabState.isLoading = true;
            try {
                const classFilter = tabState.filterClassId ? `&class_id=${tabState.filterClassId}` : '';
                const url = `/ajax.php?action=teacherapp_get_sessions&teacher_id=${this.data.teacher.id}&type=${tabName}&page=${tabState.page}${classFilter}`;
                
                const response = await fetch(url);
                if (!response.ok) throw new Error("Lỗi mạng");
                
                const responseData = await response.json();
                const result = await responseData.data;

                tabState.items.push(...result.items);
                tabState.hasMore = result.hasMore;
                tabState.page++;
                console.log(`Dữ liệu sau khi cập nhật cho tab [${tabName}]:`, JSON.parse(JSON.stringify(this.sessions[tabName].items)));

            } catch (error) {
                console.error(`Lỗi khi tải ca học [${tabName}]:`, error);
            } finally {
                tabState.isLoading = false;
            }
        },

        /**
         * (MỚI) Lấy dữ liệu lịch dạy trong tuần từ API.
         * @param {number} teacherId ID của giáo viên.
         */
        async fetchWeeklySchedule(teacherId) {
            if (!teacherId) {
                console.error("Không có teacherId để tải lịch dạy.");
                return;
            }
            this.ui.isScheduleLoading = true;
            try {
                const response = await fetch(`/ajax.php?action=teacherapp_get_schedule&teacher_id=${teacherId}`);
                
                if (!response.ok) {
                    throw new Error(`Lỗi mạng: ${response.statusText}`);
                }

                const scheduleData = await response.json();

                // Xử lý và gán dữ liệu
                this.data.weeklySchedule = Object.values(scheduleData.data || {});

                // Tự động mở rộng ngày hôm nay sau khi tải xong
                if (Array.isArray(this.data.weeklySchedule)) {
                    const today = this.data.weeklySchedule.find(day => day.isToday);
                    if (today) {
                        this.ui.expandedDate = today.full_date;
                    }
                }

            } catch (error) {
                console.error("Không thể tải lịch dạy trong tuần:", error);
                // Có thể gán một trạng thái lỗi ở đây để hiển thị trên UI
            } finally {
                this.ui.isScheduleLoading = false;
            }
        },

        async fetchSessionDetails(sessionId) {
            if (!sessionId) {
                this.data.selectedSession.error = 'Không có sessionId để tải chi tiết ca học.';
                console.error(this.data.selectedSession.error);
                return;
            }
            this.data.selectedSession = { details: null, isLoading: true, error: null };
            
            try {
                const response = await fetch(`/ajax.php?action=teacherapp_get_session_details&session_id=${sessionId}`);
                if (!response.ok) {
                    throw new Error(`Lỗi mạng: ${response.statusText}`);
                }
                
                const sessionDetails = await response.json();

                // Kiểm tra xem API có trả về lỗi hay không
                if (sessionDetails && !sessionDetails.error) {
                    this.data.selectedSession.details = sessionDetails.data;
                } else {
                    throw new Error(sessionDetails.error || 'Không tìm thấy thông tin ca học.');
                }

            } catch (error) {
                this.data.selectedSession.error = error.message || 'Không thể tải dữ liệu ca học.';
                console.error(error);
            } finally {
                this.data.selectedSession.isLoading = false;
            }
        },

        async fetchClassDetails(classId) {
            // Logic tương tự cho việc tải chi tiết lớp học
            console.log(`Đang gọi API: /api/class_details.php?id=${classId}`);
        },


        // ====================================================================
        // == (MỚI) CÁC HÀM XỬ LÝ CHO SESSION DETAILS ========================
        // ====================================================================

        /**
         * Tự động lưu nội dung buổi học khi người dùng click ra ngoài.
         */
        async saveSessionContent() {
            const session = this.data.selectedSession.details;
            if (!session) return;

            this.ui.saveStatus = 'saving'; // Hiển thị "Đang lưu..."
            try {
                const formData = new FormData();
                formData.append('action', 'teacherapp_save_session_content');
                formData.append('session_id', session.session_id);
                formData.append('content', session.content || ''); // Gửi chuỗi rỗng nếu content là null

                const response = await fetch('/ajax.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error('Lỗi server khi lưu.');
                
                const result = await response.json();
                if (result.success) {
                    this.ui.saveStatus = 'saved'; // Hiển thị "Đã lưu"
                } else {
                    throw new Error(result.message || 'Lưu thất bại.');
                }

            } catch (error) {
                this.ui.saveStatus = 'error'; // Hiển thị "Lỗi"
                console.error("Lỗi khi lưu nội dung buổi học:", error);
            } finally {
                // Ẩn thông báo sau 2 giây
                setTimeout(() => { this.ui.saveStatus = ''; }, 2000);
            }
        },

        /**
         * Xử lý việc tải file lên S3.
         * @param {Event} event - Sự kiện change từ input file.
         * @param {string} type - Loại file ('documents', 'exercices', 'comments').
         */
        async uploadSessionFile(event, type) {
            const session = this.data.selectedSession.details;
            const file = event.target.files[0];
            if (!file || !session) return;

            this.ui.isUploadingFile = true;
            try {
                const formData = new FormData();
                formData.append('action', 'teacherapp_upload_session_file');
                formData.append('session_id', session.session_id);
                formData.append('class_code', session.class_code);
                // Lấy ngày Y-m-d từ dayInfo.date (d/m/Y)
                const dateParts = session.dayInfo.date.split('/');
                const sessionDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
                formData.append('session_date', sessionDate);
                formData.append('type', type);
                formData.append('file', file);

                const response = await fetch('/ajax.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error('Lỗi server khi tải file.');
                
                const result = await response.json();
                if (result.success) {
                    // Cập nhật lại danh sách file trên giao diện
                    // Giả định API trả về toàn bộ danh sách file mới
                    if(type === 'documents') {
                        this.data.selectedSession.details.content_files = result.files;
                    }
                    // Thêm else if cho 'exercices' và 'comments' sau này
                } else {
                    throw new Error(result.message || 'Tải file thất bại.');
                }

            } catch (error) {
                console.error(`Lỗi khi tải file [${type}]:`, error);
                alert(`Lỗi: ${error.message}`);
            } finally {
                this.ui.isUploadingFile = false;
                event.target.value = ''; // Reset input file
            }
        },

        toggleSessionSection(sectionName) {
            this.ui.activeSessionSection = this.ui.activeSessionSection === sectionName ? null : sectionName;
        },
    




        // ====================================================================
        // == CÁC HÀM TIỆN ÍCH & COMPUTED PROPERTIES ==========================
        // ====================================================================

        applyClassFilter() {
            const activeTab = this.sessions.activeTab;
            const tabState = this.sessions[activeTab];
            tabState.items = [];
            tabState.page = 1;
            tabState.hasMore = true;
            this.fetchSessions(activeTab);
        },

        /**
         * Computed Property cho nút quay lại động.
         */
        get backButtonTarget() {
             const pageMap = {
                'dashboard': 'Dashboard',
                'classes': 'Lớp học',
                'weekly_schedule': 'Lịch dạy',
                'session': 'Ca học',
            };
            const targetPage = this.routing.previousPage || 'weekly_schedule';
            return {
                target: targetPage,
                label: `Quay lại ${pageMap[targetPage] || 'trang trước'}`
            };
        },

        /**
         * Mở/đóng một ngày trong lịch dạy.
         */
        toggleDay(date) {
            this.ui.expandedDate = this.ui.expandedDate === date ? null : date;
        },



    }));
});


function formatTimeRange(from, to) {
    const date1 = new Date(from);
    const date2 = new Date(to);
    if (isNaN(date1.getTime()) || isNaN(date2.getTime())) {
        console.error("Invalid date format");
        return '';
    }
    const time1 = String(date1.getHours()).padStart(2, '0') + ':' + String(date1.getMinutes()).padStart(2, '0');
    const time2 = String(date2.getHours()).padStart(2, '0') + ':' + String(date2.getMinutes()).padStart(2, '0');
    return `${time1} - ${time2}`;
};

function formatDate(date) {
    const dateObj = new Date(date);

    // Get day, month, and year from the Date object
    const day = String(dateObj.getDate()).padStart(2, '0');
    const month = String(dateObj.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const year = dateObj.getFullYear();

    // Combine the parts into the desired format
    return `${day}/${month}/${year}`;

}