<?php
namespace App\Services;

use App\Models\UserModel;

/**
 * AuthService
 *
 * Chứa logic nghiệp vụ cho việc xác thực người dùng (đăng nhập, đăng xuất).
 */
class AuthService
{
    /** @var UserModel */
    protected $userModel;
    /** @var EmailService */
    protected $emailService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->emailService = new EmailService();
    }


    // =================================================================
    // HÀM TIỆN ÍCH CHUNG 
    // =================================================================

    /**
     * Lấy và đóng gói thông tin quyền hạn của người dùng.
     *
     * @param int $userId
     * @return array|string
     */
    private function getUserPermissions(int $userId)
    {
        $user = $this->userModel->find($userId); // Lấy thông tin cơ bản
        if (!$user) return 'user_not_found';

        $teacherInfo = $this->userModel->getTeacherInfoForUser($userId);
        if (!$teacherInfo) return 'not_a_teacher'; // Nếu không phải là tài khoản giáo viên, không cho phép đăng nhập vào cổng này
            
        $allowedCenterIds = $this->userModel->getAllowedCenterIdsForUser($userId);
        if (empty($allowedCenterIds)) return 'no_center_access';

        return [
            'id' => $user['id'],
            'name' => $user['nickName'],
            'email' => $user['email'],
            'teacher_id' => $teacherInfo['id'] ?? null, // ID giáo viên để "đóng vai"
            'teacher' => $teacherInfo ?? null,
            'center_ids' => $allowedCenterIds
        ];
    }


    // =================================================================
    // PHƯƠNG ÁN 1: MAGIC LINK
    // =================================================================

    /**
     * Tạo và lưu một token đăng nhập duy nhất cho người dùng.
     *
     * @param string $email
     * @return string|null Token (dạng plaintext) để gửi qua email, hoặc null nếu không tìm thấy user.
     */
    public function generateAndSendLoginToken(string $email): ?string
    {
        $user = $this->userModel->findActiveUserByEmail($email);
        if (!$user) {
            return null;
        }

        // Tạo một token ngẫu nhiên, an toàn
        $token = bin2hex(random_bytes(32));
        // Băm token trước khi lưu vào CSDL để tăng bảo mật
        $tokenHash = hash('sha256', $token);
        // Đặt thời gian hết hạn (ví dụ: 15 phút)
        $expiresAt = (new \DateTime('+15 minutes'))->format('Y-m-d H:i:s');

        $this->userModel->updateLoginToken($user['id'], $tokenHash, $expiresAt);

        // Gửi email chứa token gốc (chưa băm)
        $result = $this->emailService->sendMagicLink($email, $token);

        return $token;
    }

    /**
     * Xác thực người dùng bằng token từ magic link.
     *
     * @param string $token
     * @return array|string
     */
    public function loginWithToken(string $token)
    {
        $tokenHash = hash('sha256', $token);
        $user = $this->userModel->findByLoginToken($tokenHash);

        if (!$user) {
            return 'invalid_token';
        }

        // Kiểm tra xem token đã hết hạn chưa
        if (new \DateTime() > new \DateTime($user['login_token_expires_at'])) {
            return 'expired_token';
        }

        // Đăng nhập thành công, xóa token để không thể tái sử dụng
        // $this->userModel->clearLoginToken($user['id']);

        // Lấy quyền hạn và trả về dữ liệu người dùng
        return $this->getUserPermissions($user['id']);
    }


    // =================================================================
    // PHƯƠNG ÁN 2: PORTAL PASSWORD
    // =================================================================

    /**
     * Đặt hoặc cập nhật mật khẩu riêng cho cổng thông tin.
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function setPortalPassword(int $userId, string $newPassword): bool
    {
        // Sử dụng hàm băm mật khẩu tiêu chuẩn và an toàn nhất của PHP
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->userModel->updatePortalPassword($userId, $passwordHash);
    }

    /**
     * Xác thực người dùng bằng email và mật khẩu riêng của cổng thông tin.
     *
     * @param string $email
     * @param string $password
     * @return array|string
     */
    public function loginWithPortalPassword(string $email, string $password, bool $rememberMe = false)
    {
        $user = $this->userModel->findActiveUserByEmail($email);

        if (!$user || empty($user['portal_password'])) {
            return 'invalid_credentials';
        }

        // Sử dụng hàm xác thực tiêu chuẩn của PHP
        if (password_verify($password, $user['portal_password'])) {
            // Mật khẩu chính xác, lấy quyền hạn
            $permissions = $this->getUserPermissions($user['id']);
            if (is_array($permissions)) {
                if ($rememberMe) {
                    $this->createRememberMeToken($user['id']);
                }
            }
            return $permissions;
        }

        return 'invalid_credentials';
    }


    /**
     * Xử lý đăng nhập tự động bằng cookie "Ghi nhớ".
     *
     * @return array|false
     */
    public function loginWithCookie()
    {
        $cookie = $_COOKIE['remember_me'] ?? null;
        if (!$cookie) {
            return false;
        }

        list($selector, $validator) = explode(':', $cookie, 2);
        if (!$selector || !$validator) {
            return false;
        }

        $tokenData = $this->userModel->findTokenBySelector($selector);
        if (!$tokenData) {
            return false;
        }

        // Kiểm tra token hết hạn
        if (new \DateTime() > new \DateTime($tokenData['expires_at'])) {
            $this->userModel->deleteTokenBySelector($selector); // Xóa token hết hạn
            return false;
        }

        // So sánh validator
        $hashedValidator = hash('sha256', $validator);
        if (hash_equals($tokenData['hashed_validator'], $hashedValidator)) {
            // Đăng nhập thành công
            $userPermissions = $this->getUserPermissions($tokenData['user_id']);

            // Bảo mật: Xóa token cũ và tạo token mới
            $this->userModel->deleteTokenBySelector($selector);
            $this->createRememberMeToken($tokenData['user_id']);

            return $userPermissions;
        }

        return false;
    }

    /**
     * Tạo và lưu token "Ghi nhớ" vào CSDL và cookie.
     */
    private function createRememberMeToken(int $userId)
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hashedValidator = hash('sha256', $validator);
        $expiresAt = (new \DateTime('+30 days'))->format('Y-m-d H:i:s');

        $this->userModel->createAuthToken($userId, $selector, $hashedValidator, $expiresAt);

        setcookie(
            'remember_me',
            $selector . ':' . $validator,
            time() + (30 * 24 * 60 * 60), // 30 ngày
            '/' // Áp dụng cho toàn bộ domain
        );
    }







    
}
