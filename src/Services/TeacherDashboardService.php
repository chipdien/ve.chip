<?php
namespace App\Services;

use App\Models\SessionModel;
use App\Models\ClassModel;
use App\Models\StudentModel;
use App\Models\AttendanceModel;

/**
 * TeacherDashboardService
 *
 * Chứa logic nghiệp vụ để lấy dữ liệu cho trang Dashboard của giáo viên.
 */
class TeacherDashboardService
{
    /** @var SessionModel */
    protected $sessionModel;
    /** @var ClassModel */
    protected $classModel;
    /** @var StudentModel */
    protected $studentModel;
    /** @var AttendanceModel */
    protected $attendanceModel;

    public function __construct()
    {
        $this->sessionModel = new SessionModel();
        $this->classModel = new ClassModel();
        $this->studentModel = new StudentModel();
        $this->attendanceModel = new AttendanceModel();
    }

    /**
     * Lấy toàn bộ dữ liệu cần thiết cho Dashboard.
     *
     * @param array $user Thông tin người dùng từ session.
     * @return array Dữ liệu đã được xử lý cho view.
     */
    public function getDashboardData(array $user): array
    {
        $teacherId = $user['teacher']['id'];
        $today = date('Y-m-d');
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

        // 1. Lấy Lịch dạy hôm nay
        $rawSchedule = $this->sessionModel->getScheduleForTeacherOnDate($teacherId, $today);
        // Định dạng lại thời gian để hiển thị
        $today_schedule = array_map(function($session) {
            $session['time'] = date('H:i', strtotime($session['from'])) . ' - ' . date('H:i', strtotime($session['to']));
            return $session;
        }, $rawSchedule);

        // 2. Lấy danh sách các lớp học mà giáo viên đang dạy
        $my_classes = $this->classModel->getActiveClassesForTeacher($teacherId);
        $classIds = !empty($my_classes) ? array_column($my_classes, 'id') : [];

        // 3. Tính toán các chỉ số thống kê nhanh
        $activeClassesCount = count($my_classes);
        $totalStudents = 0;
        $avgStudents = 0;
        if ($activeClassesCount > 0) {
            $totalStudents = $this->studentModel->countEnrolledStudentsInClasses($classIds);
            $avgStudents = round($totalStudents / $activeClassesCount);
        }
        
        $attendanceRate = $this->attendanceModel->getAttendanceRateForTeacher($teacherId, $thirtyDaysAgo, $today);

        $quick_stats = [
            'active_classes' => $activeClassesCount,
            'avg_students' => $avgStudents,
            'attendance_rate' => round($attendanceRate) . '%',
            'students_to_watch' => 3 // Giữ demo, vì tính năng này phức tạp hơn
        ];




        // 4. Lấy Thông báo (Dữ liệu demo, sẽ được thay thế bằng logic thực tế)
        $announcements = [
            ['type' => 'meeting', 'content' => 'Họp chuyên môn toàn trung tâm vào 16:00 thứ Sáu tuần này.'],
            ['type' => 'reminder', 'content' => 'Vui lòng hoàn thành báo cáo học tập cho lớp B2-2024 trước ngày 20/08.'],
            ['type' => 'info', 'content' => 'Giáo trình mới cho khóa hè đã được cập nhật trên hệ thống.'],
        ];

        // // 3. Lấy Thống kê nhanh (Dữ liệu demo)
        // $quick_stats = ['active_classes' => 5, 'avg_students' => 12, 'attendance_rate' => '95%', 'students_to_watch' => 3];

        // // 4. Lấy danh sách lớp (Dữ liệu demo)
        // $my_classes = [['id' => 1, 'code' => 'A1-2025'], ['id' => 2, 'code' => 'B2-2024'], ['id' => 3, 'code' => 'C1-IELTS']];

        return [
            'today_schedule' => $today_schedule,
            'announcements' => $announcements,
            'quick_stats' => $quick_stats,
            'my_classes' => $my_classes,
        ];
    }
}
