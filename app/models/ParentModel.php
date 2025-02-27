<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\ErrorHandler;
use App\Helpers\AuditLogger;

class ParentModel {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllParents() {
        $sql = "SELECT * FROM parents ORDER BY username ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getAllActiveParents() {
        $sql = "SELECT * FROM parents WHERE status = 'active' ORDER BY username ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getParentById($id) {
        $sql = "SELECT * FROM parents WHERE parent_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createParent($data) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO parents (matricule, username, password, email, phone, status) 
                    VALUES (?, ?, ?, ?, ?, 'active')";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['matricule'],
                $data['username'],
                $data['password'],
                $data['email'],
                $data['phone']
            ]);

            if ($result) {
                $parentId = $this->db->lastInsertId();
                AuditLogger::log('create', 'parent', $parentId, null, $data);
                $this->db->commit();
                return $parentId;
            }

            $this->db->rollBack();
            return false;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateParent($id, $data) {
        try {
            $sql = "UPDATE parents SET 
                    username = ?, 
                    email = ?, 
                    phone = ?,
                    status = ?
                    WHERE parent_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['username'],
                $data['email'],
                $data['phone'],
                $data['status'],
                $id
            ]);
        } catch (\PDOException $e) {
            ErrorHandler::logError("Failed to update parent", [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    public function getParentByUsername($username) {
        try {
            $sql = "SELECT * FROM parents WHERE username = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getParentByUsername: " . $e->getMessage());
            throw $e;
        }
    }
} 