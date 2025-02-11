<?php

namespace App\Models;

use App\Config\Database;

class Stats {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getTeacherCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM teachers WHERE status = 'active'");
        return $stmt->fetch()['count'];
    }

    public function getPupilCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM pupils WHERE status = 'active'");
        return $stmt->fetch()['count'];
    }

    public function getParentCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM parents WHERE status = 'active'");
        return $stmt->fetch()['count'];
    }

    public function getClassCount() {
        $stmt = $this->db->query("SELECT COUNT(DISTINCT class) as count FROM pupils WHERE status = 'active'");
        return $stmt->fetch()['count'];
    }

    public function getRecentActivities($limit = 5) {
        // For now, we'll just show recent pupil registrations
        $sql = "SELECT 
                    CONCAT(first_name, ' ', last_name) as pupil_name,
                    'New pupil registration' as action,
                    admission_date as date
                FROM pupils 
                ORDER BY admission_date DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        $activities = [];
        while ($row = $stmt->fetch()) {
            $activities[] = [
                'description' => "{$row['action']}: {$row['pupil_name']}",
                'time' => date('M j, Y', strtotime($row['date']))
            ];
        }
        
        return $activities;
    }
} 