<?php
namespace App\Models;

use Medoo\Medoo;

class EduTestModel  extends BaseModel {
    protected $table = 'edu_tests';

    /**
     * Lấy tất cả các bài kiểm tra của một lớp học, sắp xếp theo ngày gần nhất.
     * @param int|string $classId ID của lớp học.
     * @return array Danh sách các bài kiểm tra.
     */
    public function findAllByClassId($classId) {
        return $this->db->select($this->table, '*', [
            'class_id' => $classId,
            'ORDER' => ['test_date' => 'DESC']
        ]);
    }
    
    /**
     * Tìm thông tin chi tiết của một bài kiểm tra, bao gồm cả thông tin lớp học.
     *
     * @param int $testId ID của bài kiểm tra.
     * @return array|null Mảng chứa thông tin nếu tìm thấy, ngược lại là null.
     */
    public function findWithClassDetails(int $testId): ?array
    {
        $data = $this->db->get($this->table, [
            // JOIN với bảng edu_classes với điều kiện edu_tests.class_id = edu_classes.id
            '[>]edu_classes' => ['class_id' => 'id']
        ], [
            // Lấy các cột từ bảng edu_tests
            'edu_tests.id',
            'edu_tests.name',
            'edu_tests.test_date',
            'edu_tests.class_id',
            'edu_tests.subject',
            'edu_tests.max_score',
            'edu_tests.notes',
            
            // Lấy các cột từ bảng edu_classes và đổi tên để tránh trùng lặp
            'edu_classes.code(class_code)',
            'edu_classes.name(class_name)'
        ], [
            // Điều kiện WHERE
            'edu_tests.id' => $testId
        ]);

        return $data ?: null;
    }

    //findWithCenterDetails
    /**
     * Tìm thông tin bài test, join với lớp và trung tâm để hiển thị báo cáo.
     *
     * @param int $testId
     * @return array|null
     */
    public function findWithCenterDetails(int $testId): ?array
    {
        return $this->db->get($this->table, [
            '[>]edu_classes' => ['class_id' => 'id'],
            '[>]centers' => ['edu_classes.center_id' => 'id']
        ], [
            'edu_tests.id',
            'edu_tests.name',
            'edu_tests.test_date',
            'edu_tests.class_id',
            'edu_tests.subject',
            'edu_tests.max_score',
            'edu_tests.notes',
            'edu_classes.code(class_code)',
            'edu_classes.friendly_class_teachers(teachers)', // Giả sử có trường này
            'centers.name(center_name)',
            'centers.address(center_address)',
            'centers.phone(center_phone)',
            'centers.email(center_email)',
        ], [
            'edu_tests.id' => $testId
        ]);
    }
}
