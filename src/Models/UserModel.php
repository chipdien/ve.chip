<?php
namespace App\Models;

class UserModel extends BaseModel {
    protected $table = 'users';

    // public function find(int $userId, array $columns = ['id', 'nickName', 'email'])
    // {
    //     return $this->db->get($this->table, $columns, ['id' => $userId]);
    // }

    /**
     * Tìm một người dùng dựa trên địa chỉ email.
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        // Lấy các trường cần thiết cho việc xác thực và lưu session
        $data = $this->db->get($this->table, [
            'id',
            'nickName',
            'email',
            'password',
            'portal_password',
            // Thêm các trường khác nếu cần, ví dụ 'role_id'
        ], [
            'email' => $email
        ]);

        return $data ?: null;
    }

    /**
     * Tìm một người dùng Active dựa trên địa chỉ email.
     *
     * @param string $email
     * @return array|null
     */
    public function findActiveUserByEmail(string $email): ?array
    {
        // Lấy các trường cần thiết cho việc xác thực và lưu session
        $data = $this->db->get($this->table, [
            'id',
            'nickName',
            'email',
            'password',
            'portal_password',
            // Thêm các trường khác nếu cần, ví dụ 'role_id'
        ], [
            'email' => $email,
            'status' => 'active' // Chỉ tìm người dùng có trạng thái active
        ]);

        return $data ?: null;
    }

    /**
     * Lấy teacher_id được liên kết với một user_id.
     *
     * @param int $userId
     * @return int|null ID của giáo viên hoặc null nếu không tìm thấy.
     */
    public function getTeacherIdForUser(int $userId): ?int
    {
        $teacherId = $this->db->get('link_users_teachers', 'teacher_id', [
            'user_id' => $userId
        ]);
        return $teacherId ? (int)$teacherId : null;
    }

    /**
     * Lấy dữ liệu giáo viên được liên kết với một user_id.
     *
     * @param int $userId
     * @return array|null Dữ liệu của giáo viên hoặc null nếu không tìm thấy.
     */
    public function getTeacherInfoForUser(int $userId): ?array
    {
        $teacherInfo = $this->db->get('link_users_teachers', [
            '[>]edu_teachers' => ['teacher_id' => 'id'],
        ], [
            'edu_teachers.id',
            'edu_teachers.name',
            'edu_teachers.email',
            'edu_teachers.phone',
            'edu_teachers.school',
            'edu_teachers.avatar',
            'edu_teachers.ref_code',
            'edu_teachers.domain',
            'edu_teachers.gender',
            'edu_teachers.display_name',
        ], [
            'link_users_teachers.user_id' => $userId
        ]);
        return $teacherInfo ?: null;
    }

    /**
     * Lấy danh sách các center_id mà một người dùng được phép truy cập.
     *
     * @param int $userId
     * @return array Mảng các ID của trung tâm.
     */
    public function getAllowedCenterIdsForUser(int $userId): array
    {
        return $this->db->select('link_users_centers', 'center_id', [
            'user_id' => $userId
        ]);
    }


    // --- CÁC HÀM MỚI CHO MAGIC LINK ---
    public function updateLoginToken(int $userId, string $tokenHash, string $expiresAt): bool
    {
        $stmt = $this->db->update($this->table, [
            'login_token' => $tokenHash,
            'login_token_expires_at' => $expiresAt
        ], ['id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function findByLoginToken(string $tokenHash): ?array
    {
        return $this->db->get($this->table, '*', ['login_token' => $tokenHash]);
    }

    public function clearLoginToken(int $userId): bool
    {
        $stmt = $this->db->update($this->table, [
            'login_token' => null,
            'login_token_expires_at' => null
        ], ['id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    // --- HÀM MỚI CHO PORTAL PASSWORD ---
    public function updatePortalPassword(int $userId, string $passwordHash): bool
    {
        $stmt = $this->db->update($this->table, [
            'portal_password' => $passwordHash
        ], ['id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    
    // --- CÁC HÀM MỚI CHO "GHI NHỚ ĐĂNG NHẬP" ---
    public function createAuthToken(int $userId, string $selector, string $hashedValidator, string $expiresAt): bool
    {
        $stmt = $this->db->insert('auth_tokens', [
            'user_id' => $userId,
            'selector' => $selector,
            'hashed_validator' => $hashedValidator,
            'expires_at' => $expiresAt
        ]);
        return $stmt->rowCount() > 0;
    }

    public function findTokenBySelector(string $selector): ?array
    {
        return $this->db->get('auth_tokens', '*', ['selector' => $selector]);
    }

    public function deleteTokenBySelector(string $selector): bool
    {
        $stmt = $this->db->delete('auth_tokens', ['selector' => $selector]);
        return $stmt->rowCount() > 0;
    }
}
