<?php
// File: src/Services/ApiService.php  
namespace App\Services;

/**
 * ApiService
 * Một lớp tiện ích để xử lý các lệnh gọi cURL đến Nocobase API.
 */
class ApiService
{
    private $apiKey;
    private $apiUrl;

    public function __construct()
    {
        // Giả sử các hằng số này được định nghĩa trong config.php
        $this->apiKey = defined('NOCOBASE_API_KEY') ? \NOCOBASE_API_KEY : null;
        $this->apiUrl = defined('NOCOBASE_API_URL') ? \NOCOBASE_API_URL : 'https://nocobase.vietelite.edu.vn/api/';
    }
    
    /**
     * Thực thi một yêu cầu cURL.
     *
     * @param string $method POST, GET, PUT...
     * @param string $endpoint Ví dụ: /api/users
     * @param array|null $data Dữ liệu gửi đi
     * @param array $options Các tùy chọn bổ sung
     * - 'use_auth' (bool): Có gửi header Authorization hay không. Mặc định là true.
     * - 'token' (string|null): Một token tùy chỉnh để sử dụng thay cho API key mặc định.
     * @return array|null
     */
    private function execute($method, $endpoint, $data = null, $options = [])
    {
        $ch = curl_init();
        $url = rtrim($this->apiUrl, '/') . '/' . ltrim($endpoint, '/');

        // --- [NÂNG CẤP] ---
        // Xử lý các tùy chọn xác thực một cách linh hoạt
        $useAuth = $options['use_auth'] ?? true; // Mặc định là yêu cầu xác thực
        $customToken = $options['token'] ?? null;
        
        $headers = [
            'accept: */*',
            'Content-Type: application/json'
        ];

        if ($useAuth) {
            $tokenToUse = $customToken ?: $this->apiKey;
            if ($tokenToUse) {
                $headers[] = 'Authorization: Bearer ' . $tokenToUse;
            } else {
                 error_log("API Warning: Attempted to make an authenticated request without a token to $url.");
            }
        }
        // --- [KẾT THÚC NÂNG CẤP] ---

        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method === 'POST' || $method === 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }
        
        error_log("API Error ($httpCode) on $method $url: $response");
        return null;
    }

    public function get($endpoint, $params = [], $options = []) {
        return $this->execute('GET', $endpoint, $params, $options);
    }

    public function post($endpoint, $data, $options = []) {
        return $this->execute('POST', $endpoint, $data, $options);
    }

    public function put($endpoint, $data, $options = []) {
        return $this->execute('PUT', $endpoint, $data, $options);
    }
}