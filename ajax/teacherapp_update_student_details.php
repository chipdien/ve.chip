<?php
use App\Services\TeacherDataService;
use App\Models\StudentModel;

$studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT) ?? 0;
$dob = $data['dob'] ?? '';
$school = $data['school'] ?? '';
$aspiration = $data['aspiration'] ?? '';
$note = $data['note'] ?? '';

if ($studentId <= 0) {
    throw new InvalidArgumentException('Thiếu student_id.');
}

$updateData = [
    'dob' => $dob,
    'school' => $school,
    'aspiration' => $aspiration,
    'note' => $note,
];

$studentModel = new StudentModel();
$result = $studentModel->update($studentId, $updateData);

if ($result) {
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cập nhật không thành công.']);
}
