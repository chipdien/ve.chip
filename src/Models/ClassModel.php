<?php
namespace App\Models;

class ClassModel extends BaseModel {
    protected $table = 'edu_classes';

    /**
     * Tìm thông tin một lớp học theo ID.
     *
     * @param int $classId
     * @param array $columns
     * @return array|null
     */
    // public function find(int $classId, array $columns = ['id', 'code']): ?array {
    //     return $this->db->get($this->table, $columns, ['id' => $classId]);
    // }

    /**
     * Tìm thông tin lớp học và JOIN với thông tin cơ sở.
     *
     * @param int $classId
     * @return array|null
     */
    public function findWithCenterDetails(int $classId): ?array {
        $data = $this->db->get($this->table, [
            '[>]centers' => ['center_id' => 'id']
        ], [
            'edu_classes.id',
            'edu_classes.code(class_code)',
            'edu_classes.year',
            'edu_classes.friendly_class_teachers',
            'centers.name(center_name)',
            'centers.address(center_address)',
            'centers.phone(center_phone)',
            'centers.email(center_email)',
        ], ['edu_classes.id' => $classId]);
        
        return $data ?: null;
    }

    /**
     * Lấy danh sách các lớp học đang hoạt động mà một giáo viên dạy.
     *
     * @param int $teacherId
     * @return array
     */
    public function getActiveClassesForTeacher(int $teacherId): array
    {
        // Lấy các class_id duy nhất từ các ca học mà giáo viên này dạy
        return $this->db->select('edu_sessions', [
            '[>]edu_classes' => ['class_id' => 'id'],
            // '[>]centers' => ['center_id' => 'id'],
        ], [
            'edu_classes.id',
            'edu_classes.code',
            'edu_classes.name',
            'edu_classes.year',
            'edu_classes.friendly_class_teachers',
            'edu_classes.friendly_class_schedule (schedule)',
            'edu_classes.center_id',
            // 'centers.name(center_name)',
            // 'centers.code(center_code)',
            
        ], [
            'edu_sessions.teacher_id' => $teacherId,
            'edu_sessions.deleted_at' => null,
            'edu_classes.year' => 2025,
            'GROUP' => 'edu_sessions.class_id',
            'ORDER' => ['edu_classes.center_id' => 'ASC', 'edu_classes.code' => 'ASC'],
        ])?:[];


    }

    /**
     * Lấy danh sách các lớp học đang hoạt động mà một giáo viên dạy.
     *
     * @param int $teacherId
     * @return array
     */
    public function getClassesInfoBy(array $where): array
    {
        if (!$where) {
            return [];
        }
        // $where['edu_classes.year'] = 2025;
        $where['ORDER'] = ['edu_classes.center_id' => 'ASC', 'edu_classes.code' => 'ASC'];

        // Lấy các class_id duy nhất từ các ca học mà giáo viên này dạy
        return $this->db->select('edu_classes', [
            '[>]centers' => ['center_id' => 'id'],
        ], [
            'edu_classes.id',
            'edu_classes.code',
            'edu_classes.name',
            'edu_classes.active',
            'edu_classes.open_date',
            'edu_classes.note',
            'edu_classes.fee',
            'edu_classes.center_id',
            'edu_classes.year',
            'edu_classes.fee_per_hour',
            'edu_classes.pic_admin',
            'edu_classes.pic_assistant',
            'edu_classes.friendly_class_teachers',
            'edu_classes.friendly_class_schedule (schedule)',

            'centers.name(center_name)',
            'centers.code(center_code)',
        ], $where)?:[];
    }

    public function getClassInfoBy(array $where): array
    {
        if (!$where) {
            return [];
        }
        // $where['edu_classes.year'] = 2025;
        $where['ORDER'] = ['edu_classes.center_id' => 'ASC', 'edu_classes.code' => 'ASC'];

        // Lấy các class_id duy nhất từ các ca học mà giáo viên này dạy
        return $this->db->get('edu_classes', [
            '[>]centers' => ['center_id' => 'id'],
        ], [
            'edu_classes.id',
            'edu_classes.code',
            'edu_classes.name',
            'edu_classes.active',
            'edu_classes.open_date',
            'edu_classes.note',
            'edu_classes.fee',
            'edu_classes.center_id',
            'edu_classes.year',
            'edu_classes.fee_per_hour',
            'edu_classes.pic_admin',
            'edu_classes.pic_assistant',
            'edu_classes.friendly_class_teachers',
            'edu_classes.friendly_class_schedule (schedule)',

            'centers.name(center_name)',
            'centers.code(center_code)',
        ], $where)?:[];
    }

}
