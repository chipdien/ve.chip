<?php 
use App\Models\SessionInteractionModel; 
use App\Models\FeedbackOptionModel; 

use App\Services\TeacherDataService;



$sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT) ?? 0;
$studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT) ?? 0;
$teacherId = filter_input(INPUT_POST, 'teacher_id', FILTER_VALIDATE_INT) ?? 0;
$pointsAwarded = filter_input(INPUT_POST, 'points_awarded', FILTER_VALIDATE_INT) ?? 0;
$tag = $_POST['tag'] ?? '';

// dump($sessionId);
// dump($studentId);
// dump($teacherId);
// dump($pointsAwarded);
// dump($tag); 
// die();

if ($sessionId <= 0 || $studentId <= 0 || $teacherId <= 0) {
    throw new InvalidArgumentException('Thiếu session_id, student_id hoặc teacher_id.');
}

$service = new TeacherDataService();
$feedbackOption = $service->getFeedbackOptions();
// $feedbackOptionModel =  // new FeedbackOptionModel();
// $feedbackOption = $feedbackOptionModel->findOneBy(['tag' => $tag]);
if (!$feedbackOption) {
    throw new InvalidArgumentException('Tag không hợp lệ.');
}

$point = $pointsAwarded ?? ($feedbackOption[$tag]['default_points'] ?? 0);

$sessionInteractionModel = new SessionInteractionModel();
$data = [
    'session_id' => $sessionId,
    'student_id' => $studentId,
    'teacher_id' => $teacherId,
    'created_by_id' => $teacherId,
    'feedback_tag' => $tag,
    'points_awarded' => $point,
];
$result = $sessionInteractionModel->create($data);

$interactionInfo = [
    'id' => $result,
    'feedback_tag' => $tag,
    'label' => $feedbackOption[$tag]['label'],
    'icon' => $feedbackOption[$tag]['icon'],
    'type' => $feedbackOption[$tag]['type'],
    'points_awarded' => $point,
];

if ($result) {
    echo json_encode(['success' => true, 'interaction' => array_merge($interactionInfo, $data)]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cập nhật không thành công.']);
}