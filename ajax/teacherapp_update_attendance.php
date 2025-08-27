<?php
// use App\Services\TeacherDataService;
use App\Models\SessionModel;
use App\Models\AttendanceModel;

$sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT) ?? 0;
$studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT) ?? 0;
$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?? 0;

$fieldName = 'attendance';
$fieldValue = $data['status'] ?? 'holding';

if ($sessionId <= 0 || $studentId <= 0 || $userId <=0 ||  !$fieldValue) {
    throw new InvalidArgumentException('Thiếu session_id, student_id, user_id hoặc field_value.');
}
$where = [
    'session_id' => $sessionId,
    'student_id' => $studentId
];
$updatedData = [$fieldName => $fieldValue, 'updated_by_id' => $userId, 'user_id' => $userId];

// print_r($where);
// print_r($updatedData);
// die();

$attendanceModel = new AttendanceModel();
$result = $attendanceModel->updateOrCreateBy($where, $updatedData);

if ($result) {
    echo json_encode(['success' => true, 'data' => $updatedData]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cập nhật không thành công.']);
}
