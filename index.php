<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Config;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Helpers\RequestLogger;
use App\Controllers\ApiController;
use App\Controllers\SubjectController;
use App\Controllers\ReportCardController;

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

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize application
Config::init();
RequestLogger::startRequest();

// Create controller instances
$auth = new AuthController();
$admin = new AdminController();
$result = new ApiController();

// Get the request path
$request = cleanRequestPath(strtok($_SERVER['REQUEST_URI'], '?'));

try {
    switch ($request) {
        case '/':
            // Show landing page
            if (isset($_SESSION['user_id'])) {
                header('Location: ' . url('admin/dashboard'));
                exit;
            }
            require __DIR__ . '/app/views/landing.php';
            break;
            
        case '/login':
            $auth->login();
            break;
            
        case '/logout':
            $auth->logout();
            break;
            
        case '/admin/dashboard':
            AuthHelper::requireRole(['admin']);
            $admin->dashboard();
            break;
            
        // ... rest of your routes ...
        
        default:
            http_response_code(404);
            require __DIR__ . '/app/views/errors/404.php';
            break;
    }
    
    RequestLogger::logRequest(http_response_code());
} catch (\Exception $e) {
    ErrorHandler::logError("Unhandled exception", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    http_response_code(500);
    require __DIR__ . '/app/views/errors/500.php';
    RequestLogger::logRequest(500);
} 