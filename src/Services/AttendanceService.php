<?php
namespace App\Services;

// Giả sử bạn đã tạo các Model này.
// Service sẽ sử dụng chúng để tương tác với CSDL.
use App\Models\AcademicResultModel;
use App\Models\AttendanceModel;
use App\Models\ClassModel;
use App\Models\SessionModel;
use App\Models\StudentModel;

/**
 * Lớp AttendanceService
 *
 * Chứa tất cả logic nghiệp vụ phức tạp liên quan đến
 * chức năng điểm danh và nhận xét buổi học.
 */
class AttendanceService
{
    /**
     * @var SessionModel
     */
    protected $sessionModel;

    /**
     * @var ClassModel
     */
    protected $classModel;

    /**
     * @var StudentModel
     */
    protected $studentModel;

    /**
     * @var AttendanceModel
     */
    protected $attendanceModel;

    /**
     * @var AcademicResultModel
     */
    protected $academicResultModel;

    /**
     * Hàm khởi tạo (Constructor)
     *
     * Khởi tạo các lớp Model cần thiết. Trong một ứng dụng lớn,
     * bạn sẽ dùng Dependency Injection Container ở đây.
     */
    public function __construct()
    {
        $this->sessionModel = new SessionModel();
        $this->classModel = new ClassModel();
        $this->studentModel = new StudentModel();
        $this->attendanceModel = new AttendanceModel();
        $this->academicResultModel = new AcademicResultModel();
    }

    /**
     * Lấy toàn bộ dữ liệu cần thiết cho trang điểm danh.
     * Đây là hàm cốt lõi để hiển thị trang.
     *
     * @param int $sessionId ID của ca học đang được xem.
     * @return array|null Dữ liệu cho trang hoặc null nếu có lỗi.
     */
    public function getAttendanceSheetData(int $sessionId): ?array
    {
        // 1. Lấy thông tin ca học hiện tại để xác định ngày và lớp
        $currentSession = $this->sessionModel->find($sessionId, ['class_id', 'date']);
        if (!$currentSession) {
            return null; // Ca học không tồn tại
        }

        $classId = $currentSession['class_id'];
        $sessionDate = $currentSession['date'];

        // 2. Lấy thông tin cơ bản của lớp học
        $classInfo = $this->classModel->find($classId, ['id', 'code']);
        if (!$classInfo) {
            return null; // Lớp học không tồn tại
        }

        // 3. Lấy TẤT CẢ các ca học của lớp trong ngày hôm đó
        $sessionsOfTheDay = $this->sessionModel->getSessionsForClassOnDate($classId, $sessionDate);
        if (empty($sessionsOfTheDay)) {
            return null;
        }
        $sessionIdsOfTheDay = array_column($sessionsOfTheDay, 'id');

        // 4. Lấy ID của tất cả học viên đã được khởi tạo cho các ca học trong ngày
        $studentIds = $this->attendanceModel->getUniqueStudentIdsForSessions($sessionIdsOfTheDay);

        // Nếu không có học viên nào, trả về cấu trúc rỗng
        if (empty($studentIds)) {
            return [
                'class_info' => $classInfo,
                'session_date' => $sessionDate,
                'sessions_of_the_day' => $sessionsOfTheDay,
                'students' => []
            ];
        }

        // 5. Lấy thông tin chi tiết của các học viên
        $studentsInfo = $this->studentModel->findByIds($studentIds, ['id', 'full_name', 'dob']);

        // 6. Lấy toàn bộ bản ghi điểm danh và kết quả học tập trong một lần truy vấn
        $attendanceRecords = $this->attendanceModel->getRecordsForStudentsInSessions($studentIds, $sessionIdsOfTheDay);
        $academicResults = $this->academicResultModel->getResultsForStudentsInSessions($studentIds, $sessionIdsOfTheDay);

        // 7. Xây dựng cấu trúc dữ liệu cuối cùng để trả về cho View
        $studentDataMap = [];
        // Khởi tạo map với thông tin cơ bản
        foreach ($studentsInfo as $student) {
            $studentDataMap[$student['id']] = ['info' => $student, 'sessions' => []];
        }

        // Ghép dữ liệu điểm danh
        foreach ($attendanceRecords as $record) {
            $studentDataMap[$record['student_id']]['sessions'][$record['session_id']]['attendance'] = $record['attendance'];
        }

        // Ghép dữ liệu kết quả học tập
        foreach ($academicResults as $result) {
            $studentId = $result['student_id'];
            $sessId = $result['session_id'];
            $studentDataMap[$studentId]['sessions'][$sessId]['btvn_max'] = $result['btvn_max'];
            $studentDataMap[$studentId]['sessions'][$sessId]['btvn_complete'] = $result['btvn_complete'];
            $studentDataMap[$studentId]['sessions'][$sessId]['btvn_score'] = $result['btvn_score'];
            $studentDataMap[$studentId]['sessions'][$sessId]['btvn_comment'] = $result['btvn_comment'];
            $studentDataMap[$studentId]['sessions'][$sessId]['comment'] = $result['comment'];
        }

        return [
            'class_info' => $classInfo,
            'session_date' => $sessionDate,
            'sessions_of_the_day' => $sessionsOfTheDay,
            'students' => $studentDataMap
        ];
    }

    /**
     * Lấy danh sách học sinh đang 'enroll' và chưa được thêm vào bảng điểm danh.
     * Phục vụ cho Popup "Thêm học viên".
     *
     * @param int $sessionId ID của một ca học trong ngày.
     * @return array Danh sách học viên có dạng ['id' => id, 'full_name' => name].
     */
    public function getStudentsForInitialization(int $sessionId): array
    {
        $classId = $this->sessionModel->getClassIdForSession($sessionId);
        if (!$classId) {
            return [];
        }

        // Lấy ID học viên đã tồn tại trong bảng điểm danh của ca học này
        $existingStudentIds = $this->attendanceModel->getStudentIdsInSession($sessionId);

        // Lấy danh sách học viên đang 'enroll' trong lớp, trừ những người đã tồn tại
        return $this->studentModel->getEnrolledStudentsNotInList($classId, $existingStudentIds);
    }
    
    /**
     * Khởi tạo dữ liệu điểm danh và học tập cho một danh sách học sinh được chọn.
     * Hàm này sẽ khởi tạo cho TẤT CẢ các ca trong ngày của lớp đó.
     *
     * @param int $sessionId ID của một ca học để xác định lớp và ngày.
     * @param array $studentIds Mảng các ID của học sinh được chọn.
     * @param int $userId ID của người thực hiện hành động.
     * @return int Số lượng học sinh được thêm thành công.
     */
    public function initializeDataForSelectedStudents(int $sessionId, array $studentIds, int $userId): int
    {
        if (empty($studentIds)) {
            return 0;
        }

        $currentSession = $this->sessionModel->find($sessionId, ['class_id', 'date']);
        if (!$currentSession) {
            return 0;
        }
        
        // Lấy tất cả các ca trong ngày để khởi tạo đồng bộ
        $sessionsOfTheDay = $this->sessionModel->getSessionsForClassOnDate($currentSession['class_id'], $currentSession['date']);
        if (empty($sessionsOfTheDay)) {
            return 0;
        }

        $attendanceData = [];
        $academicData = [];

        foreach ($sessionsOfTheDay as $session) {
            foreach ($studentIds as $studentId) {
                // Tạo bản ghi điểm danh
                $attendanceData[] = [
                    'student_id' => $studentId,
                    'session_id' => $session['id'],
                    'attendance' => 'holding', // Trạng thái mặc định
                    'user_id' => $userId
                ];
                // Tạo bản ghi kết quả học tập
                $academicData[] = [
                    'student_id' => $studentId,
                    'session_id' => $session['id'],
                    'created_by_id' => $userId,
                    'updated_by_id' => $userId,
                ];
            }
        }
        
        // Thêm hàng loạt để tối ưu hiệu suất
        if (!empty($attendanceData)) {
            $this->attendanceModel->createMultiple($attendanceData);
        }
        if (!empty($academicData)) {
            $this->academicResultModel->createMultiple($academicData);
        }

        return count($studentIds);
    }
    
    /**
     * Cập nhật một trường dữ liệu duy nhất khi người dùng thay đổi trên giao diện (auto-save).
     *
     * @param int $sessionId
     * @param int $studentId
     * @param int $userId
     * @param string $field Tên trường cần cập nhật
     * @param mixed $value Giá trị mới
     * @return bool Thành công hay thất bại.
     */
    public function updateAttendanceField(int $sessionId, int $studentId, int $userId, string $field, $value): bool
    {
        $attendanceFields = ['attendance'];
        $academicResultFields = ['btvn_max', 'btvn_complete', 'btvn_score', 'btvn_comment', 'score', 'comment'];

        $where = ['session_id' => $sessionId, 'student_id' => $studentId];

        if (in_array($field, $attendanceFields)) {
            // Cập nhật bảng điểm danh
            $data = ['attendance' => $value, 'user_id' => $userId];
            return $this->attendanceModel->update($where, $data);
        }
        
        if (in_array($field, $academicResultFields)) {
            // Cập nhật bảng kết quả học tập
            $data = [$field => $value, 'updated_by_id' => $userId];
            // Sử dụng updateOrCreate để đảm bảo bản ghi được tạo nếu chưa có
            return $this->academicResultModel->updateOrCreate($where, $data);
        }

        return false; // Tên trường không hợp lệ
    }
}