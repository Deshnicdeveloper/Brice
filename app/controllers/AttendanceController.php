<?php

namespace App\Controllers;

use App\Models\Attendance;
use App\Models\Pupil;
use App\Models\MarkingPeriod;
use App\Helpers\AuthHelper;
use App\Helpers\ErrorHandler;

class AttendanceController {
    private $attendance;
    private $pupil;
    private $markingPeriod;

    public function __construct() {
        $this->attendance = new Attendance();
        $this->pupil = new Pupil();
        $this->markingPeriod = new MarkingPeriod();
    }

    public function index() {
        AuthHelper::requireRole(['admin', 'teacher']);
        
        try {
            $date = $_GET['date'] ?? date('Y-m-d');
            $currentPeriod = $this->markingPeriod->getCurrentPeriod();
            $classList = $this->pupil->getClassList();
            
            // Debug information
            error_log('User Role: ' . AuthHelper::getRole());
            error_log('Session Data: ' . print_r($_SESSION, true));
            
            // Set class based on user role
            if (AuthHelper::getRole() === 'teacher') {
                $class = AuthHelper::getTeacherClass();
                error_log('Teacher Class from AuthHelper: ' . ($class ?? 'null'));
                
                if (!$class) {
                    // If class is not in session, try to fetch it directly
                    $teacherId = $_SESSION['user_id'] ?? null;
                    if ($teacherId) {
                        $teacher = (new \App\Models\Teacher())->getTeacherById($teacherId);
                        $class = $teacher['assigned_class'] ?? null;
                        // Update session
                        $_SESSION['assigned_class'] = $class;
                        error_log('Fetched Teacher Class: ' . ($class ?? 'null'));
                    }
                }
            } else {
                $class = $_GET['class'] ?? '';
            }
            
            error_log('Final Class Value: ' . ($class ?? 'null'));
            
            $pupils = [];
            $attendanceData = [];
            
            if ($class) {
                $pupils = $this->pupil->getPupilsByClass($class);
                $attendanceData = $this->attendance->getAttendanceByClass($class, $date);
            }
            
            require __DIR__ . '/../views/attendance/index.php';
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to load attendance page", [
                'error' => $e->getMessage()
            ]);
            $error = "Failed to load attendance page";
            require __DIR__ . '/../views/attendance/index.php';
        }
    }

    public function record() {
        AuthHelper::requireRole(['admin', 'teacher']);
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }

            $date = $_POST['date'] ?? date('Y-m-d');
            $term = $_POST['term'] ?? null;
            $attendanceData = $_POST['attendance'] ?? [];

            if (empty($attendanceData)) {
                throw new \Exception('No attendance data provided');
            }

            // Format attendance data for bulk insert
            $formattedData = [];
            foreach ($attendanceData as $pupilId => $status) {
                $formattedData[] = [
                    'pupil_id' => $pupilId,
                    'date' => $date,
                    'status' => $status['status'],
                    'term' => $term,
                    'reason' => $status['reason'] ?? null
                ];
            }

            if ($this->attendance->bulkRecordAttendance($formattedData)) {
                $_SESSION['success'] = "Attendance recorded successfully";
            } else {
                $_SESSION['error'] = "Failed to record attendance";
            }

            header('Location: ' . url('attendance') . '?date=' . urlencode($date));
            exit;
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to record attendance", [
                'error' => $e->getMessage()
            ]);
            $_SESSION['error'] = "Failed to record attendance: " . $e->getMessage();
            header('Location: ' . url('attendance'));
            exit;
        }
    }

    public function viewPupilAttendance($pupilId) {
        AuthHelper::requireRole(['admin', 'teacher', 'parent']);
        
        try {
            $pupil = $this->pupil->getPupilById($pupilId);
            if (!$pupil) {
                throw new \Exception('Pupil not found');
            }

            // Check access permissions
            if (AuthHelper::getRole() === 'teacher') {
                $teacherClass = AuthHelper::getTeacherClass();
                if ($pupil['class'] !== $teacherClass) {
                    throw new \Exception('Unauthorized access');
                }
            } elseif (AuthHelper::getRole() === 'parent') {
                $parentPupils = AuthHelper::getParentPupils();
                if (!in_array($pupilId, $parentPupils)) {
                    throw new \Exception('Unauthorized access');
                }
            }

            // Get current marking period
            $currentPeriod = $this->markingPeriod->getCurrentPeriod();
            
            // Get selected term or use current term
            $term = $_GET['term'] ?? $currentPeriod['term'] ?? null;
            
            // Get attendance records and statistics
            $attendanceRecords = $this->attendance->getAttendanceByPupil($pupilId, $term);
            $statistics = $this->attendance->getAttendanceStatistics($pupilId, $term);
            
            // Get overall statistics for comparison
            $overallStats = $this->attendance->getAttendanceStatistics($pupilId);
            
            // Add academic year to view data
            $academicYear = $currentPeriod['academic_year'] ?? date('Y');

            require __DIR__ . '/../views/attendance/view.php';
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to view pupil attendance", [
                'error' => $e->getMessage(),
                'pupil_id' => $pupilId
            ]);
            $error = "Failed to view attendance: " . $e->getMessage();
            require __DIR__ . '/../views/attendance/view.php';
        }
    }
} 