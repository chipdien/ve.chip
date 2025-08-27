// File: /teacher/assets/js/components/classDetails.js
export default () => ({
    details: null,
    isLoading: true,
    error: null,
    activeTab: 'students', // Tab mặc định
    validTabs: ['students', 'sessions', 'documents'],

    async init() {

        const subPageFromURL = this.$store.app.routing.subPage;
        if (subPageFromURL && this.validTabs.includes(subPageFromURL)) {
            this.activeTab = subPageFromURL;
        }

        const classId = this.$store.app.routing.selectedId;
        if (classId) {
            await this.fetchClassDetails(classId);
        } else {
            this.error = "Không có ID lớp học nào được chọn.";
            this.isLoading = false;
        }
        // console.log(this.details);
    },

    async fetchClassDetails(classId) {
        this.isLoading = true;
        this.error = null;
        try {
            const response = await fetch(`/ajax.php?action=teacherapp_get_class_details&class_id=${classId}&teacher_id=${this.$store.app.teacher.id}`);
            if (!response.ok) throw new Error(`Lỗi mạng: ${response.statusText}`);
            const responseData = await response.json();
            if (responseData && !responseData.error) {
                this.details = responseData.data;
            } else {
                throw new Error(responseData.error || 'Không tìm thấy thông tin lớp học.');
            }
        } catch (err) {
            this.error = err.message;
        } finally {
            this.isLoading = false;
        }
    }

});
