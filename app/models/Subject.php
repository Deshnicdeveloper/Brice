<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\ErrorHandler;
use App\Helpers\AuditLogger;

class Subject {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllSubjects() {
        try {
            $sql = "SELECT * FROM subjects ORDER BY class, name";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getAllSubjects: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSubjectsByClass($class) {
        try {
            $sql = "SELECT * FROM subjects 
                    WHERE class = ? 
                    ORDER BY name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$class]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getSubjectsByClass: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSubjectById($id) {
        try {
            ErrorHandler::logError("Debug - Getting subject by ID: " . $id);
            
            $sql = "SELECT subject_id, name, code, class, coefficient, category 
                    FROM subjects 
                    WHERE subject_id = ? 
                    LIMIT 1";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$id]);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$result) {
                ErrorHandler::logError("Debug - No subject found with ID: " . $id);
                return null;
            }
            
            ErrorHandler::logError("Debug - Found subject: " . json_encode($result));
            return $result;
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getSubjectById", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function createSubject($data) {
        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO subjects (name, code, class, coefficient, category) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['name'],
                $data['code'],
                $data['class'],
                $data['coefficient'],
                $data['category']
            ]);

            if ($result) {
                $subjectId = $this->db->lastInsertId();
                AuditLogger::log('create', 'subject', $subjectId, null, $data);
                $this->db->commit();
                return $subjectId;
            }

            $this->db->rollBack();
            return false;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateSubject($id, $data) {
        try {
            $this->db->beginTransaction();
            
            $oldData = $this->getSubjectById($id);
            
            $sql = "UPDATE subjects 
                    SET name = ?, code = ?, class = ?, coefficient = ?, category = ? 
                    WHERE subject_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['name'],
                $data['code'],
                $data['class'],
                $data['coefficient'],
                $data['category'],
                $id
            ]);

            if ($result) {
                AuditLogger::log('update', 'subject', $id, $oldData, $data);
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteSubject($id) {
        try {
            $this->db->beginTransaction();

            $oldData = $this->getSubjectById($id);
            
            // Check if subject is being used in results
            $sql = "SELECT COUNT(*) as count FROM results WHERE subject_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            if ($stmt->fetch()['count'] > 0) {
                throw new \Exception("Cannot delete subject that has associated results");
            }

            $sql = "DELETE FROM subjects WHERE subject_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);

            if ($result) {
                AuditLogger::log('delete', 'subject', $id, $oldData, null);
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            ErrorHandler::logError("Database error in deleteSubject: " . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getCategories() {
        return [
            'core' => 'Core Subject',
            'elective' => 'Elective Subject',
            'optional' => 'Optional Subject'
        ];
    }

    public function getTeacherSubjects($teacherId) {
        try {
            $sql = "SELECT s.*, ts.class 
                    FROM subjects s 
                    JOIN teacher_subjects ts ON s.subject_id = ts.subject_id 
                    WHERE ts.teacher_id = ?
                    ORDER BY s.name, ts.class";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$teacherId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getTeacherSubjects: " . $e->getMessage());
            throw $e;
        }
    }
} 