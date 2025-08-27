<?php
use App\Services\TeacherDataService;
use App\Models\SessionModel;

$session_id = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT) ?? 0;
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?? 0;

$fieldName = $data['field_name'] ?? '';
$fieldValue = $data['field_value'] ?? '';

if ($session_id <= 0 || !$fieldName || !$fieldValue) {
    throw new InvalidArgumentException('Thiếu session_id, field_name hoặc field_value.');
}

$updatedData = [$fieldName => $fieldValue, 'updated_by_id' => $user_id];

$sessionModel = new SessionModel();
$sessionModel->update($session_id, $updatedData);

echo json_encode(['success' => true, 'data' => $updatedData]);
