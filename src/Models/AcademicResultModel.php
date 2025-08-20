<?php
namespace App\Models;

use Medoo\Medoo;

class AcademicResultModel extends BaseModel {
    protected $table = 'edu_student_academic_result';

    /**
     * Lấy tất cả bản ghi kết quả học tập của một nhóm học viên trong một nhóm ca học.
     *
     * @param array $studentIds
     * @param array $sessionIds
     * @return array
     */
    public function getResultsForStudentsInSessions(array $studentIds, array $sessionIds): array {
        if (empty($studentIds) || empty($sessionIds)) {
            return [];
        }
        return $this->db->select($this->table, '*', [
            'student_id' => $studentIds,
            'session_id' => $sessionIds
        ]);
    }

    /**
     * Tạo nhiều bản ghi kết quả học tập cùng lúc.
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
     * Cập nhật một bản ghi nếu nó tồn tại, nếu không thì tạo mới.
     *
     * @param array $where Điều kiện để tìm bản ghi (ví dụ: ['student_id' => 1, 'session_id' => 2])
     * @param array $data Dữ liệu để cập nhật hoặc tạo mới.
     * @return bool
     */
    public function updateOrCreate(array $where, array $data): bool {
        if ($this->db->has($this->table, $where)) {
            // Nếu tồn tại, cập nhật
            $statement = $this->db->update($this->table, $data, $where);
        } else {
            // Nếu không, tạo mới (kết hợp điều kiện và dữ liệu)
            $insertData = array_merge($where, $data);
            $statement = $this->db->insert($this->table, $insertData);
        }
        return $statement->rowCount() > 0;
    }

    /**
     * Lấy các chỉ số trung bình về BTVN cho tất cả học viên trong một lớp
     * trong một khoảng thời gian nhất định.
     *
     * @param int $classId
     * @param string $startDate (Y-m-d)
     * @param string $endDate (Y-m-d)
     * @return array
     */
    public function getAverageMetricsForClassInDateRange(int $classId, string $startDate, string $endDate): array
    {
        // Lấy danh sách các ca học của lớp trong khoảng thời gian
        $sessionIds = $this->db->select('edu_sessions', 'id', [
            'class_id' => $classId,
            'date[<>]' => [$startDate, $endDate]
        ]);

        if (empty($sessionIds)) {
            return [];
        }

        // Thực hiện truy vấn tổng hợp để tính AVG
        // Dùng CASE WHEN ... THEN ... ELSE NULL END để loại bỏ các giá trị -1
        // khỏi phép tính AVG, vì AVG() trong SQL sẽ bỏ qua các giá trị NULL.
        return $this->db->select($this->table, [
            '[>]students' => ['student_id' => 'id']
        ], [
            'students.id(student_id)',
            'students.full_name',
            'avg_completion_percent' => Medoo::raw('AVG(CASE WHEN <btvn_complete_percent> >= 0 THEN <btvn_complete_percent> ELSE NULL END)'),
            'avg_score_100' => Medoo::raw('AVG(CASE WHEN <btvn_score_100> >= 0 THEN <btvn_score_100> ELSE NULL END)')
        ], [
            'session_id' => $sessionIds,
            'GROUP' => ['students.id', 'students.full_name'],
            'ORDER' => ['students.first_name' => 'ASC', 'students.last_name' => 'ASC']
        ])?: [];
    }


    public function getAverageScoresForClasses(array $classIds): array
    {
        if (empty($classIds)) return [];

        $scores = $this->db->select('edu_student_academic_result(ar)', [
            '[>]edu_sessions(s)' => ['session_id' => 'id'],
        ], [
            's.class_id',
            'avg_score' => Medoo::raw('AVG(CASE WHEN <ar.btvn_score_100> >= 0 THEN <ar.btvn_score_100> ELSE NULL END)'),
        ], [
            's.class_id' => $classIds,
            'GROUP' => ['s.class_id']
        ])? : [];

        // $scores = $this->db->query(
        //     "SELECT s.class_id, AVG(CASE WHEN ar.btvn_score_100 >= 0 THEN ar.btvn_score_100 ELSE NULL END) AS avg_score
        //      # FROM edu_student_academic_result ar
        //      # JOIN edu_sessions s ON ar.session_id = s.id
        //      # WHERE s.class_id IN (<classIds>)
        //      # GROUP BY s.class_id",
        //     ['<classIds>' => $classIds]
        // )->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_column($scores, 'avg_score', 'class_id');
    }

}
