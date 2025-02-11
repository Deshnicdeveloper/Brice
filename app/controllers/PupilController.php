<?php

namespace App\Controllers;

use App\Models\Pupil;
use App\Models\Parent;
use App\Helpers\AuthHelper;
use App\Helpers\ValidationHelper;
use App\Helpers\SanitizationHelper;
use App\Helpers\ErrorHandler;

class PupilController {
    private $pupil;
    private $parent;
    private $validator;

    public function __construct() {
        $this->pupil = new Pupil();
        $this->parent = new Parent();
        $this->validator = new ValidationHelper();
    }

    public function index() {
        AuthHelper::requireRole(['admin', 'teacher']);
        $pupils = $this->pupil->getAllPupils();
        require __DIR__ . '/../views/admin/pupils/index.php';
    }

    public function add() {
        AuthHelper::requireRole('admin');
        $errors = [];
        $parents = $this->parent->getAllActiveParents();
        $classList = $this->pupil->getClassList();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sanitizedData = [
                'first_name' => SanitizationHelper::sanitizeName($_POST['first_name']),
                'last_name' => SanitizationHelper::sanitizeName($_POST['last_name']),
                'date_of_birth' => SanitizationHelper::sanitize($_POST['date_of_birth']),
                'gender' => SanitizationHelper::sanitize($_POST['gender']),
                'parent_id' => (int)$_POST['parent_id'],
                'class' => SanitizationHelper::sanitize($_POST['class'])
            ];

            $rules = [
                'first_name' => ['required', ['min', 2], ['max', 50]],
                'last_name' => ['required', ['min', 2], ['max', 50]],
                'date_of_birth' => ['required', 'date'],
                'gender' => ['required', ['in', ['M', 'F']]],
                'parent_id' => ['required', 'exists:parents,parent_id'],
                'class' => ['required', ['in', array_keys($this->pupil->getClassList())]]
            ];

            if ($this->validator->validate($sanitizedData, $rules)) {
                try {
                    $pupilId = $this->pupil->createPupil($sanitizedData);
                    if ($pupilId) {
                        ErrorHandler::setSuccess("Pupil registered successfully with ID: " . $pupilId);
                        header('Location: /admin/pupils');
                        exit;
                    }
                } catch (\PDOException $e) {
                    ErrorHandler::logError("Failed to create pupil", [
                        'error' => $e->getMessage(),
                        'data' => $sanitizedData
                    ]);
                    $errors['database'] = "Failed to register pupil. Please try again.";
                }
            } else {
                $errors = $this->validator->getErrors();
            }
        }

        require __DIR__ . '/../views/admin/pupils/add.php';
    }

    public function edit($id) {
        AuthHelper::requireRole('admin');
        $errors = [];
        $parents = $this->parent->getAllActiveParents();
        $classList = $this->pupil->getClassList();

        $pupil = $this->pupil->getPupilById($id);
        if (!$pupil) {
            header('Location: /admin/pupils');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sanitizedData = [
                'first_name' => SanitizationHelper::sanitizeName($_POST['first_name']),
                'last_name' => SanitizationHelper::sanitizeName($_POST['last_name']),
                'date_of_birth' => SanitizationHelper::sanitize($_POST['date_of_birth']),
                'gender' => SanitizationHelper::sanitize($_POST['gender']),
                'parent_id' => (int)$_POST['parent_id'],
                'class' => SanitizationHelper::sanitize($_POST['class']),
                'status' => SanitizationHelper::sanitize($_POST['status'])
            ];

            $rules = [
                'first_name' => ['required', ['min', 2], ['max', 50]],
                'last_name' => ['required', ['min', 2], ['max', 50]],
                'date_of_birth' => ['required', 'date'],
                'gender' => ['required', ['in', ['M', 'F']]],
                'parent_id' => ['required', 'exists:parents,parent_id'],
                'class' => ['required', ['in', array_keys($this->pupil->getClassList())]],
                'status' => ['required', ['in', ['active', 'inactive', 'graduated']]]
            ];

            if ($this->validator->validate($sanitizedData, $rules)) {
                try {
                    if ($this->pupil->updatePupil($id, $sanitizedData)) {
                        ErrorHandler::setSuccess("Pupil updated successfully");
                        header('Location: /admin/pupils');
                        exit;
                    }
                } catch (\PDOException $e) {
                    ErrorHandler::logError("Failed to update pupil", [
                        'error' => $e->getMessage(),
                        'data' => $sanitizedData
                    ]);
                    $errors['database'] = "Failed to update pupil. Please try again.";
                }
            } else {
                $errors = $this->validator->getErrors();
            }
        }

        require __DIR__ . '/../views/admin/pupils/edit.php';
    }

    public function view($id) {
        AuthHelper::requireRole(['admin', 'teacher', 'parent']);
        
        $pupil = $this->pupil->getPupilById($id);
        if (!$pupil) {
            header('Location: /admin/pupils');
            exit;
        }

        // Check if parent is authorized to view this pupil
        if ($_SESSION['user_type'] === 'parent') {
            if ($_SESSION['user_id'] !== $pupil['parent_id']) {
                http_response_code(403);
                require __DIR__ . '/../views/errors/403.php';
                exit;
            }
        }

        // Get parent details
        $parent = $this->parent->getParentById($pupil['parent_id']);

        // Get academic history
        $academicHistory = $this->pupil->getAcademicHistory($id);

        require __DIR__ . '/../views/admin/pupils/view.php';
    }
} 