<?php

namespace App\Services;

use App\Models\EduTestModel;

class TestManagementService {
    /**
     * @var EduTestModel
     */
    private $eduTestModel;

    public function __construct() {
        // Khởi tạo các model cần thiết.
        // Giả sử bạn có cơ chế autoload hoặc đã require các file model.
        $this->eduTestModel = new EduTestModel();
    }

    /**
     * Lấy danh sách bài kiểm tra cho một lớp.
     * @param int|string $classId
     * @return array
     */
    public function getTestsForClass($classId) {
        if (empty($classId)) {
            return [];
        }
        return $this->eduTestModel->findAllByClassId($classId);
    }

    /**
     * Lấy thông tin chi tiết một bài kiểm tra.
     * @param int|string $id
     * @return array|null
     */
    public function getTestById($id) {
        if (empty($id)) {
            return null;
        }
        return $this->eduTestModel->find($id);
    }

    /**
     * Xử lý logic tạo bài kiểm tra mới.
     * @param array $data Dữ liệu từ form.
     * @return array Mảng kết quả ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function createTest(array $data) {
        // 1. Validate dữ liệu đầu vào
        if (empty(trim($data['name']))) {
            return ['success' => false, 'message' => 'Tên bài kiểm tra không được để trống.'];
        }
        if (empty($data['class_id'])) {
            return ['success' => false, 'message' => 'Lớp học không hợp lệ.'];
        }
        if (empty($data['test_date'])) {
            return ['success' => false, 'message' => 'Ngày kiểm tra không được để trống.'];
        }

        // 2. Gọi Model để lưu vào CSDL
        $newTestId = $this->eduTestModel->create($data);

        // 3. Trả về kết quả
        if ($newTestId) {
            return ['success' => true, 'message' => 'Tạo bài kiểm tra thành công!', 'data' => ['id' => $newTestId]];
        }

        return ['success' => false, 'message' => 'Đã có lỗi xảy ra khi tạo bài kiểm tra.'];
    }

    /**
     * Xử lý logic cập nhật bài kiểm tra.
     * @param int|string $id ID bài kiểm tra.
     * @param array $data Dữ liệu từ form.
     * @return array Mảng kết quả ['success' => bool, 'message' => string]
     */
    public function updateTest($id, array $data) {
        // 1. Validate
        if (empty($id)) {
            return ['success' => false, 'message' => 'ID bài kiểm tra không hợp lệ.'];
        }
        if (empty(trim($data['name']))) {
            return ['success' => false, 'message' => 'Tên bài kiểm tra không được để trống.'];
        }

        // 2. Gọi Model để cập nhật
        $affectedRows = $this->eduTestModel->update($id, $data);

        // 3. Trả về kết quả
        if ($affectedRows > 0) {
            return ['success' => true, 'message' => 'Cập nhật bài kiểm tra thành công!'];
        }

        return ['success' => false, 'message' => 'Không có gì thay đổi hoặc đã có lỗi xảy ra.'];
    }

    /**
     * Xử lý logic xóa bài kiểm tra.
     * @param int|string $id
     * @return array Mảng kết quả ['success' => bool, 'message' => string]
     */
    public function deleteTest($id) {
        if (empty($id)) {
            return ['success' => false, 'message' => 'ID bài kiểm tra không hợp lệ.'];
        }

        // Thêm các bước kiểm tra quyền hạn hoặc các ràng buộc khác ở đây nếu cần

        $affectedRows = $this->eduTestModel->delete($id);

        if ($affectedRows > 0) {
            return ['success' => true, 'message' => 'Đã xóa bài kiểm tra thành công!'];
        }

        return ['success' => false, 'message' => 'Không tìm thấy bài kiểm tra để xóa hoặc đã có lỗi xảy ra.'];
    }
}