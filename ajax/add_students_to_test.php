<?php
// File: v5/ajax/add_students_to_test.php
use App\Services\TestResultService;

$test_id = (int)($data['test_id'] ?? 0);
$student_ids = $data['student_ids'] ?? [];
$current_user_id = (int)($data['current_user_id'] ?? 1);

if ($test_id <= 0 || empty($student_ids)) {
    throw new InvalidArgumentException('Thiếu test_id hoặc danh sách học viên.');
}

$service = new TestResultService();
$service->addStudentsToTest($test_id, $student_ids, $current_user_id);

// Trả về danh sách đã cập nhật
$updatedData = $service->getTestData($test_id);
echo json_encode(['success' => true, 'data' => $updatedData['students']]);

// ---