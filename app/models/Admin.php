<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\ErrorHandler;

class Admin {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAdminByUsername($username) {
        try {
            $sql = "SELECT * FROM admins WHERE username = ? AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getAdminByUsername: " . $e->getMessage());
            throw $e;
        }
    }
} 