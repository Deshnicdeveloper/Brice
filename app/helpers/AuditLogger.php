<?php

namespace App\Helpers;

use App\Config\Database;

class AuditLogger {
    private static $db;

    private static function getDb() {
        if (!self::$db) {
            self::$db = Database::getInstance()->getConnection();
        }
        return self::$db;
    }

    public static function log($action, $entityType, $entityId, $oldValues = null, $newValues = null) {
        try {
            $db = self::getDb();
            $sql = "INSERT INTO audit_logs (user_id, user_type, action, entity_type, entity_id, 
                    old_values, new_values, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                $_SESSION['user_type'] ?? null,
                $action,
                $entityType,
                $entityId,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            return true;
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to create audit log", [
                'error' => $e->getMessage(),
                'action' => $action,
                'entityType' => $entityType
            ]);
            return false;
        }
    }

    public static function getAuditTrail($entityType, $entityId) {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM audit_logs 
                    WHERE entity_type = ? AND entity_id = ? 
                    ORDER BY created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute([$entityType, $entityId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to retrieve audit trail", [
                'error' => $e->getMessage(),
                'entityType' => $entityType,
                'entityId' => $entityId
            ]);
            return [];
        }
    }
} 