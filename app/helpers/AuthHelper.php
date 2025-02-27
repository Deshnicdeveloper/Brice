<?php

namespace App\Helpers;

class AuthHelper {
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
    }

    public static function requireAuth() {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole($roles) {
        self::requireAuth();
        $roles = (array) $roles;
        if (!in_array($_SESSION['user_type'], $roles)) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }

    public static function getRole() {
        return $_SESSION['user_type'] ?? null;
    }

    public static function getTeacherClass() {
        if (self::getRole() !== 'teacher') {
            return null;
        }
        return $_SESSION['assigned_class'] ?? null;
    }

    public static function getParentPupils() {
        if (self::getRole() !== 'parent') {
            return [];
        }
        return $_SESSION['pupil_ids'] ?? [];
    }
} 