<?php
namespace App\Models;
use Medoo\Medoo;

class AttendanceModel extends BaseModel {
    protected $table = 'edu_student_session_attendance';

    /**
     * Lấy các ID học viên (duy nhất) đã được khởi tạo trong một danh sách ca học.
     *
     * @param array $sessionIds
     * @return array
     */
    public function getUniqueStudentIdsForSessions(array $sessionIds): array {
        if (empty($sessionIds)) {
            return [];
        }
        $studentIds = $this->db->select($this->table, 'student_id', [
            'session_id' => $sessionIds
        ]);
        return array_unique($studentIds);
    }
    
    /**
     * Lấy ID của các học viên đã có bản ghi trong một ca học cụ thể.
     *
     * @param int $sessionId
     * @return array
     */
    public function getStudentIdsInSession(int $sessionId): array {
        return $this->db->select($this->table, 'student_id', ['session_id' => $sessionId]);
    }

    /**
     * Lấy tất cả bản ghi điểm danh của một nhóm học viên trong một nhóm ca học.
     *
     * @param array $studentIds
     * @param array $sessionIds
     * @return array
     */
    public function getRecordsForStudentsInSessions(array $studentIds, array $sessionIds): array {
        if (empty($studentIds) || empty($sessionIds)) {
            return [];
        }
        return $this->db->select($this->table, '*', [
            'student_id' => $studentIds,
            'session_id' => $sessionIds
        ]);
    }

    /**
     * Tạo nhiều bản ghi điểm danh cùng lúc.
     *
     * @param array $data
     * @return int|null Số dòng bị ảnh hưởng.
     */
    public function createMultiple(array $data): ?int {
        if (empty($data)) {
            return 0;
        }
        $statement = $this->db->insert($this->table, $data);
        return $statement->rowCount();
    }

    /**
     * Cập nhật một bản ghi điểm danh.
     *
     * @param array $where
     * @param array $data
     * @return bool
     */
    public function update(array $where, array $data): bool {
        $statement = $this->db->update($this->table, $data, $where);
        return $statement->rowCount() > 0;
    }

    /**
     * Lấy dữ liệu điểm danh cho một danh sách các ca học.
     *
     * @param array $sessionIds Mảng các ID của ca học.
     * @return array
     */
    public function getAttendanceDataForSessions(array $sessionIds): array
    {
        if (empty($sessionIds)) {
            return [];
        }

        $attendanceData = $this->db->select($this->table, '*', [
            'session_id' => $sessionIds
        ]);
        foreach ($attendanceData as $attendance) {
            $attendanceRaws[$attendance['session_id'].'-'.$attendance['student_id']] = $attendance;
        }
        return $attendanceRaws;
    }

    /**
     * Đếm số buổi nghỉ (vắng có phép và không phép) của một nhóm học viên
     * trong một kỳ học (được định nghĩa là 2 tháng gần nhất).
     *
     * @param array $studentIds
     * @param int $classId
     * @param string $currentDate Ngày của buổi học hiện tại (Y-m-d)
     * @return array Mảng kết hợp [student_id => count]
     */
    public function countAbsencesInTermForStudents(array $studentIds, int $classId, string $currentDate): array {
        if (empty($studentIds)) {
            return [];
        }

        $term_start_date = date('Y-m-01', strtotime($currentDate . ' -1 month'));
        $term_end_date = date('Y-m-t', strtotime($currentDate));

        $sessionIdsInTerm = $this->db->select('edu_sessions', 'id', [
            'class_id' => $classId,
            'date[<>]' => [$term_start_date, $term_end_date]
        ]);

        if (empty($sessionIdsInTerm)) {
            return [];
        }

        $absenceCountsRaw = $this->db->select($this->table, [
            'student_id',
            'count' => Medoo::raw('COUNT(<id>)')
        ], [
            'student_id' => $studentIds,
            'session_id' => $sessionIdsInTerm,
            'attendance' => ['absence', 'n_absence'],
            'GROUP' => 'student_id'
        ]);

        return array_column($absenceCountsRaw, 'count', 'student_id');
    }


    /**
     * Tính tỷ lệ chuyên cần (%) cho một giáo viên trong một khoảng thời gian.
     *
     * @param int $teacherId
     * @param string $startDate (Y-m-d)
     * @param string $endDate (Y-m-d)
     * @return float
     */
    public function getAttendanceRateForTeacher(int $teacherId, string $startDate, string $endDate): float
    {
        // Lấy các ca học của giáo viên trong khoảng thời gian
        $sessionIds = $this->db->select('edu_sessions', 'id', [
            'teacher_id' => $teacherId,
            'date[<>]' => [$startDate, $endDate],
            'deleted_at' => null
        ]);

        if (empty($sessionIds)) {
            return 100.0; // Nếu không có ca nào, tỷ lệ là 100%
        }

        // Đếm tổng số lượt điểm danh
        $totalRecords = $this->db->count($this->table, ['session_id' => $sessionIds]);
        if ($totalRecords == 0) {
            return 100.0; // Nếu chưa điểm danh, coi như 100%
        }

        // Đếm số lượt có mặt
        $presentRecords = $this->db->count($this->table, [
            'session_id' => $sessionIds,
            'attendance' => 'present'
        ]);

        return ($presentRecords / $totalRecords) * 100;
    }


    public function getAttendanceRatesForClasses(array $classIds): array
    {
        if (empty($classIds)) return [];

        $rates = $this->db->select('edu_student_session_attendance', [
            '[>]edu_sessions' => ['session_id' => 'id']
        ], [
            'edu_sessions.class_id',
            'rate' => Medoo::raw(" (SUM(CASE WHEN <edu_student_session_attendance.attendance> = 'present' THEN 1 ELSE 0 END) / COUNT(<edu_student_session_attendance.id>)) * 100 "),
        ], [
            'edu_sessions.class_id' => $classIds,
            'GROUP' => 'edu_sessions.class_id'
        ]) ?: [];

        // $rates = $this->db->query(
        //     "# SELECT <edu_sessions.class_id>, (SUM(CASE WHEN <edu_student_session_attendance.attendance> = 'present' THEN 1 ELSE 0 END) / COUNT(<edu_student_session_attendance.id>)) * 100 AS rate
        //      # FROM <edu_student_session_attendance> 
        //      # JOIN <edu_sessions> ON <edu_student_session_attendance.session_id> = <edu_sessions.id>
        //      WHERE <edu_sessions.class_id> IN (:classIds)
        //      GROUP BY <edu_sessions.class_id>",
        //     [':classIds' => $classIds]
        // )->fetchAll(); // \PDO::FETCH_ASSOC

        return array_column($rates, 'rate', 'class_id');
    }
}
