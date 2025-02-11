<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\ErrorHandler;

class MarkingPeriod {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getCurrentPeriod() {
        try {
            $sql = "SELECT * FROM marking_periods 
                    WHERE start_date <= CURRENT_DATE 
                    AND end_date >= CURRENT_DATE 
                    AND is_active = 1 
                    LIMIT 1";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getCurrentPeriod: " . $e->getMessage());
            throw $e;
        }
    }

    public function createPeriod($data) {
        try {
            $sql = "INSERT INTO marking_periods (
                        academic_year, term, sequence, 
                        start_date, end_date, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['academic_year'],
                $data['term'],
                $data['sequence'],
                $data['start_date'],
                $data['end_date'],
                $data['is_active'] ?? 1
            ]);
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in createPeriod: " . $e->getMessage());
            throw $e;
        }
    }

    public function updatePeriod($id, $data) {
        try {
            $sql = "UPDATE marking_periods 
                    SET academic_year = ?, term = ?, sequence = ?,
                        start_date = ?, end_date = ?, is_active = ?
                    WHERE period_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['academic_year'],
                $data['term'],
                $data['sequence'],
                $data['start_date'],
                $data['end_date'],
                $data['is_active'] ?? 1,
                $id
            ]);
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in updatePeriod: " . $e->getMessage());
            throw $e;
        }
    }

    public function canTeacherRecord() {
        $period = $this->getCurrentPeriod();
        return !empty($period);
    }

    public function getAllPeriods() {
        try {
            $sql = "SELECT * FROM marking_periods ORDER BY academic_year DESC, term DESC, sequence DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getAllPeriods: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPeriodById($id) {
        try {
            $sql = "SELECT * FROM marking_periods WHERE period_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getPeriodById: " . $e->getMessage());
            throw $e;
        }
    }

    public function deletePeriod($id) {
        try {
            $sql = "DELETE FROM marking_periods WHERE period_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in deletePeriod: " . $e->getMessage());
            throw $e;
        }
    }
} 