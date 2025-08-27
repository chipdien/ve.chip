<?php
// File: /ajax/teacherapp_get_schedule.php
// /ajax.php?action=teacherapp_get_session_details&session_id=AAAA
use App\Services\TeacherDataService;
use App\Models\SessionModel;

$classId = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT) ?? 0;
$teacherId = filter_input(INPUT_GET, 'teacher_id', FILTER_VALIDATE_INT) ?? 0;

if (!$classId) {
    http_response_code(400);
    echo json_encode(['error' => 'ID lớp học không hợp lệ.']);
    // echo json_encode(['success' => false, 'data' => $data]);
    exit;
}

// dump($classId); die();
$dataService = new TeacherDataService();
$details = $dataService->getClassDetails($classId, $teacherId);
// print_r($details); die();

if ($details) {
    echo json_encode(['success' => true, 'data' => $details]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Không tìm thấy lớp học.']);
}

