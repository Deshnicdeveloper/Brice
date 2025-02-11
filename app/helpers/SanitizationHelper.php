<?php

namespace App\Helpers;

class SanitizationHelper {
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    public static function sanitizeEmail($email) {
        return filter_var(self::sanitize($email), FILTER_SANITIZE_EMAIL);
    }

    public static function sanitizePhone($phone) {
        // Remove everything except digits, +, -, (, ), and spaces
        return preg_replace('/[^0-9+\-\s()]/', '', $phone);
    }

    public static function sanitizeName($name) {
        // Allow letters, spaces, and basic punctuation
        return preg_replace('/[^a-zA-Z\s\'-]/', '', self::sanitize($name));
    }
} 