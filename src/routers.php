<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    // Nạp file chứa các hàm model
    require __DIR__ . '/models.php';
    
    $app->get('/', function (Request $request, Response $response, array $args) {
        $response->getBody()->write('Hello World 123123');
        return $response;
    });

    // Route để hiển thị trang quản lý học phí
    $app->get('/class/{class_id}/tuition', function (Request $request, Response $response, array $args) {
        // Lấy các dịch vụ từ container
        $db = $this->get('db');
        $view = $this->get('view');

        // Lấy tham số từ URL và query string
        $class_id = (int)$args['class_id'];
        $params = $request->getQueryParams();
        $billing_period = $params['billing_period'] ?? date('Y-m');

        // Gọi các hàm model để lấy dữ liệu
        $class = get_class_code($db, $class_id);
        if (!$class) {
            // Xử lý lỗi nếu không tìm thấy lớp
            $response->getBody()->write("Lỗi: Không tìm thấy lớp học.");
            return $response->withStatus(404);
        }
        $students = get_students_by_class($db, $class_id);
        $sessions = get_sessions_in_period($db, $class_id, $billing_period);
        // ... gọi các hàm lấy dữ liệu khác ...

        // Chuẩn bị dữ liệu để truyền sang view
        $data_for_view = [
            'current_class_id' => $class_id,
            'selected_billing_period' => $billing_period,
            'class' => $class,
            'students' => $students,
            'sessions' => $sessions,
            'billing_periods' => ['2025-07', '2025-06'], // Lấy động sau
            'save_messages' => $_SESSION['flash_messages'] ?? [],
            // ... truyền các dữ liệu khác ...
        ];
        
        // Xóa flash message sau khi dùng
        unset($_SESSION['flash_messages']);

        // Render giao diện và trả về response
        return $view->render($response, 'tuition_management.phtml', $data_for_view);
    });

    // Route để xử lý việc lưu dữ liệu
    $app->post('/class/{class_id}/tuition', function (Request $request, Response $response, array $args) {
        $db = $this->get('db');
        $class_id = (int)$args['class_id'];
        $post_data = $request->getParsedBody();

        // ...
        // Gọi hàm handle_invoice_update ở đây
        // ...
        
        // Lưu thông báo vào session
        $_SESSION['flash_messages'] = [['type' => 'success', 'text' => 'Đã lưu thay đổi thành công!']];

        // Chuyển hướng về trang cũ để hiển thị kết quả
        $billing_period = $post_data['billing_period'] ?? date('Y-m');
        return $response
            ->withHeader('Location', '/class/' . $class_id . '/tuition?billing_period=' . $billing_period)
            ->withStatus(302);
    });

    // Route để xử lý các yêu cầu AJAX
    // ...
};
