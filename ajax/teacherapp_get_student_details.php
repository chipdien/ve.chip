<?php
// File: /ajax/teacherapp_get_schedule.php
// /ajax.php?action=teacherapp_get_session_details&session_id=AAAA
use App\Services\TeacherDataService;
use App\Models\StudentModel;

$studentId = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT) ?? 0;

if (!$studentId) {
    http_response_code(400);
    echo json_encode(['error' => 'ID học sinh không hợp lệ.']);
    exit;
}

$model = new StudentModel();
$details = $model->find($studentId);

// $dataService = new TeacherDataService();
// $details = $dataService->getClassDetails($classId);
// print_r($details); die();

if ($details) {
    echo json_encode(['success' => true, 'data' => $details]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Không tìm thấy lớp học.']);
}

