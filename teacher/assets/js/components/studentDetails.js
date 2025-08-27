// File: /teacher/assets/js/components/studentDetails.js
export default () => ({
    details: null,
    isLoading: true,
    error: null,
    isEditing: false,
    saveStatus: '',
    isUploadingAvatar: false,

    async init() {
        const studentId = this.$store.app.routing.selectedId;
        if (studentId) {
            await this.fetchStudentDetails(studentId);
        } else {
            this.error = "Không có ID học sinh nào được chọn.";
            this.isLoading = false;
        }
    },

    async fetchStudentDetails(studentId) {
        this.isLoading = true;
        this.error = null;
        try {
            const response = await fetch(`/ajax.php?action=teacherapp_get_student_details&student_id=${studentId}`);
            if (!response.ok) throw new Error("Lỗi mạng");
            const responseData = await response.json();
            if (responseData && !responseData.error) {
                this.details = responseData.data;
            } else {
                throw new Error(responseData.error || "Không tìm thấy thông tin học sinh.");
            }
        } catch (err) {
            this.error = err.message;
        } finally {
            this.isLoading = false;
        }
    },

    async saveDetails() {
        this.saveStatus = 'saving';
        try {
            const formData = new FormData();
            formData.append('action', 'teacherapp_update_student_details');
            formData.append('student_id', this.details.id);
            formData.append('dob', this.details.dob || '');
            formData.append('school', this.details.school || '');
            formData.append('aspiration', this.details.aspiration || '');
            formData.append('note', this.details.note || '');
            
            const response = await fetch('/ajax.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                this.saveStatus = 'saved';
                this.isEditing = false; // Tắt chế độ chỉnh sửa sau khi lưu thành công
            } else {
                throw new Error("Lưu thông tin thất bại.");
            }
        } catch (err) {
            this.saveStatus = 'error';
            alert(err.message);
        } finally {
            setTimeout(() => { this.saveStatus = ''; }, 2000);
        }
    },

    triggerAvatarUpload() {
        this.$refs.avatarInput.click();
    },

    async handleAvatarUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        this.isUploadingAvatar = true;
        try {
            const formData = new FormData();
            formData.append('action', 'teacherapp_upload_avatar');
            formData.append('student_id', this.details.id);
            formData.append('updated_by_id', this.$store.user.user_id);
            formData.append('avatar', file);

            const response = await fetch('/ajax.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Lỗi server khi tải file.');
            
            const result = await response.json();
            if (result.success) {
                this.details.avatar = `${result.avatarUrl}?t=${new Date().getTime()}`;
            } else {
                throw new Error(result.message || 'Lỗi khi tải file.');
            }


        } catch (error) {
            console.error(`Lỗi khi tải file avatar:`, error);
            alert(`Lỗi: ${error.message}`);
        } finally {
            this.isUploadingAvatar = false;
            event.target.value = ''; // Reset input file
        }
    }
});
