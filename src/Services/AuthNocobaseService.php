<?php
// Đường dẫn file: /src/Services/AuthNocobaseService.php

namespace App\Services;

/**
 * AuthNocobaseService
 * * Dịch vụ này xử lý logic xác thực người dùng thông qua Nocobase API.
 * Nó sử dụng ApiService để thực hiện các yêu cầu mạng.
 */
class AuthNocobaseService
{
    /**
     * @var ApiService Một instance của ApiService để thực hiện các cuộc gọi API.
     */
    private $apiService;

    /**
     * Hàm khởi tạo.
     *
     * @param ApiService $apiService Instance của ApiService được inject vào.
     */
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Thực hiện đăng nhập bằng tài khoản Nocobase.
     *
     * Quy trình bao gồm 2 bước:
     * 1. Gọi API `auth:signIn` để lấy token xác thực.
     * 2. Dùng token đó để gọi API `auth:check` nhằm lấy thông tin chi tiết của người dùng, bao gồm cả vai trò (roles).
     *
     * @param string $email Email của người dùng.
     * @param string $password Mật khẩu của người dùng.
     * @return array|false Trả về một mảng chứa thông tin ['user' => ..., 'token' => ...] nếu thành công, ngược lại trả về false.
     */
    public function login(string $email, string $password): ?array
    {
        // --- Bước 1: Gọi API để đăng nhập và lấy token ---
        
        $payload = [
            'email' => $email,
            'password' => $password
        ];
        
        // Thực hiện yêu cầu POST đến endpoint `signIn`.
        // Tùy chọn 'use_auth' => false là rất quan trọng vì đây là yêu cầu công khai, không cần token.
        $loginResult = $this->apiService->post('auth:signIn', $payload, ['use_auth' => false]);

        // Kiểm tra nếu đăng nhập thất bại hoặc kết quả trả về không hợp lệ
        if (!$loginResult || !isset($loginResult['data']['token']) || !isset($loginResult['data']['user'])) {
            error_log("Nocobase sign-in failed for email: " . $email);
            return null;
        }

        // Lấy token và thông tin user cơ bản từ kết quả đăng nhập
        $token = $loginResult['data']['token'];
        $user_data_from_login = $loginResult['data']['user'];


        // --- Bước 2: Dùng token để lấy thông tin chi tiết và quyền (roles) ---

        // Thực hiện yêu cầu GET đến endpoint `check`.
        // Lần này, chúng ta truyền 'token' vừa nhận được để xác thực yêu cầu.
        $checkResult = $this->apiService->get('auth:check', [], ['token' => $token]);
        
        // Nếu gọi API `check` thành công và có dữ liệu trả về
        if ($checkResult && isset($checkResult['data'])) {
            // Gộp thông tin chi tiết (đặc biệt là 'roles') từ `checkResult`
            // vào thông tin người dùng đã có từ `loginResult`.
            // Điều này đảm bảo chúng ta có một đối tượng user đầy đủ nhất.
            $user_data_from_check = $checkResult['data'];
            $final_user_data = array_merge($user_data_from_login, $user_data_from_check);
        } else {
            // Nếu gọi API `check` thất bại, hệ thống vẫn cho phép đăng nhập
            // nhưng chỉ với thông tin cơ bản. Ghi lại lỗi để kiểm tra sau.
            error_log("Nocobase auth:check failed after successful login for user ID: " . $user_data_from_login['id']);
            $final_user_data = $user_data_from_login;
            // Đảm bảo trường 'roles' tồn tại dưới dạng mảng rỗng để tránh lỗi ở các bước sau
            $final_user_data['roles'] = []; 
        }

        // Thêm một định danh để biết user đăng nhập bằng phương thức nào
        $final_user_data['auth_method'] = 'nocobase';

        // Trả về kết quả cuối cùng bao gồm thông tin user đầy đủ và token
        return [
            'user' => $final_user_data,
            'token' => $token
        ];
    }
}
