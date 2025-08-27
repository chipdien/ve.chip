<?php 
use App\Services\NotificationService;

$subscription = $data['subscription'] ?? '';

if (!$subscription) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin subscription.']);
    exit;
}

$service = new NotificationService();
$success = $service->saveSubscription($user['id'], $subscription);

if ($success) {
    // Gửi thông báo chào mừng
    // $service->sendTestNotification($user['id'], 'Cảm ơn bạn đã đăng ký nhận thông báo!');
    echo json_encode(['success' => $success, 'message' => 'Đăng ký thành công.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể lưu thông tin đăng ký.']);
}
exit;