<?php 
// File: app/Services/TestResultService.php (Mới)
namespace App\Services;

use App\Models\StudentModel;
use App\Models\EduTestModel;
use App\Models\EduTestResultModel;

class TestResultService
{
    private $api;

    /**
     * @var EduTestResultModel
     */
    protected $testResultModel;

    /**
     * @var EduTestModel
     */
    protected $testModel;

    /**
     * @var StudentModel
     */
    protected $studentModel;

    public function __construct()
    {
        $this->api = new ApiService();
        $this->testResultModel = new EduTestResultModel();
        $this->testModel = new EduTestModel();
        $this->studentModel = new StudentModel();
    }

    public function getTestData(int $testId): ?array
    {
        $testInfo = $this->testModel->findWithClassDetails($testId);
        if (!$testInfo) return null;

        $results = $this->testResultModel->getResultsByTestIdWithStudentInfo($testId);

        $students = array_map(function($result) {
            return [
                'result_id' => $result['id'],
                'student_id' => $result['student_id'],
                'full_name' => $result['full_name'],
                'dob' => $result['dob'],
                'score' => $result['score'],
                'comment' => $result['comments']
            ];
        }, $results ?? []);

        return [
            'test_info' => $testInfo,
            'students' => $students
        ];
    }

    public function getAvailableStudents(int $testId, int $classId): array
    {
        $results = $this->testResultModel->getResultsByTestIdWithStudentInfo($testId);
        $existingStudentIds = array_column($results ?? [], 'student_id');

        // $allStudents = $this->api->get("edu_classes/{$classId}/students_inclass:list", ['filter' => '{"status":"enroll"}']);

        $available = $this->studentModel->getEnrolledStudentsNotInList($classId, $existingStudentIds);

        // $available = array_filter($allStudents['data'] ?? [], function($student) use ($existingStudentIds) {
        //     return !in_array($student['id'], $existingStudentIds);
        // });

        return $available; //array_values($available); // Reset array keys
    }

    public function addStudentsToTest(int $testId, array $studentIds, int $enteredById): bool
    {
        foreach ($studentIds as $studentId) {
            $this->api->post('edu_test_results:create', [
                'test_id' => $testId,
                'student_id' => (int)$studentId,
                'entered_by_id' => $enteredById
            ]);
        }
        return true;
    }

    public function updateTestResultField(int $resultId, string $field, $value, int $enteredById): bool
    {
        $payload = [
            $field === 'score' ? 'score' : 'comments' => $value,
            'entered_by_id' => $enteredById
        ];
        
        $response = $this->api->put("edu_test_results:update?filterByTk={$resultId}", $payload);
        return $response !== null;
    }

    /**
     * Lấy toàn bộ dữ liệu cần thiết cho bản in báo cáo kết quả kiểm tra.
     *
     * @param int $testId ID của bài kiểm tra.
     * @return array|null Dữ liệu cho view hoặc null nếu có lỗi.
     */
    public function getPrintableViewData(int $testId): ?array
    {
        // 1. Lấy thông tin bài kiểm tra, join với lớp và trung tâm
        $testInfo = $this->testModel->findWithCenterDetails($testId);
        if (!$testInfo) {
            return null; // Không tìm thấy bài test
        }

        // 2. Lấy danh sách kết quả của các học sinh trong bài test đó
        $results = $this->testResultModel->getResultsByTestIdWithStudentInfo($testId);

        // 3. Gộp dữ liệu và trả về
        return [
            'test_info' => $testInfo,
            'results' => $results
        ];
    }
}
