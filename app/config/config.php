<?php

namespace App\Config;

class Config {
    public static function init() {
        // Load environment variables
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();

        // Set error reporting based on environment
        if ($_ENV['APP_ENV'] === 'development') {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
        }

        // Set timezone
        date_default_timezone_set('Africa/Douala');

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function loadEnv() {
        // Load environment variables from .env file
        if (file_exists(__DIR__ . '/../../.env')) {
            $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if they exist
                    if (strpos($value, '"') === 0) {
                        $value = trim($value, '"');
                    }
                    
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }

        // Set default values if not set
        $defaults = [
            'SCHOOL_NAME' => 'School Management System',
            'SCHOOL_ADDRESS' => 'School Address',
            'SCHOOL_PHONE' => 'School Phone',
            'SCHOOL_EMAIL' => 'School Email'
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
} 