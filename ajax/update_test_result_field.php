<?php
// File: v5/ajax/update_test_result_field.php
use App\Services\TestResultService;

$result_id = (int)($data['result_id'] ?? 0);
$field = $data['field'] ?? '';
$value = $data['value'] ?? '';
$current_user_id = (int)($data['current_user_id'] ?? 1);

if ($result_id <= 0 || empty($field)) {
    throw new InvalidArgumentException('Thiếu result_id hoặc tên trường.');
}

$service = new TestResultService();
$success = $service->updateTestResultField($result_id, $field, $value, $current_user_id);

echo json_encode(['success' => $success]);
