<?php
namespace App\Services;

use App\Models\ClassModel;
use App\Models\SessionModel;
use App\Models\StudentModel;
use App\Models\AttendanceModel;
use App\Models\AcademicResultModel;

/**
 * AcademicReportService
 *
 * Chứa logic nghiệp vụ để tạo dữ liệu cho Báo cáo Tình hình Học tập.
 */
class AcademicReportService
{
    protected $classModel;
    protected $sessionModel;
    protected $studentModel;
    protected $attendanceModel;
    protected $academicResultModel;

    public function __construct()
    {
        $this->classModel = new ClassModel();
        $this->sessionModel = new SessionModel();
        $this->studentModel = new StudentModel();
        $this->attendanceModel = new AttendanceModel();
        $this->academicResultModel = new AcademicResultModel();
    }

    /**
     * Lấy toàn bộ dữ liệu cần thiết cho bản in báo cáo học tập.
     *
     * @param int $sessionId
     * @return array|null
     */
    public function getPrintableReportData(int $sessionId): ?array
    {
        // 1. Lấy thông tin ca học hiện tại để xác định ngày và lớp
        $currentSession = $this->sessionModel->find($sessionId);
        if (!$currentSession) return null;

        $classId = $currentSession['class_id'];
        $sessionDate = $currentSession['date'];

        // 2. Lấy thông tin lớp học và cơ sở (sử dụng phương thức mới trong Model)
        $classInfo = $this->classModel->findWithCenterDetails($classId);
        if (!$classInfo) return null;

        // 3. Lấy tất cả các ca học của lớp trong ngày hôm đó
        $sessionsOfTheDay = $this->sessionModel->getSessionsForClassOnDate($classId, $sessionDate);
        if (empty($sessionsOfTheDay)) return null;
        $sessionIdsOfTheDay = array_column($sessionsOfTheDay, 'id');

        // 4. Lấy danh sách học viên trong ca học
        $studentIds = $this->attendanceModel->getUniqueStudentIdsForSessions($sessionIdsOfTheDay);
        if (empty($studentIds)) return null;

        // Lấy thông tin học sinh
        $studentLists = $this->studentModel->findByIds($studentIds);

        // 5. Lấy dữ liệu điểm danh
        $attendances = $this->attendanceModel->getAttendanceDataForSessions($sessionIdsOfTheDay);

        // 6. Lấy kết quả học tập
        $academicResults = $this->academicResultModel->getResultsForStudentsInSessions($studentIds, $sessionIdsOfTheDay);
        
        // 7. Tính toán số buổi nghỉ trong kỳ (sử dụng phương thức mới trong Model)
        $absenceCounts = $this->attendanceModel->countAbsencesInTermForStudents($studentIds, $classId, $sessionDate);



        // Chúng ta sẽ dùng một phương thức đơn giản hơn vì chỉ cần ID và tên
        // $studentsRaw = $this->studentModel->getBasicStudentsByClassId($classId);
        // if (empty($studentsRaw)) return null;
        // $studentIds = array_column($studentsRaw, 'id');



        // 8. Xây dựng cấu trúc dữ liệu cuối cùng
        $studentDataMap = [];
        foreach ($studentLists as $student) {
            $studentDataMap[$student['id']] = [
                'info' => $student,
                'sessions' => [],
                'absence_count' => $absenceCounts[$student['id']] ?? 0
            ];
        }
        
        foreach ($academicResults as $result) {
            $updateData = [];
            if ($attendances[$result['session_id'].'-'.$result['student_id']]['attendance'] == 'present') {
                if (empty($result['btvn_max'])) {
                    $updateData['btvn_complete_percent'] = '-1';
                    $updateData['btvn_score_100'] = '-1';
                } else {
                    if (!empty($result['btvn_complete'])) {
                        $updateData['btvn_complete_percent'] = round((int) $result['btvn_complete'] / (int) $result['btvn_max'] * 100);
                    } else {
                        $updateData['btvn_complete_percent'] = '0';
                    }
                    
                    if (!empty($result['btvn_score'])) {
                        $updateData['btvn_score_100'] = round((int) $result['btvn_score'] / (int) $result['btvn_max'] * 100);
                    } else {
                        $updateData['btvn_score_100'] = '0';
                    }
                }
            } else {
                $updateData['btvn_complete_percent'] = '-1';
                $updateData['btvn_score_100'] = '-1';
            }
            $this->academicResultModel->updateOrCreate(['student_id' => $result['student_id'], 'session_id' => $result['session_id']], $updateData);
            
            $studentDataMap[$result['student_id']]['sessions'][$result['session_id']] = $result;
            
        }

        foreach ($attendances as $attendance) {
            $studentDataMap[$attendance['student_id']]['sessions'][$attendance['session_id']]['attendance'] = $attendance['attendance'];
        }

        return [
            'class_info' => $classInfo,
            'session_date' => $sessionDate,
            'sessions_of_the_day' => $sessionsOfTheDay,
            'students' => $studentDataMap
        ];
    }
}
