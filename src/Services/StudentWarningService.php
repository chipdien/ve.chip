<?php
namespace App\Services;

use App\Models\ClassModel;
use App\Models\AcademicResultModel;

/**
 * StudentWarningService
 *
 * Chứa logic nghiệp vụ để phân tích và tạo cảnh báo
 * về tình hình học tập của học viên.
 */
class StudentWarningService
{
    protected $classModel;
    protected $academicResultModel;

    public function __construct()
    {
        $this->classModel = new ClassModel();
        $this->academicResultModel = new AcademicResultModel();
    }

    /**
     * Lấy dữ liệu cảnh báo và tuyên dương cho một lớp trong 30 ngày gần nhất.
     *
     * @param int $classId
     * @return array|null
     */
    public function getWarningsForClass(int $classId): ?array
    {
        // 1. Lấy thông tin lớp học
        $classInfo = $this->classModel->find($classId);
        if (!$classInfo) {
            return null;
        }

        // 2. Xác định khoảng thời gian (30 ngày gần nhất)
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));

        // 3. Gọi Model để lấy dữ liệu trung bình đã được tính toán
        $studentAverages = $this->academicResultModel->getAverageMetricsForClassInDateRange(
            $classId,
            $startDate,
            $endDate
        );

        // 4. Phân loại học sinh vào các nhóm
        $categorizedStudents = [
            'completion_warning' => [],
            'completion_praise' => [],
            'score_severe_warning' => [],
            'score_mild_warning' => [],
            'score_praise' => [],
        ];

        foreach ($studentAverages as $student) {
            // Phân loại theo Tỷ lệ hoàn thành BTVN
            if (isset($student['avg_completion_percent'])) {
                if ($student['avg_completion_percent'] < 50) {
                    $categorizedStudents['completion_warning'][] = $student;
                } elseif ($student['avg_completion_percent'] >= 75) {
                    $categorizedStudents['completion_praise'][] = $student;
                }
            }

            // Phân loại theo Điểm số BTVN
            if (isset($student['avg_score_100'])) {
                if ($student['avg_score_100'] < 40) {
                    $categorizedStudents['score_severe_warning'][] = $student;
                } elseif ($student['avg_score_100'] < 50) {
                    $categorizedStudents['score_mild_warning'][] = $student;
                } elseif ($student['avg_score_100'] >= 75) {
                    $categorizedStudents['score_praise'][] = $student;
                }
            }
        }

        return [
            'class_info' => $classInfo,
            'time_range' => ['start' => $startDate, 'end' => $endDate],
            'warnings' => $categorizedStudents
        ];
    }
}
