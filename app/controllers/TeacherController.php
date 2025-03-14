<?php

namespace App\Controllers;

use App\Models\Result;
use App\Models\Subject;
use App\Models\Pupil;
use App\Models\Teacher;
use App\Models\MarkingPeriod;
use App\Helpers\AuthHelper;
use App\Helpers\ErrorHandler;

class TeacherController {
    private $result;
    private $subject;
    private $pupil;
    private $teacher;
    private $markingPeriod;

    public function __construct() {
        $this->result = new Result();
        $this->subject = new Subject();
        $this->pupil = new Pupil();
        $this->teacher = new Teacher();
        $this->markingPeriod = new MarkingPeriod();
    }

    public function index() {
        AuthHelper::requireRole('teacher');
        
        try {
            $teacherId = $_SESSION['user_id'];
            
            // Get subjects assigned to this teacher
            $subjects = $this->subject->getTeacherSubjects($teacherId);
            
            // Get current marking period
            $currentPeriod = $this->markingPeriod->getCurrentPeriod();
            
            require __DIR__ . '/../views/teacher/dashboard.php';
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to load teacher dashboard", [
                'error' => $e->getMessage()
            ]);
            $error = "Failed to load dashboard";
            require __DIR__ . '/../views/teacher/dashboard.php';
        }
    }

    public function classRoster() {
        AuthHelper::requireRole('teacher');
        
        try {
            $teacherId = $_SESSION['user_id'];
            $teacherInfo = $this->teacher->getTeacherById($teacherId);
            
            if (!$teacherInfo || !is_array($teacherInfo)) {
                throw new \Exception('Teacher information not found');
            }
            
            $assignedClass = $teacherInfo['assigned_class'] ?? null;
            if (!$assignedClass) {
                throw new \Exception('No class assigned to teacher');
            }
            
            // Get all active pupils in the class
            $pupils = $this->pupil->getActiveStudentsByClass($assignedClass);
            
            // Ensure pupils is an array
            if (!is_array($pupils)) {
                $pupils = [];
            }
            
            // Get current marking period
            $currentPeriod = $this->markingPeriod->getCurrentPeriod();
            
            // Initialize empty arrays if no data
            if (!$currentPeriod || !is_array($currentPeriod)) {
                $currentPeriod = [
                    'term' => null,
                    'academic_year' => null,
                    'sequence' => null
                ];
            }
            
            // For each pupil, get their attendance statistics and academic performance
            foreach ($pupils as &$pupil) {
                // Get attendance statistics
                $attendance = (new \App\Models\Attendance())->getAttendanceStatistics(
                    $pupil['pupil_id'] ?? 0,
                    $currentPeriod['term']
                );
                
                // Initialize attendance if null
                if (!$attendance || !is_array($attendance)) {
                    $attendance = [
                        'present_days' => 0,
                        'absent_days' => 0,
                        'late_days' => 0
                    ];
                }
                $pupil['attendance'] = $attendance;
                
                // Get academic performance
                $results = $this->result->getPupilResults(
                    $pupil['pupil_id'] ?? 0,
                    $currentPeriod['academic_year'],
                    $currentPeriod['term']
                );
                
                // Ensure results is an array
                if (!is_array($results)) {
                    $results = [];
                }
                
                // Calculate term average
                $totalWeightedMarks = 0;
                $totalCoefficients = 0;
                foreach ($results as $result) {
                    if (isset($result['first_sequence_marks'], $result['second_sequence_marks'], $result['exam_marks'], $result['coefficient'])) {
                        $average = ($result['first_sequence_marks'] + $result['second_sequence_marks'] + $result['exam_marks']) / 3;
                        $totalWeightedMarks += ($average * $result['coefficient']);
                        $totalCoefficients += $result['coefficient'];
                    }
                }
                
                $pupil['term_average'] = $totalCoefficients > 0 ? 
                    number_format($totalWeightedMarks / $totalCoefficients, 2) : 'N/A';
                
                // Get pupil's position
                $position = $this->result->getPupilPosition(
                    $pupil['pupil_id'] ?? 0,
                    $assignedClass,
                    $currentPeriod['academic_year'],
                    $currentPeriod['term']
                );
                
                $pupil['position'] = $position ?: 'N/A';
            }
            
            require __DIR__ . '/../views/teacher/class-roster.php';
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to load class roster", [
                'error' => $e->getMessage()
            ]);
            $error = "Failed to load class roster";
            $pupils = [];
            $assignedClass = null;
            $currentPeriod = [
                'term' => null,
                'academic_year' => null,
                'sequence' => null
            ];
            require __DIR__ . '/../views/teacher/class-roster.php';
        }
    }

    public function recordMarks($subjectId = null, $class = null) {
        AuthHelper::requireRole('teacher');
        
        try {
            $teacherId = $_SESSION['user_id'];
            ErrorHandler::logError("Debug - Teacher ID: " . $teacherId);
            
            $teacherInfo = $this->teacher->getTeacherById($teacherId);
            if (!$teacherInfo) {
                ErrorHandler::logError("Debug - Teacher not found: " . $teacherId);
                throw new \Exception('Teacher information not found');
            }
            
            $assignedClass = $teacherInfo['assigned_class'];
            ErrorHandler::logError("Debug - Assigned Class: " . $assignedClass);

            // If no subject ID is provided, show the list of subjects for teacher's class
            if ($subjectId === null) {
                ErrorHandler::logError("Debug - No subject ID provided, showing subject list");
                $subjects = $this->subject->getSubjectsByClass($assignedClass);
                $currentPeriod = $this->markingPeriod->getCurrentPeriod();
                
                require __DIR__ . '/../views/teacher/record-marks-list.php';
                return;
            }

            ErrorHandler::logError("Debug - Subject ID provided: " . $subjectId);

            // Verify that the subject exists
            $subject = $this->subject->getSubjectById($subjectId);
            if (!$subject) {
                ErrorHandler::logError("Debug - Subject not found: " . $subjectId);
                $_SESSION['error'] = 'Subject not found';
                header('Location: ' . url('teacher/record-marks'));
                exit;
            }

            ErrorHandler::logError("Debug - Subject found: " . json_encode($subject));

            // Verify that the subject belongs to teacher's assigned class
            if ($subject['class'] !== $assignedClass) {
                ErrorHandler::logError("Debug - Subject class mismatch: Subject class = " . $subject['class'] . ", Teacher class = " . $assignedClass);
                $_SESSION['error'] = 'You are not authorized to record marks for this class';
                header('Location: ' . url('teacher/record-marks'));
                exit;
            }

            // Check if marking period is active
            if (!$this->markingPeriod->canTeacherRecord()) {
                $_SESSION['error'] = 'Marking period is not active';
                header('Location: ' . url('teacher/record-marks'));
                exit;
            }

            $currentPeriod = $this->markingPeriod->getCurrentPeriod();
            $pupils = $this->pupil->getActiveStudentsByClass($assignedClass);
            
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    if (empty($_POST['marks'])) {
                        throw new \Exception('No marks data provided');
                    }

                    $marks = [];
                    foreach ($_POST['marks'] as $pupilId => $data) {
                        // Validate marks
                        $firstSequence = isset($data['first_sequence']) ? floatval($data['first_sequence']) : null;
                        $secondSequence = isset($data['second_sequence']) ? floatval($data['second_sequence']) : null;
                        $exam = isset($data['exam']) ? floatval($data['exam']) : null;

                        // Validate mark ranges
                        if (($firstSequence !== null && ($firstSequence < 0 || $firstSequence > 20)) ||
                            ($secondSequence !== null && ($secondSequence < 0 || $secondSequence > 20)) ||
                            ($exam !== null && ($exam < 0 || $exam > 20))) {
                            throw new \Exception('Marks must be between 0 and 20');
                        }

                        $marks[] = [
                            'pupil_id' => $pupilId,
                            'subject_id' => $subjectId,
                            'academic_year' => $_POST['academic_year'],
                            'term' => $_POST['term'],
                            'first_sequence_marks' => $firstSequence,
                            'second_sequence_marks' => $secondSequence,
                            'exam_marks' => $exam,
                            'teacher_comment' => $data['comment'] ?? null
                        ];
                    }

                    if ($this->result->saveResults($marks)) {
                        $_SESSION['success'] = "Marks saved successfully";
                        header('Location: ' . url('teacher/record-marks/' . $subjectId));
                        exit;
                    } else {
                        throw new \Exception('Failed to save marks');
                    }
                } catch (\Exception $e) {
                    ErrorHandler::logError("Failed to save marks", [
                        'error' => $e->getMessage(),
                        'data' => $_POST
                    ]);
                    $_SESSION['error'] = "Failed to save marks: " . $e->getMessage();
                }
            }

            // Get existing marks
            $existingMarks = $this->result->getClassSubjectMarks(
                $assignedClass, 
                $subjectId, 
                $currentPeriod['academic_year'], 
                $currentPeriod['term']
            );
            
            // Pass the marking period object to the view
            $markingPeriod = $this->markingPeriod;
            
            require __DIR__ . '/../views/teacher/record-marks.php';
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to load marks recording page", [
                'error' => $e->getMessage()
            ]);
            $_SESSION['error'] = "Failed to load marks recording page: " . $e->getMessage();
            header('Location: ' . url('teacher/dashboard'));
            exit;
        }
    }
} 