<?php
// File: App/Models/EduTestResultModel.php

namespace App\Models;

/**
 * Class EduTestResultModel
 * Model để tương tác với bảng kết quả bài kiểm tra (edu_test_result).
 */
class EduTestResultModel extends BaseModel
{
    /**
     * Tên bảng trong cơ sở dữ liệu.
     * @var string
     */
    protected $table = 'edu_test_results';

    /**
     * Lấy tất cả kết quả bài kiểm tra của một lớp học cụ thể.
     *
     * Hàm này sử dụng phương thức `findBy` được kế thừa từ `BaseModel`
     * để truy vấn tất cả các bản ghi có `class_id` khớp với giá trị được cung cấp.
     *
     * @param int $classId ID của lớp học.
     * @return array Trả về một mảng chứa tất cả các kết quả, hoặc một mảng rỗng nếu không tìm thấy.
     */
    public function getByClassId(int $classId): array
    {
        // Sử dụng phương thức findBy từ lớp cha để tìm kiếm
        // với điều kiện 'class_id' bằng với $classId.
        return $this->findBy(['class_id' => $classId]);
    }

    /**
     * Cập nhật một trường (field) cụ thể của một kết quả bài kiểm tra.
     *
     * Hàm này cho phép cập nhật một cột duy nhất một cách linh hoạt
     * mà không cần phải truyền toàn bộ dữ liệu của bản ghi.
     *
     * @param int $resultId ID của kết quả bài kiểm tra cần cập nhật.
     * @param string $fieldName Tên của trường (cột) cần cập nhật.
     * @param mixed $value Giá trị mới cho trường đó.
     * @return int Số lượng dòng bị ảnh hưởng bởi câu lệnh update.
     * Thường là 1 nếu cập nhật thành công, 0 nếu không có gì thay đổi hoặc không tìm thấy ID.
     */
    public function updateField(int $resultId, string $fieldName, $value): int
    {
        // Dữ liệu cần cập nhật được đặt trong một mảng có key là tên trường
        // và value là giá trị mới.
        $dataToUpdate = [
            $fieldName => $value
        ];

        // Sử dụng phương thức update từ lớp cha để cập nhật bản ghi có ID tương ứng.
        return $this->update($resultId, $dataToUpdate);
    }

    /**
     * [MỚI] Lấy kết quả của một bài test, kèm thông tin học sinh.
     *
     * Hàm này sẽ nối bảng `edu_test_result` với bảng `students` để lấy
     * thông tin chi tiết của học sinh (họ tên, ngày sinh) cùng với kết quả bài làm.
     * Kết quả sẽ được sắp xếp theo tên (first_name) của học sinh.
     *
     * @param int $testId ID của bài kiểm tra (test_id).
     * @return array Mảng chứa kết quả của các học sinh.
     */
    public function getResultsByTestIdWithStudentInfo(int $testId): array
    {
        return $this->db->select($this->table, [
                '[><]students' => ['student_id' => 'id']
            ], [
                'edu_test_results.id', 
                'edu_test_results.test_id', 
                'edu_test_results.student_id', 
                'edu_test_results.score', 
                'edu_test_results.comments', 
                // Và chỉ lấy các cột cần thiết từ bảng students
                'students.full_name',
                'students.dob'
            ], [
                'edu_test_results.test_id' => $testId,
                'ORDER' => ['students.first_name' => 'ASC']
            ]
        ) ?: [];
    }
}
