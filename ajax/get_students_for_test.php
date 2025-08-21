<?php
// File: v5/ajax/get_students_for_test.php
use App\Services\TestResultService;

$test_id = (int)($data['test_id'] ?? 0);
$class_id = (int)($data['class_id'] ?? 0);

if ($test_id <= 0 || $class_id <= 0) {
    throw new InvalidArgumentException('Thiếu test_id hoặc class_id.');
}

$service = new TestResultService();
$availableStudents = $service->getAvailableStudents($test_id, $class_id);

echo json_encode(['success' => true, 'data' => $availableStudents]);

// ---