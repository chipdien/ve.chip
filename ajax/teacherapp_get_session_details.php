<?php
// File: /ajax/teacherapp_get_schedule.php
// /ajax.php?action=teacherapp_get_session_details&session_id=AAAA
use App\Services\TeacherDataService;
use App\Models\SessionModel;

$sessionId = (int) $data['session_id'] ?? 0;

if ($sessionId <= 0) {
    throw new InvalidArgumentException('Thiếu session_id.');
}

// $sessionModel = new SessionModel();
// $success = $sessionModel->getSessions($filter, $page, 15);

$data = [];

$service = new TeacherDataService();
$sessionDetails = $service->getSessionDetails($sessionId);  

$students = $service->getSessionStudents($sessionId, $sessionDetails['class_id']) ?? [];
$studentIds = array_column($students, 'id');

$feedbackOptions = $service->getFeedbackOptions();
$interactions = $service->getSessionInteractions($studentIds, $sessionId);

foreach ($interactions as $i) {
    $i['label'] = $feedbackOptions[$i['feedback_tag']]['label'];
    $i['icon'] = $feedbackOptions[$i['feedback_tag']]['icon'];
    $i['type'] = $feedbackOptions[$i['feedback_tag']]['type'];

    $students[$i['student_id']]['interactions'][] = $i;
}


$data = [
    'details' => $sessionDetails,
    'feedback_options' => $service->getFeedbackOptions(),
    'students' => $students,
];

echo json_encode(['success' => true, 'data' => $data]);
 