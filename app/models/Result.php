<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\ErrorHandler;
use App\Helpers\AuditLogger;

class Result {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getResultsByClass($class, $academicYear, $term) {
        try {
            $sql = "SELECT r.*, 
                           p.first_name, p.last_name, p.matricule,
                           s.name as subject_name, s.coefficient
                    FROM results r
                    JOIN pupils p ON r.pupil_id = p.pupil_id
                    JOIN subjects s ON r.subject_id = s.subject_id
                    WHERE p.class = ? AND r.academic_year = ? AND r.term = ?
                    ORDER BY p.first_name, p.last_name, s.name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$class, $academicYear, $term]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getResultsByClass: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPupilResults($pupilId, $academicYear, $term) {
        try {
            $sql = "SELECT DISTINCT r.*, s.name as subject_name, s.coefficient 
                    FROM results r 
                    JOIN subjects s ON r.subject_id = s.subject_id 
                    WHERE r.pupil_id = ? 
                    AND r.academic_year = ? 
                    AND r.term = ? 
                    GROUP BY r.subject_id 
                    ORDER BY s.name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pupilId, $academicYear, $term]);
            
            // For debugging
            error_log("Fetching results for pupil: $pupilId, year: $academicYear, term: $term");
            $results = $stmt->fetchAll();
            error_log("Found " . count($results) . " results");
            
            return $results;
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getPupilResults: " . $e->getMessage());
            throw $e;
        }
    }

    public function getClassStatistics($class, $academicYear, $term) {
        try {
            $sql = "WITH pupil_averages AS (
                        SELECT 
                            r.pupil_id,
                            AVG(r.term_average) as average
                        FROM results r
                        JOIN pupils p ON r.pupil_id = p.pupil_id
                        WHERE p.class = ? 
                        AND r.academic_year = ? 
                        AND r.term = ?
                        GROUP BY r.pupil_id
                    )
                    SELECT 
                        AVG(average) as class_average,
                        MAX(average) as highest,
                        MIN(average) as lowest
                    FROM pupil_averages";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$class, $academicYear, $term]);
            $stats = $stmt->fetch();
            
            return [
                'average' => round($stats['class_average'] ?? 0, 2),
                'highest' => round($stats['highest'] ?? 0, 2),
                'lowest' => round($stats['lowest'] ?? 0, 2)
            ];
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getClassStatistics: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPupilPosition($pupilId, $class, $academicYear, $term) {
        try {
            // First, get all pupils' averages in the class
            $sql = "WITH pupil_averages AS (
                        SELECT 
                            r.pupil_id,
                            AVG((r.first_sequence_marks + r.second_sequence_marks + r.exam_marks) / 3 * s.coefficient) 
                            / SUM(s.coefficient) as term_average
                        FROM results r
                        JOIN pupils p ON r.pupil_id = p.pupil_id
                        JOIN subjects s ON r.subject_id = s.subject_id
                        WHERE p.class = ? 
                        AND r.academic_year = ? 
                        AND r.term = ?
                        GROUP BY r.pupil_id
                    )
                    SELECT COUNT(*) + 1 as position
                    FROM pupil_averages
                    WHERE term_average > (
                        SELECT term_average 
                        FROM pupil_averages 
                        WHERE pupil_id = ?
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$class, $academicYear, $term, $pupilId]);
            $result = $stmt->fetch();
            
            // For debugging
            error_log("Calculating position for pupil: $pupilId in class: $class");
            error_log("Position: " . ($result['position'] ?? 'N/A'));
            
            return $result ? $result['position'] : 'N/A';
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getPupilPosition: " . $e->getMessage());
            throw $e;
        }
    }

    public function saveResults($marks) {
        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO results (
                        pupil_id, subject_id, academic_year, term,
                        first_sequence_marks, second_sequence_marks, exam_marks,
                        total_marks, term_average, teacher_comment
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        first_sequence_marks = VALUES(first_sequence_marks),
                        second_sequence_marks = VALUES(second_sequence_marks),
                        exam_marks = VALUES(exam_marks),
                        total_marks = VALUES(total_marks),
                        term_average = VALUES(term_average),
                        teacher_comment = VALUES(teacher_comment)";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($marks as $mark) {
                // Calculate total and average
                $total = array_sum([
                    $mark['first_sequence_marks'] ?? 0,
                    $mark['second_sequence_marks'] ?? 0,
                    $mark['exam_marks'] ?? 0
                ]);
                
                $average = $total / 3; // Average of all marks
                
                $stmt->execute([
                    $mark['pupil_id'],
                    $mark['subject_id'],
                    $mark['academic_year'],
                    $mark['term'],
                    $mark['first_sequence_marks'],
                    $mark['second_sequence_marks'],
                    $mark['exam_marks'],
                    $total,
                    $average,
                    $mark['teacher_comment'] ?? null
                ]);
                
                // For debugging
                error_log("Saving marks for pupil: {$mark['pupil_id']}, subject: {$mark['subject_id']}");
            }
            
            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            ErrorHandler::logError("Database error in saveResults: " . $e->getMessage());
            throw $e;
        }
    }

    public function calculateTermAverage($results) {
        $totalWeightedMarks = 0;
        $totalCoefficients = 0;

        foreach ($results as $result) {
            $sequenceAverage = ($result['first_sequence_marks'] + $result['second_sequence_marks'] + $result['exam_marks']) / 3;
            $weightedMarks = $sequenceAverage * $result['coefficient'];
            
            $totalWeightedMarks += $weightedMarks;
            $totalCoefficients += $result['coefficient'];
        }

        return $totalCoefficients > 0 ? round($totalWeightedMarks / $totalCoefficients, 2) : 0;
    }

    public function updateRankings($class, $academicYear, $term) {
        try {
            $this->db->beginTransaction();

            // Get all pupils in the class with their averages
            $sql = "WITH pupil_averages AS (
                    SELECT r.pupil_id, 
                           SUM(r.total_marks * s.coefficient) / SUM(s.coefficient) as average
                    FROM results r
                    JOIN subjects s ON r.subject_id = s.subject_id
                    JOIN pupils p ON r.pupil_id = p.pupil_id
                    WHERE p.class = ? AND r.academic_year = ? AND r.term = ?
                    GROUP BY r.pupil_id
                )
                SELECT pupil_id, average,
                       RANK() OVER (ORDER BY average DESC) as rank
                FROM pupil_averages";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$class, $academicYear, $term]);
            $rankings = $stmt->fetchAll();

            // Update rankings for each pupil
            $updateSql = "UPDATE results 
                         SET ranking = ?
                         WHERE pupil_id = ? AND academic_year = ? AND term = ?";
            $updateStmt = $this->db->prepare($updateSql);

            foreach ($rankings as $rank) {
                $updateStmt->execute([
                    $rank['rank'],
                    $rank['pupil_id'],
                    $academicYear,
                    $term
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            ErrorHandler::logError("Database error in updateRankings: " . $e->getMessage(), [
                'class' => $class,
                'academicYear' => $academicYear,
                'term' => $term,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function saveResult($data) {
        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO results (
                        pupil_id, subject_id, academic_year, term,
                        first_sequence_marks, second_sequence_marks, exam_marks,
                        total_marks, term_average, ranking, teacher_comment
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        first_sequence_marks = VALUES(first_sequence_marks),
                        second_sequence_marks = VALUES(second_sequence_marks),
                        exam_marks = VALUES(exam_marks),
                        total_marks = VALUES(total_marks),
                        term_average = VALUES(term_average),
                        ranking = VALUES(ranking),
                        teacher_comment = VALUES(teacher_comment)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['pupil_id'],
                $data['subject_id'],
                $data['academic_year'],
                $data['term'],
                $data['first_sequence_marks'],
                $data['second_sequence_marks'],
                $data['exam_marks'],
                $data['total_marks'],
                $data['term_average'],
                $data['ranking'],
                $data['teacher_comment'] ?? null
            ]);

            if ($result) {
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            ErrorHandler::logError("Database error in saveResult: " . $e->getMessage());
            throw $e;
        }
    }

    public function getClassSubjectMarks($class, $subjectId, $academicYear, $term) {
        try {
            $sql = "SELECT r.*, p.first_name, p.last_name 
                    FROM results r
                    JOIN pupils p ON r.pupil_id = p.pupil_id
                    WHERE p.class = ? 
                    AND r.subject_id = ?
                    AND r.academic_year = ?
                    AND r.term = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$class, $subjectId, $academicYear, $term]);
            
            $results = [];
            while ($row = $stmt->fetch()) {
                $results[$row['pupil_id']] = $row;
            }
            return $results;
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getClassSubjectMarks: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTeacherComment($pupilId, $academicYear, $term) {
        try {
            $sql = "SELECT teacher_comment 
                    FROM results 
                    WHERE pupil_id = ? 
                    AND academic_year = ? 
                    AND term = ? 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pupilId, $academicYear, $term]);
            $result = $stmt->fetch();
            
            // For debugging
            error_log("Getting teacher comment for pupil: $pupilId, year: $academicYear, term: $term");
            
            return $result ? $result['teacher_comment'] : null;
        } catch (\PDOException $e) {
            ErrorHandler::logError("Database error in getTeacherComment: " . $e->getMessage());
            throw $e;
        }
    }
} 