// File: /teacher/assets/js/components/sessionDetails.js
// Component quản lý logic cho trang Chi tiết Ca học.

// Giả định các hàm API sẽ được định nghĩa trong services/api.js
// import { fetchSessionDetailsAPI } from '../services/api.js';

export default () => ({
    /**
     * Trạng thái (state) nội bộ, chuyên dụng cho trang này.
     */
    details: null,
    isLoading: true,
    error: null,
    activeSection: 'finalize', // Section mặc định được mở
    
    // State để quản lý việc lưu và tải file
    saveStatus: '', // 'saving', 'saved', 'error'
    isUploadingFile: false,

    isSavingContent: false,

    // State cho chức năng điểm danh & feedback
    students: [],
    feedbackOptions: [],
    isFeedbackPopupOpen: false,
    selectedStudentForFeedback: null,

    isSubmittingFeedback: false,
    feedback: {
        studentsPresent: 0,
        ratingHygiene: 0,
        ratingFacilities: 0,
        ratingOperations: 0,
        ratingAdmin: 0,
        ratingAssistant: 0,
    },

    /**
     * Hàm khởi tạo, được AlpineJS tự động gọi.
     * Tải dữ liệu chi tiết cho ca học đang được chọn.
     */
    async init() {
        const sessionId = this.$store.app.routing.selectedId;
        if (sessionId) {
            await this.fetchSessionDetails(sessionId);
        } else {
            this.error = "Không có ID ca học nào được chọn.";
            this.isLoading = false;
        }
    },

    /**
     * Lấy dữ liệu chi tiết của ca học từ API.
     * @param {number} sessionId 
     */
    async fetchSessionDetails(sessionId) {
        this.isLoading = true;
        this.error = null;
        try {
            // Trong tương lai, bạn sẽ gọi hàm từ api.js ở đây
            // const sessionData = await fetchSessionDetailsAPI(sessionId);
            const response = await fetch(`/ajax.php?action=teacherapp_get_session_details&session_id=${sessionId}`);
            
            if (!response.ok) {
                throw new Error(`Lỗi mạng: ${response.statusText}`);
            }
            // const sessionData = await response.json();
            const responseData = await response.json();
            const sessionData = await responseData.data;

            if (sessionData && !sessionData.error) {
                this.details = sessionData.details;
                this.students = Object.values(sessionData.students || {});
                this.feedbackOptions = sessionData.feedback_options;
                console.log("Dữ liệu ca học:", this.details);
                // console.log("Dữ liệu ca học:", this.students);
                // console.log("Dữ liệu ca học:", this.feedbackOptions);
                if (sessionData.details.feedback) {
                    this.feedback.id = sessionData.details.feedback.id;
                    this.feedback.studentsPresent = sessionData.details.feedback.students_present_actual;
                    this.feedback.ratingHygiene = sessionData.details.feedback.rating_hygiene;
                    this.feedback.ratingFacilities = sessionData.details.feedback.rating_facilities;
                    this.feedback.ratingOperations = sessionData.details.feedback.rating_operations;
                    this.feedback.ratingAdmin = sessionData.details.feedback.rating_admin;
                    this.feedback.ratingAssistant = sessionData.details.feedback.rating_assistant;
                    // ... gán tương tự cho các rating khác
                }

            } else {
                throw new Error(sessionData.error || 'Không tìm thấy thông tin ca học.');
            }
            // console.log("Dữ liệu ca học:", this.details);
            console.log("Dữ liệu ca học:", this.feedback);
            // console.log("Dữ liệu ca học:", this.feedbackOptions);
        } catch (err) {
            this.error = err.message || "Không thể tải dữ liệu chi tiết ca học.";
            console.error(err);
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Tự động lưu nội dung buổi học khi người dùng click ra ngoài.
     */
    async saveSessionContent(sessionId) {
        const session = this.details;
        if (!session) return;

        this.saveStatus = 'saving'; // Hiển thị "Đang lưu..."
        try {
            const formData = new FormData();
            formData.append('action', 'teacherapp_save_session_content');
            formData.append('session_id', session.session_id);
            formData.append('content', session.content || ''); // Gửi chuỗi rỗng nếu content là null
            formData.append('user_id', this.$store.user.user_id);

            const response = await fetch('/ajax.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Lỗi server khi lưu.');
            
            const result = await response.json();
            if (result.success) {
                this.saveStatus = 'saved'; // Hiển thị "Đã lưu"
            } else {
                throw new Error(result.message || 'Lưu thất bại.');
            }

        } catch (error) {
            this.saveStatus = 'error'; // Hiển thị "Lỗi"
            console.error("Lỗi khi lưu nội dung buổi học:", error);
        } finally {
            // Ẩn thông báo sau 2 giây
            setTimeout(() => { this.saveStatus = ''; }, 2000);
        }
    },

    /**
     * Tự động lưu nội dung buổi học khi người dùng click ra ngoài.
     */
    async saveSessionData(sessionId, fieldName, fieldValue) {
        const session = this.details;
        if (!session) return;

        this.saveStatus = 'saving'; // Hiển thị "Đang lưu..."
        try {
            const formData = new FormData();
            formData.append('action', 'teacherapp_update_session_field');
            formData.append('session_id', sessionId);
            formData.append('field_name', fieldName);
            formData.append('field_value', fieldValue);
            formData.append('user_id', this.$store.user.user_id);

            const response = await fetch('/ajax.php', {
                method: 'POST',
                body: formData
            }); 

            if (!response.ok) throw new Error('Lỗi server khi lưu.');
            
            const result = await response.json();
            if (result.success) {
                this.saveStatus = 'saved'; // Hiển thị "Đã lưu"
            } else {
                throw new Error(result.message || 'Lưu thất bại.');
            }

        } catch (error) {
            this.saveStatus = 'error'; // Hiển thị "Lỗi"
            console.error("Lỗi khi lưu nội dung buổi học:", error);
        } finally {
            // Ẩn thông báo sau 2 giây
            setTimeout(() => { this.saveStatus = ''; }, 2000);
        }
    },

    /**
     * Xử lý việc tải file lên S3.
     * @param {Event} event - Sự kiện change từ input file.
     * @param {string} type - Loại file ('documents', 'exercices', 'comments').
     */
    async uploadSessionFile(event, type) {
        const session = this.details;
        const file = event.target.files[0];
        if (!file || !session) return;

        this.isUploadingFile = true;
        try {
            const formData = new FormData();
            formData.append('action', 'teacherapp_upload_session_file');
            formData.append('session_id', session.session_id);
            formData.append('class_code', session.class_code);
            // Lấy ngày Y-m-d từ dayInfo.date (d/m/Y)
            // const dateParts = session.dayInfo.date.split('/');
            // const sessionDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
            formData.append('session_date', session.date);
            formData.append('type', type);
            formData.append('current_user_id', this.$store.user.user_id);
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
                    // cần trả về string và array: content_files  và content_files_array
                    this.details.content_files = result.files;
                    this.details.content_files_array = result.files_array;
                }
                if(type === 'exercices') {
                    // cần trả về string và array: content_files  và content_files_array
                    this.details.exercice_files = result.files;
                    this.details.exercice_files_array = result.files_array;
                }
                if(type === 'comments') {
                    // cần trả về string và array: content_files  và content_files_array
                    this.details.comment_files = result.files;
                    this.details.comment_files_array = result.files_array;
                }

                console.log(`Tải file [${type}] thành công:`, this.details);
                // Thêm else if cho 'exercices' và 'comments' sau này
            } else {
                throw new Error(result.message || 'Tải file thất bại.');
            }

        } catch (error) {
            console.error(`Lỗi khi tải file [${type}]:`, error);
            alert(`Lỗi: ${error.message}`);
        } finally {
            this.isUploadingFile = false;
            event.target.value = ''; // Reset input file
        }
    },


    /**
     * (MỚI) Xử lý việc xóa file trên S3.
     * @param {string} fileUrl - URL của file cần xóa.
     * @param {string} type - 'documents' hoặc 'exercices', tương ứng với tên cột CSDL.
     */
    async deleteSessionFile(fileUrl, type) {
        // Hiển thị hộp thoại xác nhận trước khi xóa
        if (!confirm(`Bạn có chắc chắn muốn xóa file này không?\nHành động này không thể hoàn tác.`)) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'teacherapp_delete_session_file');
            formData.append('session_id', this.details.session_id);
            formData.append('file_url', fileUrl);
            // Ánh xạ 'documents' -> 'content_files'
            // const fieldName = (type === 'documents') ? 'content_files' : 'exercice_files';
            formData.append('type', type);
            formData.append('current_user_id', this.$store.user.user_id);

            const response = await fetch('/ajax.php', { method: 'POST', body: formData });
            if (!response.ok) throw new Error('Lỗi server khi xóa file.');

            const result = await response.json();
            if (result.success) {
                // Cập nhật lại giao diện bằng cách lọc ra file đã bị xóa
                if (type === 'documents') {
                    this.details.content_files_array = this.details.content_files_array.filter(f => f.url !== fileUrl);
                } else if (type === 'exercices') {
                    this.details.exercice_files_array = this.details.exercice_files_array.filter(f => f.url !== fileUrl);
                } else if (type === 'comments') {
                    this.details.comment_files_array = this.details.comment_files_array.filter(f => f.url !== fileUrl);
                }

                console.log(`Xóa file [${type}] thành công:`, this.details);
            } else {
                throw new Error(result.message || 'Xóa file thất bại.');
            }
        } catch (error) {
            console.error(`Lỗi khi xóa file [${type}]:`, error);
            alert(`Lỗi: ${error.message}`);
        }
    },

    /**
     * Xử lý việc đóng/mở các section.
     * @param {string} sectionName 
     */
    toggleSection(sectionName) {
        this.activeSection = (this.activeSection === sectionName) ? null : sectionName;
    },


    // --- CÁC HÀM CHO ĐIỂM DANH & FEEDBACK ---
    cycleAttendance(studentToUpdate) {
        // Lớp bảo vệ: Dừng hàm nếu this.students chưa phải là một mảng
        if (!Array.isArray(this.students)) {
            console.warn("Dữ liệu học sinh chưa sẵn sàng để điểm danh.");
            return;
        }

        const statuses = ['holding', 'present', 'n_absence', 'absence'];
        const currentIndex = statuses.indexOf(studentToUpdate.attendance_status);
        const nextIndex = (currentIndex + 1) % statuses.length;
        const nextStatus = statuses[nextIndex];
        
        // student.attendance_status = statuses[nextIndex];

        // Tìm vị trí của học sinh trong mảng
        const studentIndex = this.students.findIndex(s => s.id === studentToUpdate.id);

        if (studentIndex > -1) {
            const updatedStudent = { ...this.students[studentIndex], attendance_status: nextStatus };
            this.students[studentIndex] = updatedStudent;
        }

        const formData = new FormData();
        formData.append('action', 'teacherapp_update_attendance');
        formData.append('session_id', this.details.session_id);
        formData.append('student_id', studentToUpdate.id);
        formData.append('status', nextStatus);
        formData.append('user_id', this.$store.user.user_id);
        fetch('/ajax.php', { method: 'POST', body: formData });
    },
    getAttendanceClass(status) {
        return {
            'holding': 'border-gray-300', 
            'present': 'border-green-500',
            'n_absence': 'border-yellow-500', 
            'absence': 'border-red-500'
        }[status] || 'border-gray-300';
    },
    getAttendanceChar(status) {
        return {
            'present': 'C', 
            'n_absence': 'P', 
            'absence': 'V'
        }[status] || '';
    },
    openFeedbackPopup(student) {
        if (!student.interactions) {
            student.interactions = [];
        }
        this.selectedStudentForFeedback = student;
        this.isFeedbackPopupOpen = true;
    },
    async addInteraction(feedbackOption) {
        const student = this.selectedStudentForFeedback;
        const formData = new FormData();
        formData.append('action', 'teacherapp_add_interaction');
        formData.append('session_id', this.details.session_id);
        formData.append('student_id', student.id);
        formData.append('tag', feedbackOption.tag);
        formData.append('teacher_id', this.$store.user.user_id);
        // formData.append('points_awarded', 0);
        const response = await fetch('/ajax.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            student.interactions.push(result.interaction);
            console.log('Selected Student: ', this.selectedStudentForFeedback);
        }
    },
    async deleteInteraction(interactionId, student) {
        student.interactions = student.interactions.filter(i => i.id !== interactionId);
        const formData = new FormData();
        formData.append('action', 'teacherapp_delete_interaction');
        formData.append('interaction_id', interactionId);
        formData.append('teacher_id', this.$store.user.user_id);
        fetch('/ajax.php', { method: 'POST', body: formData });
    },


    

    



    // --------------------------------------------
    // Các phương thức hỗ trợ cho việc gửi feedback
    // --------------------------------------------

    // Computed property để tự động đếm sĩ số có mặt
    get presentStudentCount() {
        if (!Array.isArray(this.students)) {
            return 0; // Trả về 0 nếu chưa phải là mảng
        }
        return this.students.filter(s => s.attendance_status === 'present').length;
    },

    // Gửi dữ liệu chốt ca và feedback lên server.
    async submitFinalizeFeedback() {
        this.isSubmittingFeedback = true;
        try {
            const formData = new FormData();
            formData.append('action', 'teacherapp_save_session_feedback');
            formData.append('session_id', this.details.session_id);
            formData.append('teacher_id', this.$store.app.teacher.id);
            formData.append('students_present', this.presentStudentCount);
            // Gửi tất cả các giá trị rating
            Object.keys(this.feedback).forEach(key => {
                formData.append(key.replace('rating', 'rating_').toLowerCase(), this.feedback[key]);
            });
            
            const response = await fetch('/ajax.php', { method: 'POST', body: formData });
            if (!response.ok) throw new Error("Lỗi server");
            
            const result = await response.json();
            if (!result.success) throw new Error("Lưu feedback thất bại.");
            
            alert('Chốt ca và gửi feedback thành công!');

        } catch (err) {
            alert(`Lỗi: ${err.message}`);
        } finally {
            this.isSubmittingFeedback = false;
        }
    },    

});
