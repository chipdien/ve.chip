<?php
require_once __DIR__ . '/../init.php';

use App\Services\TestResultService;

$test_id = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 0;
$current_user_id = $_SESSION['user']['id'];

if ($test_id <= 0) {
    die("Lỗi: ID bài kiểm tra không hợp lệ.");
}

$service = new TestResultService();
$data = $service->getTestData($test_id);

if (!$data) {
    die("Lỗi: Không tìm thấy dữ liệu cho bài kiểm tra này.");
}

$test_info = $data['test_info'];
$students = $data['students'];

$default_comment_template = "
    <p><strong>Mức độ hoàn thành:</strong> </p>
    <p><strong>Phần làm tốt:</strong> </p>
    <ul>
        <li>...</li>
    </ul>
    <p><strong>Lỗi sai thường gặp:</strong> </p>
    <ul>
        <li>...</li>
    </ul>
    <p><strong>Quá trình học tập:</strong> </p>
    <p><strong>Lời khuyên của thầy/cô:</strong> </p>
";

$default_vditor_comment_template = "**Mức độ hoàn thành:** \n\n**Phần làm tốt:** \n* \n\n**Lỗi sai thường gặp:** \n* \n\n**Quá trình học tập:** \n\n**Lời khuyên của thầy/cô:** ";

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập điểm: <?= htmlspecialchars($test_info['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tải thư viện TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/<?= TINYMCE_API_KEY ?>/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <!-- Tải thư viện Vditor -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vditor/dist/index.css" />
    <script src="https://cdn.jsdelivr.net/npm/vditor/dist/index.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .autosave-field:focus { box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.4); }
        .flash-success { transition: background-color 0.1s ease-in-out; background-color: #dcfce7 !important; }
        .vditor { border-radius: 0.375rem; border: 1px solid #d1d5db; }
    </style>
</head>
<body class="bg-gray-100" x-data="testResultsApp()">

<main class="p-4 lg:p-6">
    <div class="container mx-auto">
        <!-- Page Title & Actions -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800"><?= nt($test_info['name']) ?></h1>
                <p class="text-lg text-green-600 font-semibold"><?= nt($test_info['class_code']) ?></p>
            </div>
            <button @click="openAddStudentModal()" class="mt-4 md:mt-0 px-4 py-2 font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                Thêm học viên
            </button>
        </div>

        <!-- Results Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="w-20 px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">STT</th>
                            <th scope="col" class="w-48 px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Họ và tên</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Điểm số</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nhận xét</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(student, index) in students" :key="student.result_id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="index + 1"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="student.full_name"></div>
                                    <div class="text-sm font-medium text-gray-400" x-text="formatDate(student.dob)"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" x-model="student.score" @blur="autosave(student.result_id, 'score', $event.target.value, $event.target)"
                                           class="autosave-field w-full p-2 border border-gray-300 rounded-md focus:outline-none">
                                </td>
                                <td class="px-6 py-4">
                                    <!-- <input type="text" x-model="student.comment" @blur="autosave(student.result_id, 'comment', $event.target.value, $event.target)"
                                           class="autosave-field w-full p-2 border border-gray-300 rounded-md focus:outline-none"> -->
                                    <!-- Rich Text Editor Container -->
                                    <!-- <div x-data="{
                                        initEditor() {
                                            tinymce.init({
                                                target: this.$refs.editor,
                                                height: 250,
                                                menubar: false,
                                                plugins: 'lists link autolink autoresize',
                                                toolbar: 'bold italic underline | bullist numlist | link',
                                                content_style: 'body { font-family:Inter,sans-serif; font-size:14px }',
                                                setup: (editor) => {
                                                    // Set initial content
                                                    editor.on('init', () => {
                                                        const initialContent = student.comment || defaultComment;
                                                        editor.setContent(initialContent);
                                                    });
                                                    
                                                    // Trigger autosave on blur (khi click ra ngoài)
                                                    editor.on('blur', () => {
                                                        const newContent = editor.getContent();
                                                        // Chỉ lưu nếu nội dung có thay đổi
                                                        if (newContent !== student.comment) {
                                                            student.comment = newContent;
                                                            autosave(student.result_id, 'comment', newContent, this.$el);
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                    }" x-init="initEditor()">
                                        <textarea x-ref="editor" class="w-full"></textarea>
                                    </div>        -->
                                    <!-- Vditor Editor Container -->
                                    <div x-data="{
                                        initVditor() {
                                            const vditor = new Vditor(this.$el, {
                                                height: 250,
                                                toolbar: ['headings', 'bold', 'italic', 'strike', '|', 'list', 'ordered-list', 'check', '|', 'link', 'table', 'quote', '|', 'undo', 'redo', '|', 'math', 'graph', 'chart'],
                                                cache: { enable: false },
                                                mode: 'ir', // Chế độ xem trước tức thì
                                                preview: {
                                                    math: {
                                                        engine: 'KaTeX', // Sử dụng KaTeX
                                                        inlineDigit: true
                                                    }
                                                },
                                                after: () => {
                                                    const initialContent = student.comment || defaultComment;
                                                    vditor.setValue(initialContent);
                                                },
                                                blur: (value) => {
                                                    // Chỉ lưu nếu nội dung có thay đổi
                                                    if (value !== student.comment) {
                                                        student.comment = value;
                                                        autosave(student.result_id, 'comment', value, this.$el.closest('td'));
                                                    }
                                                }
                                            });
                                        }
                                    }" x-init="initVditor()">
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="students.length === 0">
                            <tr>
                                <td colspan="4" class="text-center py-10 text-gray-500">Chưa có học viên nào trong bảng điểm.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Student Modal -->
<div x-show="addStudentModalOpen" class="fixed inset-0 z-30 bg-black bg-opacity-50 flex items-center justify-center p-4" x-cloak>
    <div @click.away="addStudentModalOpen = false" class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Thêm học viên vào Bảng điểm</h2>
        <div id="available-students-list" class="space-y-2 max-h-80 overflow-y-auto border p-3 rounded-md">
            <template x-if="loadingAvailable">
                <p class="text-gray-500">Đang tải danh sách...</p>
            </template>
            <template x-if="!loadingAvailable && availableStudents.length === 0">
                 <p class="text-gray-500">Tất cả học viên trong lớp đã có trong bảng điểm.</p>
            </template>
            <template x-for="student in availableStudents" :key="student.id">
                <label class="flex items-center p-2 rounded-md hover:bg-gray-100">
                    <input type="checkbox" :value="student.id" x-model="selectedStudents" class="h-4 w-4 text-green-600 border-gray-300 rounded">
                    <span class="ml-3 text-gray-700" x-text="student.full_name"></span>
                    <span class="ml-3 text-gray-400" x-text="formatDate(student.dob)"></span>
                </label>
            </template>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
            <button type="button" @click="addStudentModalOpen = false" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">Hủy</button>
            <button @click="addSelectedStudents()" :disabled="selectedStudents.length === 0 || submitting" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:bg-gray-400">
                <span x-show="!submitting">Thêm</span>
                <span x-show="submitting">Đang thêm...</span>
            </button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function testResultsApp() {
        return {
            students: <?php echo json_encode($students); ?>,
            defaultComment: <?php echo json_encode($default_vditor_comment_template); ?>,
            testId: <?php echo $test_info['id']; ?>,
            classId: <?php echo $test_info['class_id']; ?>,
            currentUserId: <?php echo $current_user_id; ?>,
            addStudentModalOpen: false,
            availableStudents: [],
            selectedStudents: [],
            loadingAvailable: false,
            submitting: false,

            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                if (isNaN(date)) return ''; // Trả về chuỗi rỗng nếu ngày không hợp lệ
                
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0'); // Tháng trong JS bắt đầu từ 0
                const year = date.getFullYear();
                
                return `${day}/${month}/${year}`;
            },

            async openAddStudentModal() {
                this.addStudentModalOpen = true;
                this.loadingAvailable = true;
                this.availableStudents = [];
                try {
                    const response = await fetch(`/ajax.php?action=get_students_for_test&test_id=${this.testId}&class_id=${this.classId}`);
                    const result = await response.json();
                    if (result.success) {
                        this.availableStudents = result.data;
                    }
                } catch (error) {
                    console.error('Lỗi tải danh sách học viên:', error);
                } finally {
                    this.loadingAvailable = false;
                }
            },

            async addSelectedStudents() {
                this.submitting = true;
                try {
                    const response = await fetch('/ajax.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'add_students_to_test',
                            test_id: this.testId,
                            student_ids: this.selectedStudents,
                            current_user_id: this.currentUserId
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.students = result.data; // Cập nhật lại bảng điểm với danh sách mới
                        this.addStudentModalOpen = false;
                        this.selectedStudents = [];
                    }
                } catch (error) {
                    console.error('Lỗi thêm học viên:', error);
                } finally {
                    this.submitting = false;
                }
            },

            async autosave(resultId, field, value, element) {
                element.classList.remove('flash-success');
                try {
                    const response = await fetch('/ajax.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'update_test_result_field',
                            result_id: resultId,
                            field: field,
                            value: value,
                            current_user_id: this.currentUserId
                        })
                    });
                    const result = await response.json();
                    if(result.success) {
                        element.classList.add('flash-success');
                        setTimeout(() => {
                            element.classList.remove('flash-success');
                         }, 800); // Hiệu ứng tồn tại trong 0.5 giây
                    }
                } catch (error) {
                    console.error('Lỗi lưu dữ liệu:', error);
                }
            }
        }
    }
</script>
</body>
</html>
