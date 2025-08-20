<?php
namespace App\Services;

use App\Models\EmailLogModel;

/**
 * EmailService
 *
 * Chịu trách nhiệm cho việc gửi email qua các nhà cung cấp dịch vụ
 * và ghi log lại các email đã gửi.
 */
class EmailService
{
    /** @var EmailLogModel */
    protected $emailLogModel;

    public function __construct()
    {
        $this->emailLogModel = new EmailLogModel();
    }

    /**
     * Gửi email đăng nhập Magic Link.
     *
     * @param string $toEmail Email của người nhận.
     * @param string $token Token đăng nhập (plaintext).
     * @return array Kết quả gửi email.
     */
    public function sendMagicLink(string $toEmail, string $token): array
    {
        // Xây dựng URL đăng nhập
        $loginUrl = 'https://chip.vietelite.edu.vn/v5/teacher/login.php?token=' . urlencode($token);

        // Xây dựng nội dung HTML cho email
        $htmlContent = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>Xin chào,</h2>
                <p>Bạn đã yêu cầu đăng nhập vào Cổng thông tin Giáo viên của VietElite. Vui lòng nhấp vào nút bên dưới để hoàn tất đăng nhập.</p>
                <p style='text-align: center;'>
                    <a href='{$loginUrl}' style='display: inline-block; padding: 12px 24px; font-size: 16px; color: #fff; background-color: #16a34a; text-decoration: none; border-radius: 5px;'>
                        Đăng nhập vào hệ thống
                    </a>
                </p>
                <p>Liên kết này sẽ hết hạn sau 15 phút. Nếu bạn không yêu cầu đăng nhập, vui lòng bỏ qua email này.</p>
                <p>Trân trọng,<br>Đội ngũ VietElite Education</p>
            </div>
        ";

        $emailData = [
            'to_email' => $toEmail,
            'to_name' => 'Giáo viên',
            'subject' => '[VietElite] Liên kết đăng nhập của bạn',
            'html_content' => $htmlContent,
            'email_type' => 'magic_link_login',
        ];

        return $this->sendWithMaileroo($emailData);
    }

    /**
     * Gửi email sử dụng API của Maileroo và ghi log.
     *
     * @param array $emailData Mảng chứa thông tin email.
     * @return array Kết quả gửi.
     */
    private function sendWithMaileroo(array $emailData): array
    {
        $apiKey = \MAILEROO_API_KEY;
        $senderEmail = $emailData['from_email'] ?? 'VietElite Sender <sender@vietelite.edu.vn>';
        $apiUrl = 'https://smtp.maileroo.com/send';
        
        $referenceId = $this->_generateSecureUniqueHex(12);
        
        $payload = [
            'from' => $senderEmail,
            'to' => "{$emailData['to_name']} <{$emailData['to_email']}>",
            'subject' => $emailData['subject'],
            'html' => $emailData['html_content'],
            'reference_id' => $referenceId,
            'tracking' => 'yes',
        ];

        if (!empty($emailData['cc_emails'])) $payload['cc'] = implode(',', $emailData['cc_emails']);
        if (!empty($emailData['bcc_emails'])) $payload['bcc'] = implode(',', $emailData['bcc_emails']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-Key: '. $apiKey]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'Lỗi cURL: ' . $error];
        }

        $decoded_response = json_decode($response, true);
        
        if ($httpCode === 200 && isset($decoded_response['success']) && $decoded_response['success'] === true) {
            $logData = [
                'subject' => $emailData['subject'],
                'content' => $emailData['html_content'],
                'center_id' => $emailData['center_id'] ?? null,
                'address_from' => $senderEmail,
                'address_to' => $emailData['to_email'],
                'address_cc' => !empty($emailData['cc_emails']) ? implode(',', $emailData['cc_emails']) : null,
                'address_bcc' => !empty($emailData['bcc_emails']) ? implode(',', $emailData['bcc_emails']) : null,
                'num_sent' => 1,
                'last_sent_at' => date('Y-m-d H:i:s'),
                'email_type' => $emailData['email_type'],
                'status' => 'publish',
                'provider' => 'maileroo',
                'maileroo_reference_id' => $referenceId,
            ];
            
            $this->emailLogModel->create($logData);
            return ['success' => true, 'message' => 'Email đã được gửi thành công.'];
        } else {
            $error_message = $decoded_response['message'] ?? 'API của Maileroo trả về lỗi không xác định.';
            return ['success' => false, 'message' => $error_message, 'http_code' => $httpCode];
        }
    }

    /**
     * Tạo một chuỗi hex ngẫu nhiên, an toàn.
     */
    private function _generateSecureUniqueHex($byteLength = 16): string
    {
        return bin2hex(random_bytes($byteLength));
    }
}
