<?php
namespace App\Services;

use App\Models\ClassModel;
use App\Models\StudentModel;
use App\Models\AttendanceModel;
use App\Models\AcademicResultModel;

/**
 * TeacherClassService
 *
 * Chứa logic nghiệp vụ cho trang "Lớp học của tôi" của giáo viên.
 */
class TeacherClassService
{
    protected $classModel;
    protected $studentModel;
    protected $attendanceModel;
    protected $academicResultModel;

    public function __construct()
    {
        $this->classModel = new ClassModel();
        $this->studentModel = new StudentModel();
        $this->attendanceModel = new AttendanceModel();
        $this->academicResultModel = new AcademicResultModel();
    }

    /**
     * Lấy danh sách lớp học và các chỉ số liên quan cho một giáo viên.
     *
     * @param int $teacherId
     * @return array
     */
    public function getClassListData(int $teacherId): array
    {
        // 1. Lấy danh sách các lớp học cơ bản mà giáo viên dạy
        $classes = $this->classModel->getActiveClassesForTeacher($teacherId);
        if (empty($classes)) {
            return [];
        }

        $classIds = array_column($classes, 'id');

        // 2. Lấy sĩ số cho tất cả các lớp trong một lần truy vấn
        $studentCounts = $this->studentModel->getStudentCountsForClasses($classIds);

        // 3. Lấy tỷ lệ chuyên cần cho tất cả các lớp
        $attendanceRates = $this->attendanceModel->getAttendanceRatesForClasses($classIds);
        
        // 4. Lấy điểm trung bình cho tất cả các lớp
        $averageScores = $this->academicResultModel->getAverageScoresForClasses($classIds);

        // 5. Gộp tất cả dữ liệu lại
        foreach ($classes as &$class) {
            $classId = $class['id'];
            $class['student_count'] = $studentCounts[$classId]['current_students'] ?? 0;
            $class['total_students'] = $studentCounts[$classId]['total_capacity'] ?? 0; // Giả sử có trường này
            $class['attendance_rate'] = isset($attendanceRates[$classId]) ? round($attendanceRates[$classId]) : null;
            $class['avg_score'] = isset($averageScores[$classId]) ? round($averageScores[$classId], 1) : null;
            
            // Logic demo cho trạng thái và buổi học tới
            $class['status'] = 'Đang diễn ra';
            $class['status_color'] = 'green';
            $class['next_session_id'] = $class['id'] * 100 + 1; // Demo session id
        }

        return $classes;
    }
}