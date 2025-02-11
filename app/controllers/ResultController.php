<?php

namespace App\Controllers;

use App\Models\Result;
use App\Models\Pupil;
use App\Models\Subject;
use App\Helpers\AuthHelper;
use App\Helpers\ValidationHelper;
use App\Helpers\ErrorHandler;

class ResultController {
    private $result;
    private $pupil;
    private $subject;
    private $validator;

    public function __construct() {
        $this->result = new Result();
        $this->pupil = new Pupil();
        $this->subject = new Subject();
        $this->validator = new ValidationHelper();
    }

    public function index() {
        AuthHelper::requireRole(['admin', 'teacher']);
        
        $currentYear = date('Y');
        $academicYears = range($currentYear, $currentYear - 5);
        $terms = [1, 2, 3];
        $classes = $this->pupil->getClassList();
        
        $selectedClass = $_GET['class'] ?? '';
        $selectedYear = $_GET['academic_year'] ?? $currentYear;
        $selectedTerm = $_GET['term'] ?? 1;

        if ($selectedClass) {
            $results = $this->result->getResultsByClass(
                $selectedClass, 
                $selectedYear, 
                $selectedTerm
            );
        }

        require __DIR__ . '/../views/admin/results/index.php';
    }

    public function enterResults() {
        AuthHelper::requireRole(['admin', 'teacher']);
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $results = [];
                foreach ($_POST['marks'] as $pupilId => $subjects) {
                    foreach ($subjects as $subjectId => $marks) {
                        $results[] = [
                            'pupil_id' => $pupilId,
                            'subject_id' => $subjectId,
                            'academic_year' => $_POST['academic_year'],
                            'term' => $_POST['term'],
                            'first_sequence_marks' => floatval($marks['first_sequence']),
                            'second_sequence_marks' => floatval($marks['second_sequence']),
                            'exam_marks' => floatval($marks['exam']),
                            'total_marks' => (
                                floatval($marks['first_sequence']) + 
                                floatval($marks['second_sequence']) + 
                                floatval($marks['exam'])
                            ) / 3,
                            'term_average' => 0, // Will be calculated later
                            'ranking' => 0, // Will be updated later
                            'teacher_comment' => $marks['comment'] ?? ''
                        ];
                    }
                }

                if ($this->result->saveResults($results)) {
                    // Update rankings after saving results
                    $this->result->updateRankings(
                        $_POST['class'],
                        $_POST['academic_year'],
                        $_POST['term']
                    );
                    
                    ErrorHandler::setSuccess("Results saved successfully");
                    header('Location: /admin/results');
                    exit;
                }
            } catch (\Exception $e) {
                ErrorHandler::logError("Failed to save results", [
                    'error' => $e->getMessage(),
                    'data' => $_POST
                ]);
                $errors['database'] = "Failed to save results. Please try again.";
            }
        }

        $classes = $this->pupil->getClassList();
        $currentYear = date('Y');
        $academicYears = range($currentYear, $currentYear - 5);
        $terms = [1, 2, 3];
        
        require __DIR__ . '/../views/admin/results/enter.php';
    }

    public function viewPupilResults($pupilId) {
        AuthHelper::requireRole(['admin', 'teacher', 'parent']);
        
        $pupil = $this->pupil->getPupilById($pupilId);
        if (!$pupil) {
            header('Location: /admin/results');
            exit;
        }

        // Check if parent is authorized to view this pupil's results
        if ($_SESSION['user_type'] === 'parent') {
            if ($_SESSION['user_id'] !== $pupil['parent_id']) {
                http_response_code(403);
                require __DIR__ . '/../views/errors/403.php';
                exit;
            }
        }

        $currentYear = date('Y');
        $selectedYear = $_GET['academic_year'] ?? $currentYear;
        $selectedTerm = $_GET['term'] ?? 1;

        $results = $this->result->getPupilResults(
            $pupilId,
            $selectedYear,
            $selectedTerm
        );

        require __DIR__ . '/../views/admin/results/view.php';
    }
} 