<?php
// File: app/Services/NotificationService.php
namespace App\Services;

use App\Models\PushSubscriptionModel;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class NotificationService
{
    protected $pushSubscriptionModel;

    public function __construct()
    {
        $this->pushSubscriptionModel = new PushSubscriptionModel();
    }

    /**
     * Lưu thông tin đăng ký nhận thông báo của người dùng.
     */
    public function saveSubscription(int $userId, array $subscriptionData): bool
    {
        return $this->pushSubscriptionModel->save($userId, $subscriptionData);
    }

    /**
     * Gửi thông báo đến một người dùng cụ thể.
     */
    public function sendNotification(int $userId, string $title, string $body, string $icon = ''): bool
    {
        $subscriptions = $this->pushSubscriptionModel->findByUserId($userId);
        if (empty($subscriptions)) {
            return false;
        }

        $auth = [
            'VAPID' => [
                'subject' => 'mailto:sender@vietelite.edu.vn',
                'publicKey' => \VAPID_PUBLIC_KEY,
                'privateKey' => \VAPID_PRIVATE_KEY,
            ],
        ];

        $webPush = new WebPush($auth);
        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => $icon ?: 'https://vietelite.edu.vn/wp-content/uploads/2022/06/favicon.png',
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub['endpoint'],
                'publicKey' => $sub['p256dh'],
                'authToken' => $sub['auth'],
            ]);
            $webPush->queueNotification($subscription, $payload);
        }

        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                error_log("Lỗi gửi thông báo: {$report->getReason()}");
                // Có thể xóa subscription không hợp lệ ở đây
                // $this->pushSubscriptionModel->deleteByEndpoint($report->getEndpoint());
            }
        }
        
        return true;
    }


    /**
     * Gửi thông báo đến một người dùng cụ thể.
     */
    public function sendNotificationData(int $userId, array $data): bool
    {
        $subscriptions = $this->pushSubscriptionModel->findByUserId($userId);
        if (empty($subscriptions)) {
            return false;
        }

        $auth = [
            'VAPID' => [
                'subject' => 'mailto:sender@vietelite.edu.vn',
                'publicKey' => \VAPID_PUBLIC_KEY,
                'privateKey' => \VAPID_PRIVATE_KEY,
            ],
        ];

        $webPush = new WebPush($auth);
        $payload = json_encode([
            'title' => $data['title'] ?? 'VietElite Thông báo',
            'body' => $data['body'] ?? 'Nội dung thông báo',
            'icon' => $data['icon'] ?? 'https://teacher.vietelite.edu.vn/assets/icons/android-chrome-192x192.png',
            'badge' => $data['badge'] ?? 'https://teacher.vietelite.edu.vn/assets/icons/android-chrome-192x192.png',
            'vibrate' => $data['vibrate'] ?? [200, 100, 200],
            'lang' => $data['lang'] ?? 'vi-VN',
            'tag' => $data['tag'] ?? null,
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub['endpoint'],
                'publicKey' => $sub['p256dh'],
                'authToken' => $sub['auth'],
            ]);
            $webPush->queueNotification($subscription, $payload);
        }

        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                error_log("Lỗi gửi thông báo: {$report->getReason()}");
                // Có thể xóa subscription không hợp lệ ở đây
                // $this->pushSubscriptionModel->deleteByEndpoint($report->getEndpoint());
            }
        }
        
        return true;
    }
}