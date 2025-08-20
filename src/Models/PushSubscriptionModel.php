<?php
// File: app/Models/PushSubscriptionModel.php
namespace App\Models;

class PushSubscriptionModel extends BaseModel
{
    protected $table = 'push_subscriptions';

    public function save(int $userId, array $sub): bool
    {
        // Kiểm tra xem endpoint đã tồn tại chưa để tránh trùng lặp
        $exists = $this->db->has($this->table, ['endpoint' => $sub['endpoint']]);

        if ($exists) {
            // Có thể cập nhật lại user_id nếu cần
            $stmt = $this->db->update($this->table, ['user_id' => $userId], ['endpoint' => $sub['endpoint']]);
        } else {
            $stmt = $this->db->insert($this->table, [
                'user_id' => $userId,
                'endpoint' => $sub['endpoint'],
                'p256dh' => $sub['keys']['p256dh'],
                'auth' => $sub['keys']['auth'],
            ]);
        }
        return $stmt->rowCount() > 0;
    }

    public function findByUserId(int $userId): array
    {
        return $this->db->select($this->table, '*', ['user_id' => $userId]);
    }
    
    public function deleteByEndpoint(string $endpoint): bool
    {
        $stmt = $this->db->delete($this->table, ['endpoint' => $endpoint]);
        return $stmt->rowCount() > 0;
    }
}