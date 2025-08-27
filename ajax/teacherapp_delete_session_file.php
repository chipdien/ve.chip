<?php
use App\Services\S3StorageService;
use App\Models\SessionModel;

$s3Service = new S3StorageService();
$sessionModel = new SessionModel();

// Lấy và xác thực dữ liệu đầu vào
$sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
$fileUrl = trim($_POST['file_url'] ?? '');
$userId = trim($_POST['current_user_id'] ?? '');
$type = trim($_POST['type'] ?? 'documents');

if (!$sessionId || !$fileUrl || !$userId || !$type) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit;
}

$fileUrl = str_replace(\S3_BASE_URL . '/', '', $fileUrl); // Chuyển từ URL đầy đủ về key tương đối

// Xác định đúng tên cột trong CSDL
$fieldName = '';
if ($type === 'documents') {
    $fieldName = 'content_files';
} elseif ($type === 'exercices') {
    $fieldName = 'exercice_files';
} elseif ($type === 'comments') {
    $fieldName = 'comment_files';
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Loại file không hợp lệ.']);
    exit;
}

// Xây dựng đường dẫn S3
$s3FolderPath = dirname($fileUrl); 
// print_r($s3FolderPath); die();

$result = $s3Service->deleteFiles([ltrim($fileUrl, '/')]); // Xóa file khỏi S3, dữ liệu đầu vào ở dạng array
if ($result) {
    // Nếu upload thành công, cập nhật CSDL
    $listFiles = $s3Service->listFiles($s3FolderPath);
    $newFileString = implode(",", array_column($listFiles, 'url')); // $dataService->addFileToSession($sessionId, $fileUrl, $fieldName);
    $rowCount = $sessionModel->update($sessionId, [$fieldName => $newFileString]);

    if ($rowCount) {
        // Trả về chuỗi URL mới để frontend cập nhật giao diện
        echo json_encode(['success' => true, 'files' => $newFileString, 'files_array' => $listFiles]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật cơ sở dữ liệu.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể tải file lên S3.']);
}

//--