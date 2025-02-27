<?php

namespace App\Controllers;

use App\Models\Subject;
use App\Models\Pupil;
use App\Helpers\AuthHelper;
use App\Helpers\ValidationHelper;
use App\Helpers\SanitizationHelper;
use App\Helpers\ErrorHandler;

class SubjectController {
    private $subject;
    private $validator;

    public function __construct() {
        $this->subject = new Subject();
        $this->validator = new ValidationHelper();
    }

    public function index() {
        AuthHelper::requireRole('admin');
        
        try {
            $selectedClass = $_GET['class'] ?? '';
            $classList = (new Pupil())->getClassList();
            
            if ($selectedClass) {
                $subjects = $this->subject->getSubjectsByClass($selectedClass);
            } else {
                $subjects = $this->subject->getAllSubjects();
            }
            
            require __DIR__ . '/../views/admin/subjects/index.php';
            
        } catch (\PDOException $e) {
            ErrorHandler::logError("Failed to load subjects", [
                'error' => $e->getMessage()
            ]);
            $error = "Failed to load subjects";
            $subjects = [];
            require __DIR__ . '/../views/admin/subjects/index.php';
        }
    }

    public function add() {
        AuthHelper::requireRole('admin');
        $errors = [];
        
        // Get categories and class list
        $categories = $this->subject->getCategories();
        $classList = (new \App\Models\Pupil())->getClassList();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sanitizedData = [
                'name' => SanitizationHelper::sanitize($_POST['name']),
                'code' => SanitizationHelper::sanitize($_POST['code']),
                'class' => SanitizationHelper::sanitize($_POST['class']),
                'coefficient' => floatval($_POST['coefficient']),
                'category' => SanitizationHelper::sanitize($_POST['category'])
            ];

            $rules = [
                'name' => ['required', ['min', 2], ['max', 50]],
                'code' => ['required', ['min', 2], ['max', 10], ['unique', 'subjects', 'code']],
                'class' => ['required', ['in', array_keys($classList)]],
                'coefficient' => ['required', ['min', 0.5], ['max', 5]],
                'category' => ['required', ['in', array_keys($categories)]]
            ];

            if ($this->validator->validate($sanitizedData, $rules)) {
                try {
                    if ($this->subject->createSubject($sanitizedData)) {
                        ErrorHandler::setSuccess("Subject created successfully");
                        header('Location: ' . url('admin/subjects'));
                        exit;
                    }
                } catch (\PDOException $e) {
                    ErrorHandler::logError("Failed to create subject", [
                        'error' => $e->getMessage(),
                        'data' => $sanitizedData
                    ]);
                    $errors['database'] = "Failed to create subject. Please try again.";
                }
            } else {
                $errors = $this->validator->getErrors();
            }
        }
        
        require __DIR__ . '/../views/admin/subjects/add.php';
    }

    public function edit($id) {
        AuthHelper::requireRole('admin');
        
        try {
            // Get subject data
            $subject = $this->subject->getSubjectById($id);
            
            // Get supporting data
            $categories = $this->subject->getCategories();
            $classList = (new \App\Models\Pupil())->getClassList();
            
            $errors = [];
            
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Sanitize input
                $data = [
                    'name' => SanitizationHelper::sanitize($_POST['name'] ?? ''),
                    'code' => SanitizationHelper::sanitize($_POST['code'] ?? ''),
                    'class' => SanitizationHelper::sanitize($_POST['class'] ?? ''),
                    'coefficient' => floatval($_POST['coefficient'] ?? 1),
                    'category' => SanitizationHelper::sanitize($_POST['category'] ?? '')
                ];
                
                // Validate
                $rules = [
                    'name' => ['required', ['min', 2], ['max', 50]],
                    'code' => ['required', ['min', 2], ['max', 10]],
                    'class' => ['required'],
                    'coefficient' => ['required', ['min', 0.5], ['max', 5]],
                    'category' => ['required']
                ];
                
                if ($this->validator->validate($data, $rules)) {
                    if ($this->subject->updateSubject($id, $data)) {
                        ErrorHandler::setSuccess("Subject updated successfully");
                        header('Location: ' . url('admin/subjects'));
                        exit;
                    }
                    $errors['database'] = "Failed to update subject";
                } else {
                    $errors = $this->validator->getErrors();
                }
            }
            
            // Display the form
            require __DIR__ . '/../views/admin/subjects/edit.php';
            
        } catch (\Exception $e) {
            ErrorHandler::logError("Error in subject edit", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            ErrorHandler::setError('subject', $e->getMessage());
            header('Location: ' . url('admin/subjects'));
            exit;
        }
    }

    public function delete($id) {
        AuthHelper::requireRole('admin');
        
        try {
            if ($this->subject->deleteSubject($id)) {
                ErrorHandler::setSuccess("Subject deleted successfully");
            }
        } catch (\Exception $e) {
            ErrorHandler::setError('delete', $e->getMessage());
        }
        
        header('Location: ' . url('admin/subjects'));
        exit;
    }
} 