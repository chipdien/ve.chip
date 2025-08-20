<?php
namespace App\Models;

class ParentModel extends BaseModel {
    protected $table = 'parents';

    /**
     * Cập nhật thông tin chi tiết cho một phụ huynh.
     *
     * @param int $parentId
     * @param array $data Dữ liệu cần cập nhật (ví dụ: ['name' => 'New Name', 'phone' => '...']).
     * @return bool
     */
    public function updateDetails(int $parentId, array $data): bool {
        if (empty($data)) {
            return true; // Không có gì để cập nhật
        }
        $statement = $this->db->update($this->table, $data, ['id' => $parentId]);
        return $statement->rowCount() > 0;
    }
}
