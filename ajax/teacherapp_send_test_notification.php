<?php
/**
 * HANDLER: send_test_notification
 *
 * Gửi một thông báo thử nghiệm đến người dùng hiện tại.
 * - Biến $user được cung cấp bởi ajax.php.
 */

use App\Services\NotificationService;

$service = new NotificationService();
$success = $service->sendNotification(
    $user['id'],
    'Thông báo thử nghiệm',
    'Đây là một thông báo được gửi từ Cổng thông tin Giáo viên.'
);

echo json_encode(['success' => $success]);

