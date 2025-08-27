<?php 
use App\Models\UserModel;

define('APP_BASE_PATH', '');

$userModel = new UserModel();

if (!hasRole('teacher')) {
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    header('Location: /login.php?not-teacher=1');
    exit;
}


if (!isset($user['teacher_id']) || empty($user['teacher_id'])) {
    $teacherInfo = $userModel->getTeacherInfoForUser($user['id']);
    if (!isset($teacherInfo)) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $_SESSION['user']['teacher_id'] = $teacherInfo['id'];
    $_SESSION['user']['teacher'] = $teacherInfo;
}
