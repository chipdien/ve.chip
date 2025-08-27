<?php
/**
 * AJAX ROUTER TRUNG TÂM
 *
 * File này nhận tất cả các yêu cầu AJAX, kiểm tra và xác thực tham số 'action',
 * sau đó gọi đến file xử lý tương ứng trong thư mục /ajax/.
 */

// =================================================================
// BƯỚC 1: KHỞI TẠO & KIỂM TRA BẢO MẬT
// =================================================================

// Bao gồm các file cần thiết như config, functions để có thể truy cập CSDL và các hàm khác
require_once __DIR__ . '/../init.php'; 
// require_once 'functions.php'; // functions.php đã bao gồm cả config và kết nối DB

// Luôn trả về kiểu JSON
header('Content-Type: application/json');


// Lấy dữ liệu JSON từ body của request
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

// Nếu không có JSON body (ví dụ: request FormData), thử lấy từ $_POST
if (empty($data)) {
    $data = $_POST;
}

// Gộp cả $_GET để linh hoạt hơn
$data = array_merge($data, $_GET);

$action = $data['action'] ?? '';
$user = $_SESSION['user'] ?? null;


// Lấy action từ request (POST được khuyến khích cho các hành động thay đổi dữ liệu)
// $action = $_POST['action'] ?? $_GET['action'] ?? '';

// Kiểm tra xem action có được cung cấp không
if (empty($action)) {
    // Trả về lỗi và dừng thực thi nếu không có action
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Hành động (action) không được cung cấp.']);
    exit;
}

// Kiểm tra đăng nhập (có thể thêm ngoại lệ cho các action công khai)
// if (!$user) {
//     http_response_code(401); // Unauthorized
//     echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện hành động này.']);
//     exit;
// }



// =================================================================
// BƯỚC 2: ĐỊNH TUYẾN (ROUTING)
// =================================================================

// Bảo mật: Làm sạch tên action để chỉ cho phép các ký tự an toàn (chữ, số, gạch dưới)
// Điều này giúp ngăn chặn các cuộc tấn công Directory Traversal.
$safe_action = preg_replace('/[^a-zA-Z0-9_]/', '', $action);

// Tạo đường dẫn đến file xử lý trong thư mục /ajax/
$handler_file = __DIR__ . '/../ajax/' . $safe_action . '.php';
// dump($handler_file); die();
// Kiểm tra xem file xử lý có tồn tại không
if (file_exists($handler_file)) {
    try {
        // Nếu file tồn tại, gọi nó để xử lý.
        // File handler sẽ chịu trách nhiệm tạo ra output JSON.
        require $handler_file;
    } catch (Exception $e) {
        // Bắt các lỗi không mong muốn từ file handler
        http_response_code(500); // Internal Server Error
        // Ghi lại lỗi để debug (quan trọng trên môi trường production)
        error_log("Lỗi trong file handler '$safe_action': " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi máy chủ. Vui lòng thử lại sau.']);
    }
} else {
    // Trả về lỗi nếu không tìm thấy file xử lý cho action
    http_response_code(404); // Not Found
    echo json_encode(['success' => false, 'message' => "Hành động không hợp lệ hoặc không được hỗ trợ: " . htmlspecialchars($action)]);
}

exit; // Luôn kết thúc kịch bản sau khi xử lý xong