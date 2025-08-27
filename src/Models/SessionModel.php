<?php
namespace App\Models;

use Medoo\Medoo;

class SessionModel extends BaseModel {
    protected $table = 'edu_sessions';

    /**
     * Tìm một ca học theo ID.
     *
     * @param int $sessionId
     * @param array $columns
     * @return array|null
     */
    // public function find(int $sessionId, array $columns = ['class_id', 'date']): ?array {
    //     return $this->db->get($this->table, $columns, ['id' => $sessionId]);
    // }

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
            'edu_sessions.date',
            'edu_sessions.from',
            'edu_sessions.to',
            'edu_classes.code(class_code)',
            'edu_sessions.content(subject)',
            'edu_sessions.room_id(room)', // Giả sử tên phòng học được lưu trong bảng edu_sessions
            'centers.name(center_name)',
            'centers.code(center_code)',
        ], [
            'edu_sessions.teacher_id' => $teacherId,
            'edu_sessions.date' => $date,
            'edu_sessions.deleted_at' => null, // Chỉ lấy các ca chưa bị xóa
            'ORDER' => ['edu_sessions.date' => 'ASC', 'edu_sessions.from' => 'ASC']
        ]) ?: [];
    }

    /**
     * Lấy lịch dạy của một giáo viên trong một ngày cụ thể.
     *
     * @param int $teacherId ID của giáo viên.
     * @param string $date Ngày cần lấy lịch (định dạng Y-m-d).
     * @return array Danh sách các ca học.
     */
    public function getScheduleForTeacher(int $teacherId): array
    {
        return $this->db->select($this->table, [
            '[>]edu_classes' => ['class_id' => 'id'],
            '[>]centers' => ['center_id' => 'id'],
            // '[>]rooms' => ['room_id' => 'id'], 
        ], [
            'edu_sessions.id(session_id)',
            'edu_sessions.date',
            'edu_sessions.from',
            'edu_sessions.to',
            'edu_classes.code(class_code)',
            'edu_sessions.content(subject)',
            'edu_sessions.room_id(room)', // Giả sử tên phòng học được lưu trong bảng edu_sessions
            'centers.name(center_name)',
            'centers.code(center_code)',
        ], [
            'edu_sessions.teacher_id' => $teacherId,
            'edu_sessions.date[<>]' => [date('Y-m-d', strtotime("-3 days")), date('Y-m-d', strtotime("+6 days"))],
            'edu_sessions.deleted_at' => null, // Chỉ lấy các ca chưa bị xóa
            'ORDER' => ['edu_sessions.date' => 'ASC', 'edu_sessions.from' => 'ASC']
        ]) ?: [];
    }

    public function getSessionDetail(int $sessionId): ?array
    {
        return $this->db->get($this->table, [
            '[>]edu_classes' => ['class_id' => 'id'],
            '[>]centers' => ['center_id' => 'id'],
        ], [
            'edu_sessions.id(session_id)',
            'edu_sessions.date',
            'edu_sessions.from',
            'edu_sessions.to',
            'edu_sessions.content',
            'edu_sessions.content_files',
            'edu_sessions.exercice',
            'edu_sessions.exercice_files',
            'edu_sessions.type',
            'edu_sessions.teacher_id',

            'edu_classes.id(class_id)',
            'edu_classes.name(class_name)',
            'edu_classes.code(class_code)',
            'centers.name(center_name)',
            'centers.code(center_code)',

        ], [
            'edu_sessions.id' => $sessionId
        ]);
    }

    public function getSessions(array $filters = [], int $page = 1, int $limit = 10): array 
    {
        $where = [];
        $orderBy = [];

        if ($filters) {
            if (isset($filters['teacher_id'])) {
                $where['s.teacher_id'] = $filters['teacher_id'];
            }
            if (isset($filters['type'])) {
                if ($filters['type'] == 'past') {
                    $where["s.date[<=]"] = Medoo::raw('NOW()');
                    $orderBy["s.date"] =  "DESC";
                } else { // upcoming
                    $where["s.date[>]"] = Medoo::raw('NOW()');
                    $orderBy["s.date"] = "ASC";
                }
            }
            if (isset($filters['class_id'])) {
                $where['s.class_id'] = $filters['class_id'];
            }
            if (isset($filters['center_id'])) {
                $where['c.center_id'] = $filters['center_id'];
            }
            if (isset($filters['date'])) {
                $where['s.date'] = $filters['date'];
            }
        }
        $orderBy["s.from"] = "ASC";
        $offset = ($page - 1) * $limit;

        // 2. Đếm tổng số bản ghi thỏa mãn điều kiện (không có LIMIT)
        $total = $this->db->count("edu_sessions (s)", [
            "[>]edu_classes (c)" => ["class_id" => "id"]
        ], "s.id", $where);

        // 3. Thêm điều kiện sắp xếp và phân trang vào mệnh đề WHERE
        $where["ORDER"] = $orderBy;
        $where["LIMIT"] = [$offset, $limit];

        // 4. Lấy dữ liệu chi tiết
        $items = $this->db->select("edu_sessions (s)", [
            // JOIN với bảng edu_classes, đặt alias là 'c'
            "[>]edu_classes (c)" => ["class_id" => "id"]
        ], [
            // Chọn các cột và đặt alias
            's.id',
            's.content',
            's.content_files',
            's.exercice',
            's.exercice_files',
            's.from',
            's.to',
            's.date',
            's.status',
            's.type',
            'c.id(class_id)',
            'c.code(class_code)',
            'c.name(class_name)',
        ], $where);

        // 5. Trả về kết quả theo cấu trúc chuẩn
        return [
            'items' => $items,
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit,
            'hasMore' => ($page * $limit) < $total
        ];
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
