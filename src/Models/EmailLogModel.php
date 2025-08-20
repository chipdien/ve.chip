<?php
namespace App\Models;

use Medoo\Medoo;

class EmailLogModel extends BaseModel {
    protected $table = 'email_logs';

    /**
     * Ghi một bản ghi log email mới vào CSDL.
     *
     * @param array $logData Dữ liệu log cần ghi.
     * @return bool
     */
    public function create(array $logData): bool
    {
        // Thêm các giá trị mặc định nếu chưa có
        $defaults = [
            'email_uuid' => Medoo::raw('UUID()'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        $dataToInsert = array_merge($defaults, $logData);

        $statement = $this->db->insert($this->table, $dataToInsert);
        return $statement->rowCount() > 0;
    }
}
