<?php
namespace App\Models;

use Medoo\Medoo;

/**
 * Class BaseModel 
 * Lớp cơ sở trừu tượng cho tất cả các Model.
 */
abstract class BaseModel {
    /**
     * @var Medoo Đối tượng kết nối CSDL Medoo.
     */
    protected $db;

    /**
     * Bảng CSDL chính mà Model này quản lý.
     * @var string
     */
    protected $table;

    /**
     * Hàm khởi tạo, tự động lấy kết nối CSDL.
     */
    public function __construct() {
        // if ($this->table === null) {
        //     throw new Exception("Lớp " . get_class($this) . " phải định nghĩa thuộc tính \$table.");
        // }
        $this->db = Database::getInstance();
    }

    
    /**
     * Lấy tất cả các bản ghi từ bảng.
     * @return array
    */
    public function all() {
        return $this->db->select($this->table, '*');
    }
    
    /**
     * Tìm một bản ghi bằng ID (khóa chính).
     * @param int|string $id
     * @return array|null
     */
    public function find($id) {
        $result = $this->db->get($this->table, '*', ['id' => $id]);
        return $result ?: null;
    }

    /**
     * Tìm một bản ghi dựa trên điều kiện.
     *
     * @param array $where Điều kiện truy vấn.
     * @param array|string $columns Các cột cần lấy.
     * @return array|null
     */
    public function findOneBy(array $where, $columns = '*') : ?array
    {
        $result = $this->db->get($this->table, $columns, $where);
        return $result ?: null;
    }
    
    /**
     * (MỚI) Tìm TẤT CẢ các bản ghi theo nhiều điều kiện.
     * @param array $where Điều kiện tìm kiếm, cú pháp theo Medoo (có thể gồm ORDER, LIMIT...).
     * @return array
     */
    public function findBy(array $where) {
        return $this->db->select($this->table, '*', $where);
    }

    /**
     * Tạo một bài kiểm tra mới.
     * @param array $data Mảng dữ liệu của bài kiểm tra (ví dụ: ['name' => 'Test 1', 'class_id' => 5, ...]).
     * @return string|null ID của bài kiểm tra vừa được tạo hoặc null nếu thất bại.
     */
    public function create(array $data) {
        $this->db->insert($this->table, $data);
        $lastId = $this->db->id();
        return $lastId > 0 ? $lastId : null;
    }

    /**
     * Cập nhật thông tin một bài kiểm tra.
     * @param int|string $id ID của bài kiểm tra cần cập nhật.
     * @param array $data Mảng dữ liệu cần cập nhật.
     * @return int Số dòng bị ảnh hưởng.
     */
    public function update($id, array $data) {
        $statement = $this->db->update($this->table, $data, ['id' => $id]);
        return $statement->rowCount();
    }

    /**
     * (MỚI) Cập nhật các bản ghi theo nhiều điều kiện.
     * @param array $where Điều kiện để xác định các dòng cần cập nhật.
     * @param array $data Dữ liệu mới.
     * @return int Số dòng bị ảnh hưởng.
     */
    public function updateBy(array $where, array $data) {
        $statement = $this->db->update($this->table, $data, $where);
        return $statement->rowCount();
    }

    /**
     * Xóa một bản ghi bằng ID.
     * @param int|string $id
     * @return int Số dòng bị ảnh hưởng.
     */
    public function delete($id) {
        $statement = $this->db->delete($this->table, ['id' => $id]);
        return $statement->rowCount();
    }

    /**
     * (MỚI) Xóa các bản ghi theo nhiều điều kiện.
     * @param array $where Điều kiện để xác định các dòng cần xóa.
     * @return int Số dòng bị ảnh hưởng.
     */
    public function deleteBy(array $where) {
        $statement = $this->db->delete($this->table, $where);
        return $statement->rowCount();
    }


}
