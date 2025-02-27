<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\ErrorHandler;
use App\Helpers\AuditLogger;

class Attendance {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function recordAttendance($data) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO attendance (pupil_id, date, status, term, reason) 
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    status = VALUES(status),
                    reason = VALUES(reason)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['pupil_id'],
                $data['date'],
                $data['status'],
                $data['term'],
                $data['reason'] ?? null
            ]);

            if ($result) {
                AuditLogger::log('record', 'attendance', $data['pupil_id'], null, $data);
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            ErrorHandler::logError("Failed to record attendance", [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    public function getAttendanceByClass($class, $date) {
        try {
            $sql = "SELECT a.*, p.first_name, p.last_name, p.matricule 
                    FROM attendance a
                    JOIN pupils p ON a.pupil_id = p.pupil_id
                    WHERE p.class = ? AND a.date = ?
                    ORDER BY p.first_name, p.last_name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$class, $date]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Failed to get class attendance", [
                'error' => $e->getMessage(),
                'class' => $class,
                'date' => $date
            ]);
            throw $e;
        }
    }

    public function getAttendanceByPupil($pupilId, $term = null) {
        try {
            $sql = "SELECT a.*, p.first_name, p.last_name, p.matricule 
                    FROM attendance a
                    JOIN pupils p ON a.pupil_id = p.pupil_id
                    WHERE a.pupil_id = ?";
            
            if ($term) {
                $sql .= " AND a.term = ?";
            }
            
            $sql .= " ORDER BY a.date DESC";
            
            $stmt = $this->db->prepare($sql);
            if ($term) {
                $stmt->execute([$pupilId, $term]);
            } else {
                $stmt->execute([$pupilId]);
            }
            
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Failed to get pupil attendance", [
                'error' => $e->getMessage(),
                'pupil_id' => $pupilId,
                'term' => $term
            ]);
            throw $e;
        }
    }

    public function getAttendanceStatistics($pupilId, $term = null) {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
                    FROM attendance
                    WHERE pupil_id = ?";
            
            $params = [$pupilId];
            
            if ($term !== null) {
                $sql .= " AND term = ?";
                $params[] = $term;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Failed to get attendance statistics", [
                'error' => $e->getMessage(),
                'pupil_id' => $pupilId,
                'term' => $term
            ]);
            throw $e;
        }
    }

    public function bulkRecordAttendance($attendanceData) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO attendance (pupil_id, date, status, term, reason) 
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    status = VALUES(status),
                    reason = VALUES(reason)";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($attendanceData as $data) {
                $stmt->execute([
                    $data['pupil_id'],
                    $data['date'],
                    $data['status'],
                    $data['term'],
                    $data['reason'] ?? null
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            ErrorHandler::logError("Failed to bulk record attendance", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 