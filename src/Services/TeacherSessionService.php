<?php
namespace App\Services;

use App\Models\SessionModel;
use App\Models\StudentModel;
use App\Models\StaffModel; // Giả sử có Model này để lấy thông tin nhân sự

/**
 * TeacherSessionService
 *
 * Chứa logic nghiệp vụ cho trang chi tiết ca học của giáo viên.
 */
class TeacherSessionService
{
    protected $sessionModel;
    protected $studentModel;
    protected $staffModel;

    public function __construct()
    {
        $this->sessionModel = new SessionModel();
        $this->studentModel = new StudentModel();
        $this->staffModel = new StaffModel();
    }

    /**
     * Lấy toàn bộ dữ liệu chi tiết cho một ca học.
     *
     * @param int $sessionId
     * @return array|null
     */
    public function getSessionDetails(int $sessionId): ?array
    {
        // 1. Lấy thông tin cơ bản của ca học và lớp học
        $sessionDetails = $this->sessionModel->findWithClassDetails($sessionId);
        if (!$sessionDetails) {
            return null;
        }

        // 2. Lấy danh sách học viên đã được khởi tạo cho ca học này
        $students = $this->studentModel->getStudentsForSession($sessionId);

        // 3. Lấy thông tin nhân sự hỗ trợ cho lớp
        $staff = $this->staffModel->getSupportStaffForClass($sessionDetails['class_id']);

        // 4. Lấy các tiêu chí đánh giá (có thể từ CSDL hoặc config)
        $operation_criteria = [
            ['tag' => 'operation_support', 'label' => 'Công tác vận hành chung'],
            ['tag' => 'facilities', 'label' => 'Trang thiết bị, CSVC'],
            ['tag' => 'cleanliness', 'label' => 'Vệ sinh lớp học'],
        ];

        // 5. Tổng hợp và trả về dữ liệu
        return [
            'session_details' => array_merge($sessionDetails, ['staff' => $staff]),
            'students' => $students,
            'operation_criteria' => $operation_criteria,
            // ... có thể thêm feedback_options ở đây nếu muốn lấy từ CSDL
        ];
    }
}
