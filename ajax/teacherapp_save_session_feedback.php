<?php 
use App\Models\SessionFeedbackModel;

$sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT) ?? 0;
$teacherId = filter_input(INPUT_POST, 'teacher_id', FILTER_VALIDATE_INT) ?? 0;
$studentsPresent = filter_input(INPUT_POST, 'students_present', FILTER_VALIDATE_INT) ?? 0;

if ($sessionId <= 0 || $teacherId <= 0) {
    throw new InvalidArgumentException('Thiếu session_id hoặc teacher_id.');
}
$where = [
    'session_id' => $sessionId,
    'teacher_id' => $teacherId,
];

$feedbackData = [
    'students_present_actual' => filter_input(INPUT_POST, 'students_present', FILTER_VALIDATE_INT),
    'rating_hygiene' => filter_input(INPUT_POST, 'rating_hygiene', FILTER_VALIDATE_INT),
    'rating_facilities' => filter_input(INPUT_POST, 'rating_facilities', FILTER_VALIDATE_INT),
    'rating_operations' => filter_input(INPUT_POST, 'rating_operations', FILTER_VALIDATE_INT),
    'rating_admin' => filter_input(INPUT_POST, 'rating_admin', FILTER_VALIDATE_INT),
    'rating_assistant' => filter_input(INPUT_POST, 'rating_assistant', FILTER_VALIDATE_INT),
];

$model = new SessionFeedbackModel();
$result = $model->updateOrCreateBy($where, $feedbackData);

if ($result) {
    echo json_encode(['success' => true, 'data' => array_merge($feedbackData, $where)]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cập nhật không thành công.']);
}
