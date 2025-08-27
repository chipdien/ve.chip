<?php 
use App\Models\SessionInteractionModel; 

$interactionId = filter_input(INPUT_POST, 'interaction_id', FILTER_VALIDATE_INT) ?? 0;
$teacherId = filter_input(INPUT_POST, 'teacher_id', FILTER_VALIDATE_INT) ?? 0;
// $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT) ?? 0;
// $pointsAwarded = filter_input(INPUT_POST, 'points_awarded', FILTER_VALIDATE_INT) ?? 0;
// $tag = $_POST['tag'] ?? '';

if ($interactionId <= 0 || $teacherId <= 0) {
    throw new InvalidArgumentException('Thiếu session_id, student_id hoặc teacher_id.');
}

$sessionInteractionModel = new SessionInteractionModel();
$result = $sessionInteractionModel->delete($interactionId);

if ($result) {
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cập nhật không thành công.']);
}