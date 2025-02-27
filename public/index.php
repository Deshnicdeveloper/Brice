<?php

error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Cleaned Path: " . $request);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
\App\Config\Config::loadEnv();

use App\Config\Config;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Helpers\RequestLogger;
use App\Controllers\ApiController;
use App\Controllers\SubjectController;
use App\Controllers\ReportCardController;
use App\Controllers\TeacherController;
use App\Controllers\AttendanceController;

// Add this near the top, after require_once autoload
session_start();

// Function to clean request path
function cleanRequestPath($request) {
    // Remove base path and trailing slashes
    $basePath = '/Brice';
    $cleanPath = str_replace($basePath, '', $request);
    $cleanPath = trim($cleanPath, '/');
    
    // Return / for home page, otherwise prepend / to path
    return $cleanPath === '' ? '/' : '/' . $cleanPath;
}

// Function to generate URLs with correct base path
function url($path = '') {
    $basePath = '/Brice';
    return $basePath . '/' . ltrim($path, '/');
}

// Initialize application
Config::init();
RequestLogger::startRequest();

// Create controller instances
$auth = new AuthController();
$admin = new AdminController();
$result = new ApiController();
$subject = new SubjectController();
$teacher = new TeacherController();

// Basic routing (we'll improve this later)
$request = cleanRequestPath(strtok($_SERVER['REQUEST_URI'], '?'));

// Add this debug line at the top of the switch statement
error_log("Current request path: " . $request);

try {
    switch ($request) {
        case '/':
            // Show landing page
            if (isset($_SESSION['user_id'])) {
                header('Location: ' . url('admin/dashboard'));
                exit;
            }
            require __DIR__ . '/../app/views/landing.php';
            break;
        case '/login':
            $auth->login();
            break;
        case '/logout':
            $auth->logout();
            break;
        case '/admin/dashboard':
            $admin->dashboard();
            break;
        case '/admin/class-roster':
            $admin->classRoster();
            break;
        case '/admin/teachers':
            $admin->teachers();
            break;
        case '/admin/teachers/add':
            $admin->addTeacher();
            break;
        case (preg_match('/^\/admin\/teachers\/edit\/(\d+)$/', $request, $matches) ? true : false):
            $admin->editTeacher($matches[1]);
            break;
        case '/admin/pupils':
            $admin->pupils();
            break;
        case (preg_match('/^\/admin\/pupils\/view\/(\d+)$/', $request, $matches) ? true : false):
            error_log("Matched pupil view route with ID: " . $matches[1]);
            $admin->viewPupil($matches[1]);
            break;
        case (preg_match('/^\/admin\/pupils\/edit\/(\d+)$/', $request, $matches) ? true : false):
            $admin->editPupil($matches[1]);
            break;
        case '/admin/pupils/add':
            $admin->addPupil();
            break;
        case '/admin/parents':
            $admin->parents();
            break;
        case '/admin/parents/add':
            $admin->addParent();
            break;
        case (preg_match('/^\/admin\/parents\/edit\/(\d+)$/', $request, $matches) ? true : false):
            $admin->editParent($matches[1]);
            break;
        case (preg_match('/^\/admin\/parents\/view\/(\d+)$/', $request, $matches) ? true : false):
            $admin->viewParent($matches[1]);
            break;
        case '/admin/subjects':
            $subject->index();
            break;
        case '/admin/subjects/add':
            $subject->add();
            break;
        case (preg_match('/^\/admin\/subjects\/edit\/(\d+)$/', $request, $matches) ? true : false):
            $subject->edit($matches[1]);
            break;
        case (preg_match('/^\/admin\/subjects\/delete\/(\d+)$/', $request, $matches) ? true : false):
            $subject = new SubjectController();
            $subject->delete($matches[1]);
            break;
        case '/admin/settings':
            $admin->settings();
            break;
        case '/admin/settings/update':
            $admin->updateSettings();
            break;
        case '/admin/audit':
            $admin->viewAuditLogs();
            break;
        case (preg_match('/^\/admin\/audit\/(\w+)\/(\d+)$/', $request, $matches) ? true : false):
            $admin->viewEntityAuditTrail($matches[1], $matches[2]);
            break;
        case '/admin/results/enter':
            $result->enterResults();
            break;
        case '/api/results/get-class-data':
            $api = new ApiController();
            $api->getClassData();
            break;
        case '/admin/report-cards':
            $reportCard = new ReportCardController();
            $reportCard->index();
            break;
        case '/admin/report-cards/archives':
            $reportCard = new ReportCardController();
            $reportCard->archives();
            break;
        case (preg_match('/^\/admin\/report-cards\/archives\/download\/(\d+)$/', $request, $matches) ? true : false):
            $reportCard = new ReportCardController();
            $reportCard->downloadArchive($matches[1]);
            break;
        case (preg_match('/^\/admin\/report-cards\/generate\/(\d+)$/', $request, $matches) ? true : false):
            $reportCard = new ReportCardController();
            $reportCard->generate($matches[1]);
            break;
        case (preg_match('/^\/admin\/report-cards$/', $request) ? true : false):
            $reportCard = new ReportCardController();
            $reportCard->index();
            break;
        case '/admin/marking-periods':
            $admin->markingPeriods();
            break;
        case '/admin/marking-periods/add':
            $admin->addMarkingPeriod();
            break;
        case (preg_match('/^\/admin\/marking-periods\/edit\/(\d+)$/', $request, $matches) ? true : false):
            $admin->editMarkingPeriod($matches[1]);
            break;
        case '/teacher/dashboard':
            $teacher->index();
            break;
        case '/teacher/class-roster':
            $teacher->classRoster();
            break;
        case '/teacher/record-marks':
            $teacher->recordMarks();
            break;
        case (preg_match('/^\/teacher\/record-marks\/(\d+)$/', $request, $matches) ? true : false):
            $teacher->recordMarks($matches[1]);
            break;
        case (preg_match('/^\/teacher\/record-marks\/(\d+)\/([^\/]+)$/', $request, $matches) ? true : false):
            $teacher->recordMarks($matches[1], $matches[2]);
            break;
        case (preg_match('/^\/report-card\/view\/(\d+)(?:\/(\d+))?$/', $request, $matches) ? true : false):
            $reportCard = new ReportCardController();
            $reportCard->view($matches[1], $matches[2] ?? null);
            break;
        case (preg_match('/^\/report-card\/download\/(\d+)\/(\d+)$/', $request, $matches) ? true : false):
            $reportCard = new ReportCardController();
            $reportCard->download($matches[1], $matches[2]);
            break;
        case (preg_match('/^\/report-card\/print\/(\d+)\/(\d+)$/', $request, $matches) ? true : false):
            $reportCard = new ReportCardController();
            $reportCard->print($matches[1], $matches[2]);
            break;
        case '/attendance':
            (new AttendanceController())->index();
            break;
        case (preg_match('/^\/attendance\/record$/', $request) ? true : false):
            (new AttendanceController())->record();
            break;
        case (preg_match('/^\/attendance\/view\/(\d+)$/', $request, $matches) ? true : false):
            (new AttendanceController())->viewPupilAttendance($matches[1]);
            break;
        default:
            http_response_code(404);
            require __DIR__ . '/../app/views/errors/404.php';
            break;
    }
    RequestLogger::logRequest(http_response_code());
} catch (\Exception $e) {
    ErrorHandler::logError("Unhandled exception", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    http_response_code(500);
    require __DIR__ . '/../app/views/errors/500.php';
    RequestLogger::logRequest(500);
} 