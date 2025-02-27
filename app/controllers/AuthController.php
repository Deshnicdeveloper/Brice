<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Teacher;
use App\Models\ParentModel;
use App\Models\Admin;
use App\Models\Pupil;
use App\Helpers\AuthHelper;
use App\Helpers\ErrorHandler;

class AuthController {
    private $user;
    private $teacher;
    private $parentModel;
    private $admin;
    private $pupil;

    public function __construct() {
        $this->user = new User();
        $this->teacher = new Teacher();
        $this->parentModel = new ParentModel();
        $this->admin = new Admin();
        $this->pupil = new Pupil();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $role = $_POST['role'] ?? 'admin';

            try {
                switch ($role) {
                    case 'teacher':
                        $matricule = $_POST['matricule'] ?? '';
                        $pin = $_POST['pin'] ?? '';
                        $user = $this->teacher->getTeacherByMatricule($matricule);
                        
                        if ($user && $pin === $user['pin']) {
                            $_SESSION['user_id'] = $user['teacher_id'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['user_type'] = 'teacher';
                            $_SESSION['assigned_class'] = $user['assigned_class'];
                            header('Location: ' . url('teacher/dashboard'));
                            exit;
                        }
                        break;

                    case 'parent':
                        $username = $_POST['username'] ?? '';
                        $password = $_POST['password'] ?? '';
                        $user = $this->parentModel->getParentByUsername($username);
                        
                        if ($user && password_verify($password, $user['password'])) {
                            // Get pupils associated with this parent
                            $pupils = $this->pupil->getPupilsByParentId($user['parent_id']);
                            $pupilIds = array_column($pupils, 'pupil_id');
                            
                            $_SESSION['user_id'] = $user['parent_id'];
                            $_SESSION['user_name'] = $user['username'];
                            $_SESSION['user_type'] = 'parent';
                            $_SESSION['pupil_ids'] = $pupilIds;
                            header('Location: ' . url('parent/dashboard'));
                            exit;
                        }
                        break;

                    case 'admin':
                        $username = $_POST['username'] ?? '';
                        $password = $_POST['password'] ?? '';
                        $user = $this->admin->getAdminByUsername($username);
                        
                        if ($user && password_verify($password, $user['password'])) {
                            $_SESSION['user_id'] = $user['admin_id'];
                            $_SESSION['user_name'] = $user['username'];
                            $_SESSION['user_type'] = 'admin';
                            header('Location: ' . url('admin/dashboard'));
                            exit;
                        }
                        break;
                }

                ErrorHandler::setError('login', 'Invalid credentials');
            } catch (\Exception $e) {
                ErrorHandler::logError("Login failed", [
                    'error' => $e->getMessage(),
                    'role' => $role
                ]);
                ErrorHandler::setError('login', 'Login failed. Please try again.');
            }
        }

        $role = $_GET['role'] ?? 'admin';
        require __DIR__ . '/../views/auth/login.php';
    }

    private function redirectToDashboard() {
        $route = match($_SESSION['user_type']) {
            'admin' => '/admin/dashboard',
            'teacher' => '/teacher/dashboard',
            'parent' => '/parent/dashboard',
            default => '/login'
        };
        header("Location: $route");
        exit;
    }

    public function logout() {
        session_destroy();
        header('Location: ' . url('login'));
        exit;
    }
} 