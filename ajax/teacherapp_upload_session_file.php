<?php
use App\Services\S3StorageService;
use App\Models\SessionModel;

$s3Service = new S3StorageService();
$sessionModel = new SessionModel();

// Lấy và xác thực dữ liệu đầu vào
$sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
$classCode = trim($_POST['class_code'] ?? '');
$sessionDate = trim($_POST['session_date'] ?? '');
$userId = trim($_POST['current_user_id'] ?? '');
$type = trim($_POST['type'] ?? 'documents');
$file = $_FILES['file'] ?? null;

if (!$sessionId || !$classCode || !$sessionDate || !$userId || !$file || $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ hoặc lỗi tải file.']);
    exit;
}

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
$s3FolderPath = "public/docs/{$classCode}/{$sessionDate}_{$sessionId}/{$type}";
$fileName = time() . '-' . preg_replace('/[^A-Za-z0-9.\-]/', '', basename($file['name']));
$s3Key = "{$s3FolderPath}/{$fileName}";

// dump($s3FolderPath);
// dump($s3Key);
// dump($userId);
// die();

// Gọi S3 Service để upload
$fileUrl = $s3Service->uploadFile($file['tmp_name'], $s3Key, $file['type']);

if ($fileUrl) {
    // 5. Nếu upload thành công, cập nhật CSDL
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