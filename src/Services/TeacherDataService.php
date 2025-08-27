<?php
namespace App\Services;

use App\Models\SessionModel;
use App\Models\ClassModel;
use App\Models\StudentModel;
use App\Models\AttendanceModel;
use App\Models\FeedbackOptionModel;
use App\Models\SessionInteractionModel;
use App\Models\SessionFeedbackModel;


/**
 * TeacherDataService
 *
 * Chứa logic nghiệp vụ để lấy dữ liệu cho Module Teacher dành riêng cho giáo viên.
 */
class TeacherDataService
{
    /** @var SessionModel */
    protected $sessionModel;

    protected $classModel;
    protected $studentModel;
    protected $attendanceModel;

    protected $feedbackOptionModel;
    protected $sessionInteractionModel;
    protected $sessionFeedbackModel;


    

    // Dữ liệu giả lập (private để đảm bảo tính đóng gói)

    private $classes_demo = [
        101 => ['id' => 101, 'code' => 'A1-2025', 'name' => 'Tiếng Anh Giao tiếp Cơ bản', 'status' => 'Đang diễn ra', 'status_color' => 'green', 'attendance_rate' => 98, 'needs_attention' => false],
        102 => ['id' => 102, 'code' => 'C1-IELTS', 'name' => 'Luyện thi IELTS 7.0+', 'status' => 'Đang diễn ra', 'status_color' => 'green', 'attendance_rate' => 85, 'needs_attention' => true],
        103 => ['id' => 103, 'code' => 'KIDS-STARTERS', 'name' => 'Tiếng Anh Thiếu nhi Starters', 'status' => 'Sắp bắt đầu', 'status_color' => 'blue', 'attendance_rate' => null, 'needs_attention' => false],
    ];
    

    private $favorite_functions_demo = [
        ['label' => 'Nhập điểm', 'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>'],
        ['label' => 'Soạn giáo án', 'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v11.494m-9-5.747h18" /></svg>'],
        ['label' => 'Gửi thông báo', 'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>'],
        ['label' => 'Báo cáo', 'icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'],
    ];

    private $weekly_schedule_demo = [
        ['day' => 'Thứ Hai', 'date' => '18/08', 'sessions' => [
            ['session_id' => 301, 'time' => '18:00 - 19:30', 'class_code' => 'A1-2025', 'subject' => 'Unit 5: Present Tense', 'room' => 'Phòng 201'],
        ]],
        ['day' => 'Thứ Ba', 'date' => '19/08', 'sessions' => [
            ['session_id' => 302, 'time' => '19:00 - 20:30', 'class_code' => 'C1-IELTS', 'subject' => 'Speaking Part 1', 'room' => 'Phòng 303'],
        ]],
        ['day' => 'Thứ Tư', 'date' => '20/08', 'sessions' => [
            ['session_id' => 303, 'time' => '18:00 - 19:30', 'class_code' => 'A1-2025', 'subject' => 'Unit 6: Past Tense', 'room' => 'Phòng 201'],
        ]],
        ['day' => 'Thứ Năm', 'date' => '21/08', 'sessions' => []],
        ['day' => 'Thứ Sáu', 'date' => '22/08', 'sessions' => [
            ['session_id' => 304, 'time' => '18:00 - 19:30', 'class_code' => 'A1-2025', 'subject' => 'Unit 7: Future Tenses', 'room' => 'Phòng 201'],
        ]],
        ['day' => 'Thứ Bảy', 'date' => '23/08', 'sessions' => [
            ['session_id' => 305, 'time' => '08:00 - 09:30', 'class_code' => 'KIDS-STARTERS', 'subject' => 'Lesson 10: Animals', 'room' => 'Phòng 101'],
            ['session_id' => 306, 'time' => '19:00 - 20:30', 'class_code' => 'C1-IELTS', 'subject' => 'Writing Task 1', 'room' => 'Phòng 303'],
        ]],
        ['day' => 'Chủ Nhật', 'date' => '24/08', 'sessions' => [
            ['session_id' => 307, 'time' => '08:00 - 09:30', 'class_code' => 'KIDS-STARTERS', 'subject' => 'Lesson 11: Colors', 'room' => 'Phòng 101'],
        ]],
    ];

    // Dữ liệu mẫu cho các ca học trong tuần
    private $sessions_by_day_of_week = [
        1 => [['session_id' => 301, 'time' => '18:00 - 19:30', 'class_code' => 'A1-2025', 'subject' => 'Unit 5: Present Tense', 'room' => 'Phòng 201']], // Thứ 2
        2 => [['session_id' => 302, 'time' => '19:00 - 20:30', 'class_code' => 'C1-IELTS', 'subject' => 'Speaking Part 1', 'room' => 'Phòng 303']], // Thứ 3
        3 => [['session_id' => 303, 'time' => '18:00 - 19:30', 'class_code' => 'A1-2025', 'subject' => 'Unit 6: Past Tense', 'room' => 'Phòng 201']], // Thứ 4
        4 => [], // Thứ 5
        5 => [['session_id' => 304, 'time' => '18:00 - 19:30', 'class_code' => 'A1-2025', 'subject' => 'Unit 7: Future Tenses', 'room' => 'Phòng 201']], // Thứ 6
        6 => [ // Thứ 7
            ['session_id' => 305, 'time' => '08:00 - 09:30', 'class_code' => 'KIDS-STARTERS', 'subject' => 'Lesson 10: Animals', 'room' => 'Phòng 101'],
            ['session_id' => 306, 'time' => '19:00 - 20:30', 'class_code' => 'C1-IELTS', 'subject' => 'Writing Task 1', 'room' => 'Phòng 303'],
        ],
        7 => [['session_id' => 307, 'time' => '08:00 - 09:30', 'class_code' => 'KIDS-STARTERS', 'subject' => 'Lesson 11: Colors', 'room' => 'Phòng 101']], // Chủ nhật
    ];
    
    public function __construct()
    {
        $this->sessionModel = new SessionModel();
        $this->classModel = new ClassModel();
        $this->studentModel = new StudentModel();
        // $this->attendanceModel = new AttendanceModel();
        $this->feedbackOptionModel = new FeedbackOptionModel();
        $this->sessionInteractionModel = new SessionInteractionModel();
        $this->sessionFeedbackModel = new SessionFeedbackModel();
    }


    private function getVietnameseDayName(string $dayOfWeek): string {
        $days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
        return $days[(int)$dayOfWeek];
    }

    /**
     * NÂNG CẤP: Tạo lịch dạy linh động xoay quanh ngày hiện tại.
     * @return array
     */
    public function getDynamicWeeklySchedule(int $teacherId): array {
        $sessions = $this->sessionModel->getScheduleForTeacher($teacherId);

        $scheduleByDate = [];
        foreach ($sessions as $session) {
            $scheduleByDate[$session['date']][] = $session;
        }

        $totalDates = array_keys($scheduleByDate);
        $finalSchedule = [];
        foreach ($totalDates as $d) {
            $finalSchedule[$d] = [
                'day' => ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'][(int)date("w", strtotime($d))],
                'date' => date("d/m", strtotime($d)),
                'full_date' => $d,
                'isToday' => $d === date("Y-m-d") ? true : false,
                'sessions' => $scheduleByDate[$d]
            ];
        }

        return $finalSchedule;
    }

    /**
     * (MỚI) Lấy thông tin chi tiết của một ca học cụ thể.
     * @param int $sessionId ID của ca học.
     * @return array|null Dữ liệu chi tiết hoặc null nếu không tìm thấy.
     */
    public function getSessionDetails(int $sessionId): ?array 
    {
        $session_detail = $this->sessionModel->getSessionDetail($sessionId);
        
        $dayOfWeek = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'][(int) date("w", strtotime($session_detail['date']))];
        $session_detail['day'] = $dayOfWeek;

        $session_detail['content_files_array'] = getFilesFromString($session_detail['content_files']); // Ghi đè lại bằng mảng đã xử lý
        $session_detail['exercice_files_array'] = getFilesFromString($session_detail['exercice_files']); // Ghi đè lại bằng mảng đã xử lý

        $feedback = $this->sessionFeedbackModel->findOneBy(['session_id' => $sessionId, 'teacher_id' => $session_detail['teacher_id']]);
        $session_detail['feedback'] = $feedback;

        return $session_detail;
    }



    // --- PHƯƠNG THỨC MỚI ---
    public function getWeeklySchedule(): array {
        return $this->weekly_schedule_demo;
    }

    public function getTeacherInfo(): array {
        return $_SESSION['user']['teacher'] ?? [];
    }

    public function getClasses(): array {
        return array_values($this->classes_demo);
    }
    
    public function getAttentionClasses(): array {
        return array_filter($this->classes_demo, function($class) {
            return $class['needs_attention'] === true;
        });
    }

    public function getFavoriteFunctions(): array {
        return $this->favorite_functions_demo;
    }

    /**
     * Lấy toàn bộ dữ liệu cần thiết để khởi tạo ứng dụng.
     * Trong thực tế, bạn có thể tách thành nhiều API call nhỏ hơn
     * và chỉ gọi khi cần (Lazy Loading).
     */
    public function getInitialAppData(): array {
        $teacher = $this->getTeacherInfo();

        // $weeklySchedule = $this->getDynamicWeeklySchedule($teacher['id']);
        $todaySchedule = $this->sessionModel->getScheduleForTeacherOnDate($teacher['id'], date('Y-m-d'));
        $my_classes = $this->classModel->getActiveClassesForTeacher($teacher['id']);
        $classIds = !empty($my_classes) ? array_column($my_classes, 'id') : [];
        // $classes = $this->classModel->findBy(['id' => $classIds]);
        $classes = $this->classModel->getClassesInfoBy(['edu_classes.id' => $classIds]);

        return [
            'teacher' => $teacher,
            'todaySchedule' => $todaySchedule,
            'favoriteFunctions' => $this->getFavoriteFunctions(),
            'attentionClasses' => $this->getAttentionClasses(),
            'classes' => $classes,
            // 'activeClasses' => $classes,
            // 'weeklySchedule' => $weeklySchedule,
        ];
    }


    /**
     * Lấy danh sách học sinh và trạng thái điểm danh của một ca học.
     */
    public function getSessionStudents(int $sessionId, int $classId): array 
    {
        if (!$classId) {
            return []; // Không tìm thấy lớp học cho ca này
        }

        // Sử dụng UNION để gộp hai danh sách và tự động loại bỏ trùng lặp
        $sql = "
            -- 1. Lấy tất cả học sinh đã có dữ liệu điểm danh trong ca học này
            (SELECT 
                <students.id>, <students.first_name>, <students.last_name>, <students.full_name>, <students.avatar>, <students.dob>,
                <edu_student_session_attendance.attendance> as attendance_status
            FROM <edu_student_session_attendance> 
            JOIN <students> ON <edu_student_session_attendance.student_id> = <students.id>
            WHERE <edu_student_session_attendance.session_id> = :session_id)

            UNION ALL
            
            -- 2. Lấy tất cả học sinh đang có trạng thái 'enroll' trong lớp
            (SELECT 
                <students.id>, <students.first_name>, <students.last_name>, <students.full_name>, <students.avatar>, <students.dob>,
                'holding' as attendance_status
            FROM <edu_student_class>
            JOIN <students> ON <edu_student_class.student_id> = <students.id>
            WHERE <edu_student_class.class_id> = :class_id AND <edu_student_class.status> = 'enroll'
                AND <edu_student_class.student_id> NOT IN (
                    SELECT <student_id> FROM <edu_student_session_attendance> WHERE <session_id> = :session_id_for_subquery
                ))

            ORDER BY <first_name> ASC, <full_name> ASC
        ";

        $data = $this->classModel->query($sql, [
            ':session_id' => $sessionId,
            ':class_id' => $classId,
            ':session_id_for_subquery' => $sessionId,
        ]);
        $students = [];
        if ($data) {
            foreach ($data as $student) {
                $students[$student['id']] = $student;
            }
        }

        return $students;
    }

    /**
     * (MỚI) Lấy các tùy chọn feedback được định nghĩa sẵn.
     * Trong thực tế, dữ liệu này sẽ được lấy từ bảng `edu_feedback_options`.
     */
    public function getFeedbackOptions(): array 
    {
        // return $this->feedbackOptionModel->findAll();
    
        // Dữ liệu giả lập, bạn có thể thay thế bằng truy vấn CSDL
        return [
            'interaction' => ['tag' => 'interaction', 'label' => 'Tích cực', 'icon' => '👍', 'type' => 'positive', 'points' => 5],
            'focus' => ['tag' => 'focus', 'label' => 'Tập trung', 'icon' => '🎯', 'type' => 'positive', 'points' => 3],
            'progress' => ['tag' => 'progress', 'label' => 'Tiến bộ', 'icon' => '🚀', 'type' => 'positive', 'points' => 10],
            'disruptive' => ['tag' => 'disruptive', 'label' => 'Mất trật tự', 'icon' => '🗣️', 'type' => 'warning', 'points' => -2],
            'careless' => ['tag' => 'careless', 'label' => 'BTVN ẩu', 'icon' => '✍️', 'type' => 'warning', 'points' => -3],
            'distracted' => ['tag' => 'distracted', 'label' => 'Mất tập trung', 'icon' => '😵', 'type' => 'negative', 'points' => -3],
        ];
    }

    public function getSessionInteractions(array $studentIds, int $sessionId): array 
    {
        return $this->sessionInteractionModel->findBy(['student_id' => $studentIds, 'session_id' => $sessionId])? : [];
    }


    /**
     * (MỚI) Lấy toàn bộ thông tin chi tiết cho một lớp học.
     * @param int $classId ID của lớp học
     * @return array|null Dữ liệu chi tiết hoặc null nếu không tìm thấy
     */
    public function getClassDetails(int $classId, int $teacherId): ?array 
    {
        // 1. Lấy thông tin cơ bản của lớp

        $classInfo = $this->classModel->getClassInfoBy(['edu_classes.id' => $classId]);

        if (!$classInfo) return null;

        // 2. Lấy danh sách học sinh đang 'enroll'
        $students = $this->studentModel->getEnrolledStudentsByClassId($classId, 50, 0);

        // 3. Lấy danh sách ca học 
        $sessions = $this->sessionModel->getSessions(['class_id' => $classId, 'teacher_id' => $teacherId, 'type' => 'past'], 1, 100);

        // 4. Lấy danh sách tài liệu 
        $s3service = new S3StorageService();
        $documents = $s3service->getClassDocuments(trim($classInfo['code'])) ?? [];
        uasort($documents, function ($a, $b) {
            // Chuyển đổi chuỗi ngày thành timestamp để so sánh chính xác
            $timeA = strtotime($a['session_date']);
            $timeB = strtotime($b['session_date']);
            
            // So sánh để sắp xếp giảm dần (DESC)
            return $timeB <=> $timeA;
        });

        return [
            'info' => $classInfo,
            'students' => $students,
            'sessions' => $sessions,
            'documents' => $documents
        ];
    }




}