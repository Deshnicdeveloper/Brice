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

    public function recordMarks($subjectId = null, $class = null) {
        AuthHelper::requireRole('teacher');
        
        try {
            $teacherId = $_SESSION['user_id'];
            $teacherInfo = $this->teacher->getTeacherById($teacherId);
            $assignedClass = $teacherInfo['assigned_class'];

            // If no subject ID is provided, show the list of subjects for teacher's class
            if ($subjectId === null) {
                $subjects = $this->subject->getSubjectsByClass($assignedClass);
                $currentPeriod = $this->markingPeriod->getCurrentPeriod();
                
                require __DIR__ . '/../views/teacher/record-marks-list.php';
                return;
            }

            // Check if marking period is active
            if (!$this->markingPeriod->canTeacherRecord()) {
                ErrorHandler::setError('record', 'Marking period is not active');
                header('Location: ' . url('teacher/record-marks'));
                exit;
            }

            // Verify that the subject belongs to teacher's assigned class
            $subject = $this->subject->getSubjectById($subjectId);
            if ($subject['class'] !== $assignedClass) {
                ErrorHandler::setError('record', 'You are not authorized to record marks for this class');
                header('Location: ' . url('teacher/record-marks'));
                exit;
            }

            $currentPeriod = $this->markingPeriod->getCurrentPeriod();
            $pupils = $this->pupil->getActiveStudentsByClass($assignedClass);
            
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->saveMarks($_POST);
            }

            // Get existing marks
            $existingMarks = $this->result->getClassSubjectMarks(
                $assignedClass, 
                $subjectId, 
                $currentPeriod['academic_year'], 
                $currentPeriod['term']
            );
            
            // Pass the markingPeriod instance to the view
            $markingPeriod = $this->markingPeriod;
            
            require __DIR__ . '/../views/teacher/record-marks.php';
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to load marks recording page", [
                'error' => $e->getMessage()
            ]);
            header('Location: ' . url('teacher/dashboard'));
            exit;
        }
    }

    private function saveMarks($postData) {
        try {
            $marks = [];
            foreach ($postData['marks'] as $pupilId => $data) {
                $marks[] = [
                    'pupil_id' => $pupilId,
                    'subject_id' => $postData['subject_id'],
                    'academic_year' => $postData['academic_year'],
                    'term' => $postData['term'],
                    'first_sequence_marks' => $data['first_sequence'] ?? null,
                    'second_sequence_marks' => $data['second_sequence'] ?? null,
                    'exam_marks' => $data['exam'] ?? null,
                    'teacher_comment' => $data['comment'] ?? null
                ];
            }

            if ($this->result->saveResults($marks)) {
                ErrorHandler::setSuccess("Marks saved successfully");
            }
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to save marks", [
                'error' => $e->getMessage(),
                'data' => $postData
            ]);
            ErrorHandler::setError('save', 'Failed to save marks');
        }
    }
} 