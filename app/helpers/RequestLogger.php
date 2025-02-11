<?php

namespace App\Helpers;

use App\Config\Database;

class RequestLogger {
    private static $startTime;
    private static $db;

    public static function startRequest() {
        self::$startTime = microtime(true);
    }

    private static function getDb() {
        if (!self::$db) {
            self::$db = Database::getInstance()->getConnection();
        }
        return self::$db;
    }

    public static function logRequest($responseCode = 200) {
        try {
            $executionTime = microtime(true) - self::$startTime;
            $db = self::getDb();

            $sql = "INSERT INTO request_logs (user_id, method, url, ip_address, user_agent, 
                    request_data, response_code, execution_time) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $requestData = [
                'get' => $_GET,
                'post' => $_POST,
                'files' => !empty($_FILES)
            ];

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                $_SERVER['REQUEST_METHOD'],
                $_SERVER['REQUEST_URI'],
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                json_encode($requestData),
                $responseCode,
                $executionTime
            ]);

            return true;
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to log request", [
                'error' => $e->getMessage(),
                'url' => $_SERVER['REQUEST_URI']
            ]);
            return false;
        }
    }
} 