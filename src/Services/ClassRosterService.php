<?php
namespace App\Services;

use App\Models\ClassModel;
use App\Models\StudentModel;
use App\Models\ParentModel;
use App\Services\S3StorageService;

/**
 * ClassRosterService
 *
 * Chứa logic nghiệp vụ cho việc quản lý danh sách học viên trong lớp.
 */
class ClassRosterService
{
    protected $classModel;
    protected $studentModel;
    protected $parentModel;
    protected $storageService;

    const ITEMS_PER_PAGE = 9;

    public function __construct()
    {
        $this->classModel = new ClassModel();
        $this->studentModel = new StudentModel();
        $this->parentModel = new ParentModel();
        $this->storageService = new S3StorageService();
    }

    /**
     * Lấy dữ liệu danh sách học viên cho một lớp cụ thể, có phân trang.
     *
     * @param int $classId
     * @param int $page Trang hiện tại
     * @return array|null
     */
    public function getClassRoster(int $classId, int $page = 1): ?array
    {
        $classInfo = $this->classModel->find($classId);
        if (!$classInfo) {
            return null;
        }

        // Lấy tổng số học viên để tính toán phân trang
        $totalStudents = $this->studentModel->countEnrolledStudentsByClassId($classId);
        $totalPages = ceil($totalStudents / self::ITEMS_PER_PAGE);
        
        // Đảm bảo trang hiện tại hợp lệ
        $page = max(1, min($page, (int)$totalPages));
        
        // Tính offset để truy vấn CSDL
        $offset = ($page - 1) * self::ITEMS_PER_PAGE;

        // Lấy danh sách học viên cho trang hiện tại
        $students = $this->studentModel->getEnrolledStudentsByClassId($classId, self::ITEMS_PER_PAGE, $offset);

        // Trả về dữ liệu bao gồm cả thông tin phân trang
        return [
            'class_info' => $classInfo,
            'students' => $students,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => (int)$totalPages,
                'total_items' => $totalStudents
            ]
        ];
    }

    /**
     * Cập nhật thông tin chi tiết của một học viên, bao gồm cả việc upload avatar lên S3.
     *
     * @param int $studentId ID của học viên cần cập nhật.
     * @param array $formData Dữ liệu từ form.
     * @param array|null $fileData Dữ liệu file avatar được tải lên (từ $_FILES).
     * @return array|null Dữ liệu học viên sau khi cập nhật, hoặc null nếu thất bại.
     */
    public function updateStudentDetails(int $studentId, array $formData, ?array $fileData): ?array
    {
        $studentDataToUpdate = [];
        $parentDataToUpdate = [];

        // 1. Xử lý tải lên ảnh đại diện nếu có file mới
        if ($fileData && $fileData['error'] === UPLOAD_ERR_OK) {
            $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
            
            // Tạo tên file duy nhất trên S3 theo ID học sinh và timestamp để tránh cache
            $s3Key = 'public/images/students/' . $studentId . '_' . time() . '.' . $fileExtension;
            
            // Gọi S3StorageService để tải file lên
            $avatarUrl = $this->storageService->uploadFile(
                $fileData['tmp_name'],
                $s3Key,
                $fileData['type']
            );

            if ($avatarUrl) {
                // Nếu tải lên thành công, thêm URL vào mảng dữ liệu để cập nhật CSDL
                $studentDataToUpdate['avatar'] = $avatarUrl;
            } else {
                // Nếu tải lên thất bại, có thể dừng lại và báo lỗi
                error_log("Failed to upload avatar to S3 for student ID: " . $studentId);
                // Bạn có thể ném một Exception ở đây để handler AJAX bắt được
                // throw new \Exception("Không thể tải ảnh đại diện lên.");
            }
        }

        // 2. Lọc và chuẩn bị dữ liệu cho bảng `students`
        $studentAllowedFields = ['dob'];
        foreach ($studentAllowedFields as $field) {
            if (isset($formData[$field])) {
                $studentDataToUpdate[$field] = $formData[$field];
            }
        }
        // Ánh xạ 'learning_goal' từ form sang 'aspiration' trong CSDL
        if (isset($formData['learning_goal'])) {
            $studentDataToUpdate['aspiration'] = $formData['learning_goal'];
        }
        if (isset($formData['full_name'])) {
            $name = split_fullname($formData['full_name']);
            $studentDataToUpdate['first_name'] = $name['first_name'];
            $studentDataToUpdate['last_name'] = $name['last_name'];
            $studentDataToUpdate['full_name'] = $name['last_name'].' '.$name['first_name'];
        }

        // 3. Lọc và chuẩn bị dữ liệu cho bảng `parents`
        $parentDataMapping = [
            'fullname' => 'parent_name',
            'phone' => 'parent_phone',
            'email' => 'parent_email'
        ];
        foreach ($parentDataMapping as $dbField => $formField) {
            if (isset($formData[$formField])) {
                $parentDataToUpdate[$dbField] = $formData[$formField];
            }
        }

        // 4. Cập nhật bảng `students`
        if (!empty($studentDataToUpdate)) {
            $this->studentModel->updateDetails($studentId, $studentDataToUpdate);
        }

        // 5. Cập nhật bảng `parents`
        if (!empty($parentDataToUpdate)) {
            // Lấy parent_id từ bảng students
            $parentId = $this->studentModel->getParentId($studentId);
            if ($parentId) {
                $this->parentModel->updateDetails($parentId, $parentDataToUpdate);
            }
            // Cân nhắc: Nếu $parentId là null, có thể tạo mới một bản ghi parent
            // và cập nhật student.parent_id. Hiện tại, chỉ cập nhật nếu đã có.
        }

        // 4. Trả về dữ liệu mới nhất của học viên (giữ nguyên)
        return $this->studentModel->findDetails($studentId);
    }
}
