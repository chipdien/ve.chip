<?php
// File: /ajax/teacherapp_get_schedule.php
// /ajax.php?action=teacherapp_get_sessions&teacher_id=AAAA&type=${tabName}&page=${tabState.page}&class_id=${tabState.filterClassId}
use App\Services\TeacherDataService;
use App\Models\SessionModel;

$filter = [];
$page = (int) $data['page'] ?? 1;
$teacher_id = (int) $data['teacher_id'] ?? 0;
$filter['teacher_id'] = $teacher_id;

if (isset($data['type']) && $data['type'] === 'past') {
    $filter['type'] = 'past';
} else {
    $filter['type'] = 'upcoming';
}

if (isset($data['class_id']) && !empty($data['class_id'])) {
    $filter['class_id'] = (int)$data['class_id'];
    $class_id = $filter['class_id'];
} else {
    $class_id = 0;
} 


if ($teacher_id <= 0) {
    throw new InvalidArgumentException('Thiếu teacher_id hoặc class_id.');
}

$sessionModel = new SessionModel();
$success = $sessionModel->getSessions($filter, $page, 15);

// $service = new TeacherDataService();
// $success = $service->getDynamicWeeklySchedule($teacher_id);  


echo json_encode(['success' => true, 'data' => $success]);
 