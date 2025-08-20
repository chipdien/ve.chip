<?php
namespace App\Models;

class SessionModel extends BaseModel {
    protected $table = 'edu_sessions';

    /**
     * Tìm một ca học theo ID.
     *
     * @param int $sessionId
     * @param array $columns
     * @return array|null
     */
    public function find(int $sessionId, array $columns = ['class_id', 'date']): ?array {
        return $this->db->get($this->table, $columns, ['id' => $sessionId]);
    }

    /**
     * Lấy tất cả các ca học của một lớp trong một ngày cụ thể.
     *
     * @param int $classId
     * @param string $date (định dạng Y-m-d)
     * @return array
     */
    public function getSessionsForClassOnDate(int $classId, string $date): array {
        return $this->db->select($this->table, [
            '[>]edu_teachers' => ['teacher_id' => 'id']
        ], [
            'edu_sessions.id',
            'edu_sessions.date',
            'edu_sessions.content', 
            'edu_sessions.exercice',
            'edu_sessions.session_type',
            'edu_teachers.name(teacher_name)'
        ], [
            'edu_sessions.class_id' => $classId,
            'edu_sessions.date' => $date,
            'edu_sessions.deleted_at' => null, // Chỉ lấy các ca chưa bị xóa
            'ORDER' => ['edu_sessions.from' => 'ASC']
        ]) ?: [];
    }
    
    /**
     * Lấy class_id từ một session_id.
     *
     * @param int $sessionId
     * @return int|null
     */
    public function getClassIdForSession(int $sessionId): ?int {
        return $this->db->get($this->table, 'class_id', ['id' => $sessionId]);
    }

    /**
     * Lấy lịch dạy của một giáo viên trong một ngày cụ thể.
     *
     * @param int $teacherId ID của giáo viên.
     * @param string $date Ngày cần lấy lịch (định dạng Y-m-d).
     * @return array Danh sách các ca học.
     */
    public function getScheduleForTeacherOnDate(int $teacherId, string $date): array
    {
        return $this->db->select($this->table, [
            '[>]edu_classes' => ['class_id' => 'id'],
            '[>]centers' => ['center_id' => 'id']
        ], [
            'edu_sessions.id(session_id)',
            'edu_sessions.from',
            'edu_sessions.to',
            'edu_classes.code(class_code)',
            'edu_sessions.content(subject)',
            'edu_sessions.room_id(room)', // Giả sử tên phòng học được lưu trong bảng edu_sessions
            'centers.name(center_name)'
        ], [
            'edu_sessions.teacher_id' => $teacherId,
            'edu_sessions.date' => $date,
            'edu_sessions.deleted_at' => null, // Chỉ lấy các ca chưa bị xóa
            'ORDER' => ['edu_sessions.from' => 'ASC']
        ]) ?: [];
    }



    public function findWithClassDetails(int $sessionId): ?array
    {
        return $this->db->get($this->table, [
            '[>]edu_classes' => ['class_id' => 'id']
        ], [
            'edu_sessions.id',
            'edu_sessions.content',
            'edu_sessions.content_files',
            'edu_sessions.exercice',
            'edu_sessions.exercice_files',
            'edu_sessions.from',
            'edu_sessions.to',
            'edu_sessions.date',
            'edu_sessions.status',
            'edu_sessions.type',
            'edu_classes.id(class_id)',
            'edu_classes.code(class_code)',
            'edu_classes.name(class_name)',
        ], [
            'edu_sessions.id' => $sessionId
        ]);
    }
}
