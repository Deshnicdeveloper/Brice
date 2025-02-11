<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Stats;
use App\Models\Teacher;
use App\Helpers\AuthHelper;
use App\Helpers\ValidationHelper;
use App\Helpers\SanitizationHelper;
use App\Helpers\ErrorHandler;
use App\Helpers\AuditLogger;

class AdminController {
    private $stats;
    private $teacher;
    private $validator;
    private $pupil;
    private $result;

    public function __construct() {
        $this->stats = new Stats();
        $this->teacher = new Teacher();
        $this->validator = new ValidationHelper();
        $this->pupil = new \App\Models\Pupil();
        $this->result = new \App\Models\Result();
    }

    public function dashboard() {
        // Check if user is logged in and is admin
        AuthHelper::requireRole('admin');
        
        try {
            // Get counts from database
            $db = \App\Config\Database::getInstance()->getConnection();
            
            // Get teacher count
            $teacherCount = $db->query("SELECT COUNT(*) as count FROM teachers")->fetch()['count'];
            
            // Get pupil count
            $pupilCount = $db->query("SELECT COUNT(*) as count FROM pupils")->fetch()['count'];
            
            // Get parent count
            $parentCount = $db->query("SELECT COUNT(*) as count FROM parents")->fetch()['count'];
            
            // Get unique class count
            $classCount = $db->query("SELECT COUNT(DISTINCT class) as count FROM pupils")->fetch()['count'];
            
            // Initialize recentActivities array
            $recentActivities = [];
            
            // Get recent activities from audit_logs
            $recentActivitiesQuery = $db->query(
                "SELECT action as description, created_at as time 
                 FROM audit_logs 
                 ORDER BY created_at DESC 
                 LIMIT 5"
            );
            
            if ($recentActivitiesQuery) {
                $recentActivities = $recentActivitiesQuery->fetchAll();
            }
            
            // Load the dashboard view with data
            require __DIR__ . '/../views/admin/dashboard.php';
            
        } catch (\PDOException $e) {
            ErrorHandler::logError("Dashboard data fetch failed", [
                'error' => $e->getMessage()
            ]);
            $error = "Failed to load dashboard data";
            $recentActivities = []; // Set empty array in case of error
            require __DIR__ . '/../views/admin/dashboard.php';
        }
    }

    public function teachers() {
        AuthHelper::requireRole('admin');
        $teachers = $this->teacher->getAllTeachers();
        require __DIR__ . '/../views/admin/teachers/index.php';
    }

    public function pupils() {
        AuthHelper::requireRole('admin');
        
        try {
            $pupilModel = new \App\Models\Pupil();
            $pupils = $pupilModel->getAllPupils();
            require __DIR__ . '/../views/admin/pupils/index.php';
        } catch (\PDOException $e) {
            ErrorHandler::logError("Failed to fetch pupils", [
                'error' => $e->getMessage()
            ]);
            $error = "Failed to load pupils data";
            $pupils = [];
            require __DIR__ . '/../views/admin/pupils/index.php';
        }
    }

    public function parents() {
        AuthHelper::requireRole('admin');
        
        try {
            // Get all parents using the ParentModel
            $parent = new \App\Models\ParentModel();
            $parents = $parent->getAllParents();
            
            // Load the parents view
            require __DIR__ . '/../views/admin/parents/index.php';
            
        } catch (\PDOException $e) {
            ErrorHandler::logError("Failed to fetch parents", [
                'error' => $e->getMessage()
            ]);
            $error = "Failed to load parents data";
            $parents = [];
            require __DIR__ . '/../views/admin/parents/index.php';
        }
    }

    public function settings() {
        AuthHelper::requireRole('admin');
        
        try {
            // Get current settings
            $db = \App\Config\Database::getInstance()->getConnection();
            
            // Initialize default values
            $academicYear = date('Y');
            $currentTerm = 1;
            $resultsPerPage = 25;
            
            // Try to get settings from database
            try {
                $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
                $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
                
                // Override defaults with database values if they exist
                $academicYear = $settings['academic_year'] ?? $academicYear;
                $currentTerm = $settings['current_term'] ?? $currentTerm;
                $resultsPerPage = $settings['results_per_page'] ?? $resultsPerPage;
            } catch (\PDOException $e) {
                // Log the error but continue with defaults
                ErrorHandler::logError("Failed to fetch settings", [
                    'error' => $e->getMessage()
                ]);
            }
            
            // Load the settings view
            require __DIR__ . '/../views/admin/settings/index.php';
            
        } catch (\PDOException $e) {
            ErrorHandler::logError("Failed to load settings", [
                'error' => $e->getMessage()
            ]);
            
            // Set default values in case of error
            $academicYear = date('Y');
            $currentTerm = 1;
            $resultsPerPage = 25;
            $error = "Failed to load settings";
            
            require __DIR__ . '/../views/admin/settings/index.php';
        }
    }

    public function addTeacher() {
        AuthHelper::requireRole('admin');
        $errors = [];
        
        // Get class list for dropdown
        $pupilModel = new \App\Models\Pupil();
        $classList = $pupilModel->getClassList();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $sanitizedData = [
                'name' => SanitizationHelper::sanitizeName($_POST['name']),
                'email' => SanitizationHelper::sanitizeEmail($_POST['email']),
                'phone' => SanitizationHelper::sanitizePhone($_POST['phone']),
                'assigned_class' => SanitizationHelper::sanitize($_POST['assigned_class'])
            ];

            $rules = [
                'name' => ['required', ['min', 3], ['max', 100]],
                'email' => ['required', 'email', ['unique', 'teachers', 'email']],
                'phone' => ['required', 'phone'],
                'assigned_class' => ['required', ['in', array_keys($classList)]]
            ];

            if ($this->validator->validate($sanitizedData, $rules)) {
                try {
                    // Generate matricule and PIN
                    $matricule = 'TCH' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    $pin = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);

                    $teacherData = [
                        'matricule' => $matricule,
                        'pin' => $pin,
                        ...$sanitizedData
                    ];

                    if ($this->teacher->createTeacher($teacherData)) {
                        ErrorHandler::setSuccess("Teacher added successfully. Matricule: {$matricule}, PIN: {$pin}");
                        header('Location: ' . url('admin/teachers'));
                        exit;
                    }
                } catch (\PDOException $e) {
                    ErrorHandler::logError("Failed to create teacher", [
                        'error' => $e->getMessage(),
                        'data' => $sanitizedData
                    ]);
                    $errors['database'] = "Failed to create teacher. Please try again.";
                }
            } else {
                $errors = $this->validator->getErrors();
            }
        }
        
        require __DIR__ . '/../views/admin/teachers/add.php';
    }

    public function editTeacher($id) {
        AuthHelper::requireRole('admin');
        $errors = [];
        
        $teacher = $this->teacher->getTeacherById($id);
        if (!$teacher) {
            header('Location: /admin/teachers');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inputData = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'assigned_class' => $_POST['assigned_class'],
                'status' => $_POST['status']
            ];
            
            $rules = [
                'name' => ['required', ['min', 3], ['max', 100]],
                'email' => ['required', 'email', ['unique', 'teachers', 'email', $id]],
                'phone' => ['required', 'phone'],
                'assigned_class' => ['required', ['in', ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6']]],
                'status' => ['required', ['in', ['active', 'inactive']]]
            ];

            if ($this->validator->validate($inputData, $rules)) {
                try {
                    if ($this->teacher->updateTeacher($id, $inputData)) {
                        $_SESSION['success'] = "Teacher updated successfully";
                        header('Location: /admin/teachers');
                        exit;
                    }
                } catch (\PDOException $e) {
                    $errors['database'] = "Database error: " . $e->getMessage();
                }
            } else {
                $errors = $this->validator->getErrors();
            }
        }
        
        require __DIR__ . '/../views/admin/teachers/edit.php';
    }

    public function viewAuditLogs() {
        AuthHelper::requireRole('admin');
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get audit logs with pagination
        $logs = $this->getAuditLogsWithPagination($offset, $perPage);
        $totalLogs = $this->getAuditLogsCount();
        $totalPages = ceil($totalLogs / $perPage);
        
        require __DIR__ . '/../views/admin/audit/index.php';
    }

    public function viewEntityAuditTrail($entityType, $entityId) {
        AuthHelper::requireRole('admin');
        
        $logs = AuditLogger::getAuditTrail($entityType, $entityId);
        
        // Get entity details
        $entity = $this->getEntityDetails($entityType, $entityId);
        if (!$entity) {
            header('Location: /admin/audit');
            exit;
        }
        
        require __DIR__ . '/../views/admin/audit/entity-trail.php';
    }

    private function getAuditLogsWithPagination($offset, $limit) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT al.*, 
                CONCAT(UPPER(SUBSTRING(al.entity_type, 1, 1)), LOWER(SUBSTRING(al.entity_type, 2))) as entity_type_formatted,
                DATE_FORMAT(al.created_at, '%Y-%m-%d %H:%i:%s') as formatted_date
                FROM audit_logs al
                ORDER BY al.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    private function getAuditLogsCount() {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as count FROM audit_logs";
        return $db->query($sql)->fetch()['count'];
    }

    private function getEntityDetails($entityType, $entityId) {
        $db = Database::getInstance()->getConnection();
        
        switch ($entityType) {
            case 'teacher':
                $sql = "SELECT teacher_id as id, name, matricule FROM teachers WHERE teacher_id = ?";
                break;
            case 'pupil':
                $sql = "SELECT pupil_id as id, CONCAT(first_name, ' ', last_name) as name, matricule FROM pupils WHERE pupil_id = ?";
                break;
            default:
                return null;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$entityId]);
        return $stmt->fetch();
    }

    public function updateSettings() {
        AuthHelper::requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('admin/settings'));
            exit;
        }

        try {
            $db = \App\Config\Database::getInstance()->getConnection();
            
            // Validate and sanitize input
            $academicYear = filter_input(INPUT_POST, 'academic_year', FILTER_VALIDATE_INT);
            $currentTerm = filter_input(INPUT_POST, 'current_term', FILTER_VALIDATE_INT);
            $schoolName = SanitizationHelper::sanitize($_POST['school_name']);
            $resultsPerPage = filter_input(INPUT_POST, 'results_per_page', FILTER_VALIDATE_INT);

            // Start transaction
            $db->beginTransaction();

            // Update settings
            $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            
            $stmt = $db->prepare($sql);
            
            // Update each setting
            $settings = [
                ['academic_year', $academicYear],
                ['current_term', $currentTerm],
                ['results_per_page', $resultsPerPage]
            ];

            foreach ($settings as [$key, $value]) {
                $stmt->execute([$key, $value]);
            }

            $db->commit();
            ErrorHandler::setSuccess("Settings updated successfully");
            
        } catch (\PDOException $e) {
            $db->rollBack();
            ErrorHandler::logError("Failed to update settings", [
                'error' => $e->getMessage()
            ]);
            ErrorHandler::setError('update', 'Failed to update settings');
        }
        
        // Redirect back to settings page
        header('Location: ' . url('admin/settings'));
        exit;
    }

    public function addPupil() {
        AuthHelper::requireRole('admin');
        $errors = [];
        
        // Get active parents for dropdown
        $parentModel = new \App\Models\ParentModel();
        $parents = $parentModel->getAllActiveParents();
        
        // Get class list
        $pupilModel = new \App\Models\Pupil();
        $classList = $pupilModel->getClassList();
        
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
                'class' => ['required', ['in', array_keys($classList)]]
            ];

            if ($this->validator->validate($sanitizedData, $rules)) {
                try {
                    $pupilId = $pupilModel->createPupil($sanitizedData);
                    if ($pupilId) {
                        ErrorHandler::setSuccess("Pupil registered successfully");
                        header('Location: ' . url('admin/pupils'));
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

    public function addParent() {
        AuthHelper::requireRole('admin');
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sanitizedData = [
                'username' => SanitizationHelper::sanitize($_POST['username']),
                'email' => SanitizationHelper::sanitizeEmail($_POST['email']),
                'phone' => SanitizationHelper::sanitizePhone($_POST['phone']),
                'password' => $_POST['password']
            ];

            $rules = [
                'username' => ['required', ['min', 3], ['max', 50], ['unique', 'parents', 'username']],
                'email' => ['required', 'email', ['unique', 'parents', 'email']],
                'phone' => ['required', 'phone'],
                'password' => ['required', ['min', 6]]
            ];

            if ($this->validator->validate($sanitizedData, $rules)) {
                try {
                    $parentModel = new \App\Models\ParentModel();
                    $matricule = 'PAR' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    
                    $parentData = [
                        'matricule' => $matricule,
                        'username' => $sanitizedData['username'],
                        'email' => $sanitizedData['email'],
                        'phone' => $sanitizedData['phone'],
                        'password' => AuthHelper::hashPassword($sanitizedData['password'])
                    ];

                    if ($parentModel->createParent($parentData)) {
                        ErrorHandler::setSuccess("Parent added successfully. Matricule: {$matricule}");
                        header('Location: ' . url('admin/parents'));
                        exit;
                    }
                } catch (\PDOException $e) {
                    ErrorHandler::logError("Failed to create parent", [
                        'error' => $e->getMessage(),
                        'data' => $sanitizedData
                    ]);
                    $errors['database'] = "Failed to create parent. Please try again.";
                }
            } else {
                $errors = $this->validator->getErrors();
            }
        }
        
        require __DIR__ . '/../views/admin/parents/add.php';
    }

    public function viewPupil($id) {
        AuthHelper::requireRole('admin');
        
        try {
            $pupilModel = new \App\Models\Pupil();
            $pupil = $pupilModel->getPupilById($id);
            
            if (!$pupil) {
                ErrorHandler::setError('view', 'Pupil not found');
                header('Location: ' . url('admin/pupils'));
                exit;
            }
            
            // Get parent details
            $parentModel = new \App\Models\ParentModel();
            $parent = $parentModel->getParentById($pupil['parent_id']);
            
            require __DIR__ . '/../views/admin/pupils/view.php';
            
        } catch (\PDOException $e) {
            ErrorHandler::logError("Failed to load pupil details", [
                'error' => $e->getMessage(),
                'pupil_id' => $id
            ]);
            ErrorHandler::setError('view', 'Failed to load pupil details');
            header('Location: ' . url('admin/pupils'));
            exit;
        }
    }

    public function editPupil($id) {
        AuthHelper::requireRole('admin');
        $errors = [];
        
        try {
            // Get pupil data
            $pupilModel = new \App\Models\Pupil();
            $pupil = $pupilModel->getPupilById($id);
            
            if (!$pupil) {
                ErrorHandler::setError('edit', 'Pupil not found');
                header('Location: ' . url('admin/pupils'));
                exit;
            }
            
            // Get active parents for dropdown
            $parentModel = new \App\Models\ParentModel();
            $parents = $parentModel->getAllActiveParents();
            
            // Get class list
            $classList = $pupilModel->getClassList();
            
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
                    'class' => ['required', ['in', array_keys($classList)]],
                    'status' => ['required', ['in', ['active', 'inactive']]]
                ];

                if ($this->validator->validate($sanitizedData, $rules)) {
                    try {
                        if ($pupilModel->updatePupil($id, $sanitizedData)) {
                            ErrorHandler::setSuccess("Pupil updated successfully");
                            header('Location: ' . url('admin/pupils/view/' . $id));
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
            
        } catch (\PDOException $e) {
            ErrorHandler::logError("Failed to load pupil edit form", [
                'error' => $e->getMessage(),
                'pupil_id' => $id
            ]);
            ErrorHandler::setError('edit', 'Failed to load pupil data');
            header('Location: ' . url('admin/pupils'));
            exit;
        }
    }

    public function markingPeriods() {
        AuthHelper::requireRole('admin');
        
        try {
            $markingPeriod = new \App\Models\MarkingPeriod();
            $periods = $markingPeriod->getAllPeriods();
            require __DIR__ . '/../views/admin/marking-periods/index.php';
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to load marking periods", [
                'error' => $e->getMessage()
            ]);
            $error = "Failed to load marking periods";
            require __DIR__ . '/../views/admin/marking-periods/index.php';
        }
    }

    public function addMarkingPeriod() {
        AuthHelper::requireRole('admin');
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'academic_year' => $_POST['academic_year'],
                    'term' => (int)$_POST['term'],
                    'sequence' => (int)$_POST['sequence'],
                    'start_date' => $_POST['start_date'],
                    'end_date' => $_POST['end_date'],
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                
                $markingPeriod = new \App\Models\MarkingPeriod();
                if ($markingPeriod->createPeriod($data)) {
                    ErrorHandler::setSuccess("Marking period created successfully");
                    header('Location: ' . url('admin/marking-periods'));
                    exit;
                }
            } catch (\Exception $e) {
                ErrorHandler::logError("Failed to create marking period", [
                    'error' => $e->getMessage(),
                    'data' => $data ?? null
                ]);
                $errors[] = "Failed to create marking period";
            }
        }
        
        require __DIR__ . '/../views/admin/marking-periods/add.php';
    }

    public function editMarkingPeriod($id) {
        AuthHelper::requireRole('admin');
        $errors = [];
        
        try {
            $markingPeriod = new \App\Models\MarkingPeriod();
            $period = $markingPeriod->getPeriodById($id);
            
            if (!$period) {
                ErrorHandler::setError('edit', 'Marking period not found');
                header('Location: ' . url('admin/marking-periods'));
                exit;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'academic_year' => $_POST['academic_year'],
                    'term' => (int)$_POST['term'],
                    'sequence' => (int)$_POST['sequence'],
                    'start_date' => $_POST['start_date'],
                    'end_date' => $_POST['end_date'],
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                
                if ($markingPeriod->updatePeriod($id, $data)) {
                    ErrorHandler::setSuccess("Marking period updated successfully");
                    header('Location: ' . url('admin/marking-periods'));
                    exit;
                }
            }
            
            require __DIR__ . '/../views/admin/marking-periods/edit.php';
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to load/update marking period", [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            header('Location: ' . url('admin/marking-periods'));
            exit;
        }
    }

    public function reportCards() {
        AuthHelper::requireRole('admin');
        
        try {
            // Get filter parameters
            $class = $_GET['class'] ?? '';
            $academicYear = $_GET['academic_year'] ?? date('Y');
            $term = $_GET['term'] ?? 1;
            
            // Get list of all classes for the filter dropdown
            $classList = $this->pupil->getClassList();
            
            // Get pupils only if a class is selected
            $pupils = [];
            if (!empty($class)) {
                // Get active pupils from the selected class
                $pupils = $this->pupil->getActiveStudentsByClass($class);
                
                // For each pupil, get their term average and position
                foreach ($pupils as &$pupil) {
                    // Get results for this pupil
                    $results = $this->result->getPupilResults(
                        $pupil['pupil_id'], 
                        $academicYear, 
                        $term
                    );
                    
                    // Calculate term average
                    if (!empty($results)) {
                        $totalWeightedMarks = 0;
                        $totalCoefficients = 0;
                        
                        foreach ($results as $result) {
                            $average = ($result['first_sequence_marks'] + $result['second_sequence_marks'] + $result['exam_marks']) / 3;
                            $totalWeightedMarks += ($average * $result['coefficient']);
                            $totalCoefficients += $result['coefficient'];
                        }
                        
                        $pupil['term_average'] = $totalCoefficients > 0 ? 
                            ($totalWeightedMarks / $totalCoefficients) : 0;
                    } else {
                        $pupil['term_average'] = 0;
                    }
                    
                    // Get pupil's position
                    $pupil['position'] = $this->result->getPupilPosition(
                        $pupil['pupil_id'], 
                        $class, 
                        $academicYear, 
                        $term
                    );
                }
                unset($pupil); // Break the reference
            }
            
            require __DIR__ . '/../views/admin/report-cards/index.php';
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to load report cards page", [
                'error' => $e->getMessage()
            ]);
            $error = "Failed to load report cards";
            require __DIR__ . '/../views/admin/report-cards/index.php';
        }
    }
} 