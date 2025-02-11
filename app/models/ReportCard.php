<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\ErrorHandler;
use App\Helpers\GradeCalculator;

class ReportCard {
    protected $db;
    protected $result;
    protected $gradeCalculator;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->result = new Result();
        $this->gradeCalculator = new GradeCalculator();
    }

    public function generateReportCard($pupilId, $academicYear, $term) {
        try {
            // Get pupil details
            $pupil = (new Pupil())->getPupilById($pupilId);
            if (!$pupil) {
                throw new \Exception("Pupil not found");
            }

            // Get all results for the pupil
            $results = $this->result->getPupilResults($pupilId, $academicYear, $term);
            if (empty($results)) {
                throw new \Exception("No results found for this period");
            }

            // Calculate overall statistics
            $totalWeightedMarks = 0;
            $totalCoefficients = 0;
            $subjectResults = [];

            foreach ($results as $result) {
                $average = ($result['first_sequence_marks'] + $result['second_sequence_marks'] + $result['exam_marks']) / 3;
                $weightedMark = $average * $result['coefficient'];
                
                $totalWeightedMarks += $weightedMark;
                $totalCoefficients += $result['coefficient'];

                $gradeInfo = GradeCalculator::calculateGrade($average);

                $subjectResults[] = [
                    'subject' => $result['subject_name'],
                    'coefficient' => $result['coefficient'],
                    'first_sequence' => $result['first_sequence_marks'],
                    'second_sequence' => $result['second_sequence_marks'],
                    'exam' => $result['exam_marks'],
                    'average' => $average,
                    'weighted_mark' => $weightedMark,
                    'grade' => $gradeInfo['grade'],
                    'remark' => $gradeInfo['remark'],
                    'teacher_comment' => $result['teacher_comment']
                ];
            }

            $termAverage = $totalWeightedMarks / $totalCoefficients;
            $termGrade = GradeCalculator::calculateGrade($termAverage);
            $termRemark = GradeCalculator::generateTermRemark($termAverage);
            
            // Get previous term's average for progress tracking
            $previousTermAverage = $this->getPreviousTermAverage($pupilId, $academicYear, $term);
            $progressIndicator = GradeCalculator::getProgressIndicator($termAverage, $previousTermAverage);

            // Get class statistics
            $classStats = $this->getClassStatistics($pupil['class'], $academicYear, $term);

            return [
                'pupil' => $pupil,
                'academic_year' => $academicYear,
                'term' => $term,
                'subjects' => $subjectResults,
                'term_average' => $termAverage,
                'term_grade' => $termGrade['grade'],
                'term_remark' => $termRemark,
                'progress_indicator' => $progressIndicator,
                'rank' => $results[0]['ranking'], // All results have same ranking
                'class_size' => $classStats['class_size'],
                'class_average' => $classStats['class_average'],
                'highest_average' => $classStats['highest_average'],
                'lowest_average' => $classStats['lowest_average']
            ];
        } catch (\Exception $e) {
            ErrorHandler::logError("Error generating report card: " . $e->getMessage(), [
                'pupil_id' => $pupilId,
                'academic_year' => $academicYear,
                'term' => $term,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function getClassStatistics($class, $academicYear, $term) {
        $sql = "WITH pupil_averages AS (
                    SELECT r.pupil_id,
                           SUM(((r.first_sequence_marks + r.second_sequence_marks + r.exam_marks) / 3) * s.coefficient) / 
                           SUM(s.coefficient) as average
                    FROM results r
                    JOIN subjects s ON r.subject_id = s.subject_id
                    JOIN pupils p ON r.pupil_id = p.pupil_id
                    WHERE p.class = ? AND r.academic_year = ? AND r.term = ?
                    GROUP BY r.pupil_id
                )
                SELECT COUNT(*) as class_size,
                       AVG(average) as class_average,
                       MAX(average) as highest_average,
                       MIN(average) as lowest_average
                FROM pupil_averages";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$class, $academicYear, $term]);
        return $stmt->fetch();
    }

    private function getPreviousTermAverage($pupilId, $academicYear, $currentTerm) {
        if ($currentTerm == 1) {
            $prevYear = $academicYear - 1;
            $prevTerm = 3;
        } else {
            $prevYear = $academicYear;
            $prevTerm = $currentTerm - 1;
        }

        $sql = "WITH term_results AS (
                    SELECT r.*,
                           ((r.first_sequence_marks + r.second_sequence_marks + r.exam_marks) / 3) * s.coefficient as weighted_mark,
                           s.coefficient
                    FROM results r
                    JOIN subjects s ON r.subject_id = s.subject_id
                    WHERE r.pupil_id = ? AND r.academic_year = ? AND r.term = ?
                )
                SELECT SUM(weighted_mark) / SUM(coefficient) as term_average
                FROM term_results";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pupilId, $prevYear, $prevTerm]);
        $result = $stmt->fetch();

        return $result ? $result['term_average'] : null;
    }

    public function generateBulkReportCards($class, $academicYear, $term) {
        try {
            $pupils = (new Pupil())->getPupilsByClass($class);
            $reportCards = [];

            foreach ($pupils as $pupil) {
                $reportCards[] = $this->generateReportCard($pupil['pupil_id'], $academicYear, $term);
            }

            return $reportCards;
        } catch (\Exception $e) {
            ErrorHandler::logError("Error generating bulk report cards: " . $e->getMessage());
            throw $e;
        }
    }

    public function archiveReportCard($reportData) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO archived_report_cards 
                    (pupil_id, academic_year, term, term_average, rank, class_size, 
                     class_average, pdf_content, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            // Generate PDF content
            $pdf = new PDFGenerator();
            ob_start();
            require __DIR__ . '/../views/admin/report-cards/pdf-template.php';
            $html = ob_get_clean();
            
            $pdf->loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->render();
            $pdfContent = $pdf->output();

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $reportData['pupil']['pupil_id'],
                $reportData['academic_year'],
                $reportData['term'],
                $reportData['term_average'],
                $reportData['rank'],
                $reportData['class_size'],
                $reportData['class_average'],
                $pdfContent
            ]);

            if ($result) {
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (\Exception $e) {
            $this->db->rollBack();
            ErrorHandler::logError("Error archiving report card: " . $e->getMessage());
            throw $e;
        }
    }
} 