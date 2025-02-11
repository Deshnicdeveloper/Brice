<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\AuthHelper;

class User {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function authenticate($matricule, $password) {
        // Try to find user in admins table first
        $sql = "SELECT * FROM admins WHERE matricule = ? AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$matricule]);
        $user = $stmt->fetch();

        if ($user && AuthHelper::verifyPassword($password, $user['password'])) {
            $_SESSION['user_type'] = 'admin';
            return $user;
        }

        return null;
    }

    public function getUserType($matricule) {
        $tables = ['admin', 'teacher', 'parent'];
        foreach ($tables as $type) {
            $table = $type . 's';
            $sql = "SELECT 1 FROM $table WHERE matricule = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$matricule]);
            if ($stmt->fetch()) {
                return $type;
            }
        }
        return null;
    }
} 