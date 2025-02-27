<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\ErrorHandler;
use App\Helpers\AuditLogger;

class Pupil {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllPupils() {
        try {
            $sql = "SELECT p.pupil_id, p.matricule, p.first_name, p.last_name, 
                           p.class, p.status, p.parent_id,
                           pr.username as parent_name 
                    FROM pupils p 
                    LEFT JOIN parents pr ON p.parent_id = pr.parent_id 
                    ORDER BY p.class, p.first_name, p.last_name";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getAllPupils: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPupilsByClass($class) {
        $sql = "SELECT p.*, pr.username as parent_name 
                FROM pupils p 
                LEFT JOIN parents pr ON p.parent_id = pr.parent_id 
                WHERE p.class = ? 
                ORDER BY p.first_name, p.last_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$class]);
        return $stmt->fetchAll();
    }

    public function createPupil($data) {
        try {
            $this->db->beginTransaction();

            // Generate matricule
            $matricule = 'PUP' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

             // Convert class ID to class name
            $data['class'] = $classList[$data['class']] ?? $data['class']; 
            
            $sql = "INSERT INTO pupils (matricule, first_name, last_name, date_of_birth, 
                                      gender, parent_id, class, admission_date, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), 'active')";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $matricule,
                $data['first_name'],
                $data['last_name'],
                $data['date_of_birth'],
                $data['gender'],
                $data['parent_id'],
                $data['class']
            ]);

            if ($result) {
                $pupilId = $this->db->lastInsertId();
                AuditLogger::log('create', 'pupil', $pupilId, null, $data);
                $this->db->commit();
                return $pupilId;
            }

            $this->db->rollBack();
            return false;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updatePupil($id, $data) {
        try {
            $this->db->beginTransaction();

            $oldData = $this->getPupilById($id);
            
            $sql = "UPDATE pupils 
                    SET first_name = ?, last_name = ?, date_of_birth = ?, 
                        gender = ?, parent_id = ?, class = ?, status = ? 
                    WHERE pupil_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['date_of_birth'],
                $data['gender'],
                $data['parent_id'],
                $data['class'],
                $data['status'],
                $id
            ]);

            if ($result) {
                AuditLogger::log('update', 'pupil', $id, $oldData, $data);
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            ErrorHandler::logError("Database error in updatePupil: " . $e->getMessage(), [
                'id' => $id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getPupilById($id) {
        try {
            $sql = "SELECT p.pupil_id, p.matricule, p.first_name, p.last_name,
                           p.date_of_birth, p.gender, p.parent_id, p.class,
                           p.status, p.admission_date,
                           pr.username as parent_name 
                    FROM pupils p 
                    LEFT JOIN parents pr ON p.parent_id = pr.parent_id 
                    WHERE p.pupil_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if (!$result) {
                return null;
            }
            
            return $result;
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getPupilById: " . $e->getMessage(), [
                'pupil_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    // Get all classes
    public function getClassList($onlyActive = false) {
        try {
            if ($onlyActive) {
                // For report cards and filters - only classes with active pupils
                $sql = "SELECT DISTINCT class FROM pupils WHERE status = 'active' ORDER BY class";
                $stmt = $this->db->query($sql);
                return $stmt->fetchAll(\PDO::FETCH_COLUMN);
            } else {
                // For enrollment - return all possible classes
                return [
                    'Class 1',
                    'Class 2',
                    'Class 3',
                    'Class 4',
                    'Class 5',
                    'Class 6'
                ];
            }
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getClassList: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAcademicHistory($pupilId) {
        $sql = "SELECT r.*, 
                       (SELECT COUNT(*) FROM results r2 
                        WHERE r2.academic_year = r.academic_year 
                        AND r2.term = r.term 
                        AND r2.class = r.class) as total_students
                FROM results r
                WHERE r.pupil_id = ?
                ORDER BY r.academic_year DESC, r.term DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pupilId]);
        return $stmt->fetchAll();
    }

    public function getActiveStudentsByClass($class) {
        try {
            $sql = "SELECT * FROM pupils 
                    WHERE class = ? AND status = 'active' 
                    ORDER BY first_name, last_name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$class]);
            
            // For debugging
            error_log("Fetching pupils for class: $class");
            $pupils = $stmt->fetchAll();
            error_log("Found " . count($pupils) . " pupils");
            
            return $pupils;
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getActiveStudentsByClass: " . $e->getMessage());
            throw $e;
        }
    }

    public function getClassCount($class) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM pupils 
                    WHERE class = ? AND status = 'active'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$class]);
            $result = $stmt->fetch();
            
            // For debugging
            error_log("Getting count for class: $class, count: {$result['count']}");
            
            return (int)$result['count'];
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getClassCount: " . $e->getMessage());
            throw $e;
        }
    }
} 
