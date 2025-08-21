<?php
namespace App\Models;

class StudentModel extends BaseModel {
    protected $table = 'students';

    /**
     * Lấy thông tin chi tiết của một học viên.
     *
     * @param int $studentId
     * @return array|null
     */
    public function findDetails(int $studentId): ?array {
        return $this->db->get($this->table, '*', ['id' => $studentId]);
    }

    /**
     * Lấy thông tin chi tiết của một học viên, bao gồm cả thông tin phụ huynh.
     *
     * @param int $studentId
     * @return array|null
     */
    public function findDetailsWithParent(int $studentId): ?array {
        $data = $this->db->get($this->table, [
            '[>]parents' => ['parent_id' => 'id']
        ], [
            'students.id',
            'students.full_name',
            'students.dob',
            'students.avatar',
            'students.aspiration(learning_goal)', // Map ngược lại để frontend không đổi
            'students.credit_balance',
            'parents.name(parent_name)',
            'parents.phone(parent_phone)',
            'parents.email(parent_email)',
        ], [
            'students.id' => $studentId
        ]);
        return $data ?: null;
    }

    /**
     * Lấy danh sách học viên đang 'enroll' trong một lớp cụ thể, có phân trang.
     *
     * @param int $classId
     * @param int $limit Số lượng mục cần lấy
     * @param int $offset Vị trí bắt đầu lấy
     * @return array
     */
    public function getEnrolledStudentsByClassId(int $classId, int $limit, int $offset): array {
        $where = [
            'edu_student_class.class_id' => $classId,
            'edu_student_class.status' => 'enroll',
            'ORDER' => ['students.first_name' => 'ASC', 'students.last_name' => 'ASC'],
            'LIMIT' => [$offset, $limit] // Thêm điều kiện phân trang
        ];
        
        return $this->db->select('edu_student_class', [
            '[>]students' => ['student_id' => 'id'],
            '[>]parents' => ['students.parent_id' => 'id'],
        ], [
            'students.id',
            'students.full_name',
            'students.dob',
            'students.avatar',
            'students.aspiration (learning_goal)',
            'students.credit_balance',
            'parents.fullname (parent_name)',
            'parents.phone (parent_phone)',
            'parents.email (parent_email)',
        ], $where) ?: [];
    }

    /**
     * Đếm tổng số học viên đang 'enroll' trong một lớp.
     *
     * @param int $classId
     * @return int
     */
    public function countEnrolledStudentsByClassId(int $classId): int {
        return $this->db->count('edu_student_class', [
            'class_id' => $classId,
            'status' => 'enroll'
        ]);
    }

    /**
     * Lấy parent_id của một học viên.
     *
     * @param int $studentId
     * @return int|null
     */
    public function getParentId(int $studentId): ?int {
        $parentId = $this->db->get($this->table, 'parent_id', ['id' => $studentId]);
        return $parentId ? (int)$parentId : null;
    }

    /**
     * Cập nhật thông tin chi tiết cho một học viên.
     *
     * @param int $studentId
     * @param array $data Dữ liệu cần cập nhật.
     * @return bool
     */
    public function updateDetails(int $studentId, array $data): bool {
        if (empty($data)) {
            return true; // Không có gì để cập nhật
        }
        $statement = $this->db->update($this->table, $data, ['id' => $studentId]);
        return $statement->rowCount() > 0;
    }

    /**
     * Tìm thông tin nhiều học sinh dựa trên mảng các ID.
     *
     * @param array $studentIds
     * @param array $columns
     * @return array
     */
    public function findByIds(array $studentIds, array $columns = ['id', 'full_name', 'dob', 'credit_balance']): array {
        if (empty($studentIds)) {
            return [];
        }
        return $this->db->select($this->table, $columns, [
            'id' => $studentIds,
            'ORDER' => ['first_name' => 'ASC', 'last_name' => 'ASC']
        ]);
    }

    /**
     * Lấy danh sách học viên đang 'enroll' trong lớp, trừ ra những người đã có trong danh sách.
     *
     * @param int $classId
     * @param array $excludedIds Mảng ID của học viên cần loại trừ.
     * @return array
     */
    public function getEnrolledStudentsNotInList(int $classId, array $excludedIds): array {
        $where = [
            'edu_student_class.class_id' => $classId,
            'edu_student_class.status' => 'enroll',
            'ORDER' => ['students.first_name' => 'ASC', 'students.last_name' => 'ASC']
        ];

        if (!empty($excludedIds)) {
            $where['edu_student_class.student_id[!]'] = $excludedIds;
        }

        return $this->db->select('edu_student_class', [
            '[>]students' => ['student_id' => 'id']
        ], [
            'students.id',
            'students.full_name',
            'students.dob',
        ], $where) ?: [];
    }

    /**
     * Đếm tổng số học viên đang 'enroll' trong một danh sách các lớp.
     *
     * @param array $classIds
     * @return int
     */
    public function countEnrolledStudentsInClasses(array $classIds): int
    {
        if (empty($classIds)) {
            return 0;
        }
        return $this->db->count('edu_student_class', [
            'class_id' => $classIds,
            'status' => 'enroll'
        ]);
    }

    /**
     * Lấy danh sách học viên và trạng thái điểm danh cho một ca học cụ thể.
     */
    public function getStudentsForSession(int $sessionId): array
    {
        $students = $this->db->select('edu_student_session_attendance(att)', [
            '[>]students(s)' => ['att.student_id' => 'id']
        ], [
            's.id',
            's.full_name(name)',
            's.avatar',
            'att.attendance(status)'
        ], [
            'att.session_id' => $sessionId,
            'ORDER' => ['s.first_name' => 'ASC']
        ])?: [];

        // Thêm mảng feedbacks rỗng để Alpine.js khởi tạo
        return array_map(function($student) {
            $student['feedbacks'] = [];
            return $student;
        }, $students);
    }


    public function getStudentCountsForClasses(array $classIds): array
    {
        if (empty($classIds)) return [];

        $counts = $this->db->select('edu_student_class', [
            'class_id',
            'count' => \Medoo\Medoo::raw('COUNT(<student_id>)')
        ], [
            'class_id' => $classIds,
            'status' => 'enroll',
            'GROUP' => 'class_id'
        ]);

        $results = [];
        foreach ($counts as $count) {
            $results[$count['class_id']] = ['current_students' => (int)$count['count']];
        }
        return $results;
    }
    
}
