<?php
use App\Services\TeacherDataService;
use App\Models\SessionModel;

$session_id = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT) ?? 0;
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?? 0;
$content = $data['content'] ?? ''; // Lấy content từ POST

if ($session_id <= 0) {
    throw new InvalidArgumentException('Thiếu session_id .');
}

$updatedData = [
    'updated_by_id' => $user_id,
    'content' => $content
];

$sessionModel = new SessionModel();
$sessionModel->update($session_id, $updatedData);

echo json_encode(['success' => true, 'data' => $updatedData]);
