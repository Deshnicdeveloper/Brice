<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\AuthHelper;
use App\Helpers\ErrorHandler;
use App\Helpers\AuditLogger;

class Teacher {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllTeachers() {
        $sql = "SELECT * FROM teachers ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getTeacherById($id) {
        try {
            $sql = "SELECT * FROM teachers WHERE teacher_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getTeacherById: " . $e->getMessage());
            throw $e;
        }
    }

    public function createTeacher($data) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO teachers (matricule, pin, name, email, phone, assigned_class, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'active')";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['matricule'],
                $data['pin'],
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['assigned_class']
            ]);

            if ($result) {
                $teacherId = $this->db->lastInsertId();
                AuditLogger::log('create', 'teacher', $teacherId, null, $data);
                $this->db->commit();
                return $teacherId;
            }

            $this->db->rollBack();
            return false;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateTeacher($id, $data) {
        try {
            $this->db->beginTransaction();

            // Get old values for audit log
            $oldData = $this->getTeacherById($id);

            $sql = "UPDATE teachers 
                    SET name = ?, email = ?, phone = ?, assigned_class = ?, status = ?
                    WHERE teacher_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['assigned_class'],
                $data['status'],
                $id
            ]);

            if ($result) {
                AuditLogger::log('update', 'teacher', $id, $oldData, $data);
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            ErrorHandler::logError("Database error in updateTeacher: " . $e->getMessage(), [
                'id' => $id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function generateMatricule() {
        // Get the last teacher ID and create a new matricule
        $sql = "SELECT MAX(CAST(SUBSTRING(matricule, 4) AS UNSIGNED)) as last_num 
                FROM teachers WHERE matricule LIKE 'TCH%'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        
        $nextNum = ($result['last_num'] ?? 0) + 1;
        return 'TCH' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    public function generatePin() {
        // Generate a random 8-digit PIN
        return str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    public function getTeacherByUsername($username) {
        try {
            $sql = "SELECT * FROM teachers WHERE username = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getTeacherByUsername: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTeacherByMatricule($matricule) {
        try {
            $sql = "SELECT * FROM teachers WHERE matricule = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$matricule]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getTeacherByMatricule: " . $e->getMessage());
            throw $e;
        }
    }
} 