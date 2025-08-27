<?php
use App\Services\S3StorageService;
use App\Models\StudentModel;

$s3Service = new S3StorageService();
$studentModel = new StudentModel();

// Lấy và xác thực dữ liệu đầu vào
$studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT) ?? 0;
$file = $_FILES['avatar'] ?? null;
$updatedById = trim($data['updated_by_id'] ?? '');

if ($studentId <= 0 || !$updatedById || !$file || $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ hoặc lỗi tải file.']);
    exit;
}

// Xây dựng đường dẫn S3
$s3FolderPath = "public/images/students";
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = $studentId . '-' . time() . '.' . $extension;
$s3Key = "{$s3FolderPath}/{$fileName}";

// dump($s3FolderPath);
// dump($s3Key);
// dump($studentId);
// die();

// Gọi S3 Service để upload
$fileUrl = $s3Service->uploadFile($file['tmp_name'], $s3Key, $file['type']);

if ($fileUrl) {
    // 5. Nếu upload thành công, cập nhật CSDL
    $fileUrl = str_replace(\S3_BASE_URL.'/', '', $fileUrl);
    $rowCount = $studentModel->update($studentId, ['avatar' => $fileUrl]);

    if ($rowCount) {
        // Trả về chuỗi URL mới để frontend cập nhật giao diện
        echo json_encode(['success' => true, 'avatarUrl' => $fileUrl]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật cơ sở dữ liệu.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể tải file lên S3.']);
}

//--