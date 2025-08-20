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
        $this->db = Database::getInstance();
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
}
