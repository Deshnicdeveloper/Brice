<?php

namespace App\Helpers;

class ErrorHandler {
    private static $errors = [];
    private static $successMessage = null;

    public static function setError($key, $message) {
        self::$errors[$key] = $message;
    }

    public static function getErrors() {
        return self::$errors;
    }

    public static function hasErrors() {
        return !empty(self::$errors);
    }

    public static function getFirstError() {
        return reset(self::$errors) ?: null;
    }

    public static function setSuccess($message) {
        self::$successMessage = $message;
        $_SESSION['success'] = $message;
    }

    public static function getSuccess() {
        $message = $_SESSION['success'] ?? null;
        unset($_SESSION['success']);
        return $message;
    }

    public static function logError($error, $context = []) {
        $logDir = __DIR__ . '/../../logs';
        $logFile = $logDir . '/error.log';

        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logMessage = date('Y-m-d H:i:s') . " ERROR: " . $error . "\n";
        if (!empty($context)) {
            $logMessage .= "Context: " . json_encode($context) . "\n";
        }
        error_log($logMessage, 3, $logFile);
    }
} 