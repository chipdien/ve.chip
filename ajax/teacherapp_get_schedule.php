<?php
// File: /ajax/teacherapp_get_schedule.php
use App\Services\TeacherDataService;

$teacher_id = (int)($data['teacher_id'] ?? 0);

if ($teacher_id <= 0) {
    throw new InvalidArgumentException('Thiếu teacher_id.');
}

$service = new TeacherDataService();
$success = $service->getDynamicWeeklySchedule($teacher_id);  


echo json_encode(['success' => true, 'data' => $success]);
 