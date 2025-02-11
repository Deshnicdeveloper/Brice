<?php

namespace App\Controllers;

use App\Models\ReportCard;
use App\Models\Pupil;
use App\Models\Result;
use App\Models\Subject;
use App\Models\MarkingPeriod;
use App\Helpers\AuthHelper;
use App\Helpers\PDFGenerator;
use App\Helpers\ErrorHandler;
use TCPDF; // We'll use this for PDF generation

class ReportCardController {
    private $reportCard;
    private $pupil;
    private $result;
    private $subject;
    private $markingPeriod;
    private $itemsPerPage = 20;

    public function __construct() {
        $this->reportCard = new ReportCard();
        $this->pupil = new Pupil();
        $this->result = new Result();
        $this->subject = new Subject();
        $this->markingPeriod = new MarkingPeriod();
    }

    public function index() {
        AuthHelper::requireRole('admin');
        
        try {
            $class = $_GET['class'] ?? '';
            $academicYear = $_GET['academic_year'] ?? date('Y');
            $term = $_GET['term'] ?? 1;
            
            $classList = $this->pupil->getClassList(true);
            $pupils = [];
            
            if ($class) {
                $pupils = $this->pupil->getActiveStudentsByClass($class);
            }
            
            require __DIR__ . '/../views/admin/report-cards/index.php';
            
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to load report cards page", [
                'error' => $e->getMessage()
            ]);
            $error = "Failed to load report cards";
            require __DIR__ . '/../views/admin/report-cards/index.php';
        }
    }

    public function generate($pupilId) {
        AuthHelper::requireRole('admin');
        
        try {
            // Get pupil data
            $pupil = $this->pupil->getPupilById($pupilId);
            if (!$pupil) {
                throw new \Exception("Pupil not found");
            }

            // Get parameters from URL
            $academicYear = $_GET['academic_year'] ?? date('Y');
            $term = $_GET['term'] ?? 1;

            // Get all subjects for the pupil's class
            $subjects = $this->subject->getSubjectsByClass($pupil['class']);

            // Get results for each subject
            $results = $this->result->getPupilResults($pupilId, $academicYear, $term);

            // Calculate totals and averages
            $totalMarks = 0;
            $totalCoefficient = 0;
            foreach ($results as $result) {
                $totalMarks += ($result['mark'] * $result['coefficient']);
                $totalCoefficient += $result['coefficient'];
            }
            $average = $totalCoefficient > 0 ? round($totalMarks / $totalCoefficient, 2) : 0;

            // Get class statistics
            $class_stats = $this->result->getClassStatistics($pupil['class'], $academicYear, $term);
            
            // Get position in class
            $position = $this->result->getPupilPosition($pupilId, $pupil['class'], $academicYear, $term);

            // Prepare data for the view
            $reportData = [
                'pupil' => $pupil,
                'academic_year' => $academicYear,
                'term' => $term,
                'subjects' => $subjects,
                'results' => $results,
                'average' => $average,
                'position' => $position,
                'class_stats' => $class_stats
            ];

            // Check if PDF format is requested
            if (isset($_GET['format']) && $_GET['format'] === 'pdf') {
                $this->generatePDF($reportData);
            } else {
                // Extract variables for the view
                extract($reportData);
                require __DIR__ . '/../views/admin/report-cards/view.php';
            }

        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to generate report card", [
                'error' => $e->getMessage(),
                'pupil_id' => $pupilId
            ]);
            header('Location: ' . url('admin/report-cards'));
            exit;
        }
    }

    private function generatePDF($reportData) {
        $html = $this->renderPDFTemplate($reportData);
        
        $pdf = new PDFGenerator();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
        
        $filename = sprintf(
            'report_card_%s_%s_term%d.pdf',
            $reportData['pupil']['matricule'],
            $reportData['academic_year'],
            $reportData['term']
        );
        
        $pdf->stream($filename, ['Attachment' => true]);
    }

    private function renderPDFTemplate($data) {
        ob_start();
        require __DIR__ . '/../views/admin/report-cards/pdf-template.php';
        return ob_get_clean();
    }

    public function generateBulk() {
        AuthHelper::requireRole(['admin', 'teacher']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/report-cards');
            exit;
        }

        $class = $_POST['class'] ?? '';
        $academicYear = $_POST['academic_year'] ?? '';
        $term = $_POST['term'] ?? '';

        if (!$class || !$academicYear || !$term) {
            ErrorHandler::setError('bulk', 'Missing required parameters');
            header('Location: /admin/report-cards');
            exit;
        }

        try {
            $reportCards = $this->reportCard->generateBulkReportCards($class, $academicYear, $term);
            
            // Create ZIP archive
            $zip = new \ZipArchive();
            $zipName = sprintf('report_cards_%s_%s_term%d.zip', $class, $academicYear, $term);
            $zipPath = sys_get_temp_dir() . '/' . $zipName;
            
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
                throw new \Exception("Cannot create ZIP archive");
            }

            foreach ($reportCards as $reportData) {
                // Archive each report card
                $this->reportCard->archiveReportCard($reportData);
                
                // Add PDF to ZIP
                $pdfContent = $this->generatePDFContent($reportData);
                $filename = sprintf(
                    'report_card_%s_%s_term%d.pdf',
                    $reportData['pupil']['matricule'],
                    $academicYear,
                    $term
                );
                $zip->addFromString($filename, $pdfContent);
            }
            
            $zip->close();

            // Send ZIP file
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipName . '"');
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);
            unlink($zipPath);
            exit;
        } catch (\Exception $e) {
            ErrorHandler::setError('bulk', $e->getMessage());
            header('Location: /admin/report-cards');
            exit;
        }
    }

    private function generatePDFContent($reportData) {
        $html = $this->renderPDFTemplate($reportData);
        $pdf = new PDFGenerator();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
        return $pdf->output();
    }

    public function archives() {
        AuthHelper::requireRole(['admin', 'teacher']);
        
        $currentYear = date('Y');
        $academicYears = range($currentYear, $currentYear - 5);
        $terms = [1, 2, 3];
        $classes = $this->pupil->getClassList();
        
        // Get filter parameters
        $selectedClass = $_GET['class'] ?? '';
        $selectedYear = $_GET['academic_year'] ?? '';
        $selectedTerm = $_GET['term'] ?? '';
        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        
        // Get archives with pagination
        $offset = ($page - 1) * $this->itemsPerPage;
        $archiveData = $this->reportCard->getArchives(
            $selectedClass,
            $selectedYear,
            $selectedTerm,
            $search,
            $this->itemsPerPage,
            $offset
        );
        
        $archives = $archiveData['archives'];
        $totalArchives = $archiveData['total'];
        $totalPages = ceil($totalArchives / $this->itemsPerPage);
        
        require __DIR__ . '/../views/admin/report-cards/archives.php';
    }

    public function downloadArchive($archiveId) {
        AuthHelper::requireRole(['admin', 'teacher', 'parent']);
        
        try {
            $archive = $this->reportCard->getArchiveById($archiveId);
            if (!$archive) {
                throw new \Exception("Archive not found");
            }

            // Check if parent is authorized to download this report card
            if ($_SESSION['user_type'] === 'parent') {
                $pupil = $this->pupil->getPupilById($archive['pupil_id']);
                if ($_SESSION['user_id'] !== $pupil['parent_id']) {
                    http_response_code(403);
                    require __DIR__ . '/../views/errors/403.php';
                    exit;
                }
            }

            // Send PDF file
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $archive['filename'] . '"');
            echo $archive['pdf_content'];
            exit;
        } catch (\Exception $e) {
            ErrorHandler::setError('download', $e->getMessage());
            header('Location: /admin/report-cards/archives');
            exit;
        }
    }

    public function view($pupilId, $term = null) {
        try {
            // Get current period if term not specified
            $currentPeriod = $this->markingPeriod->getCurrentPeriod();
            if ($term === null) {
                $term = $currentPeriod['term'];
            }
            $academicYear = $currentPeriod['academic_year'];

            // Get pupil info
            $pupil = $this->pupil->getPupilById($pupilId);
            if (!$pupil) {
                throw new \Exception("Pupil not found");
            }

            // Get all results for the pupil
            $results = $this->result->getPupilResults($pupilId, $academicYear, $term);

            // Get class statistics
            $classStats = $this->result->getClassStatistics($pupil['class'], $academicYear, $term);

            // Get total number of students in class
            $totalStudents = $this->pupil->getClassCount($pupil['class']);

            // Get pupil's position in class
            $position = $this->result->getPupilPosition($pupilId, $pupil['class'], $academicYear, $term);

            // Get teacher's comment
            $teacherComment = $this->result->getTeacherComment($pupilId, $academicYear, $term);

            require __DIR__ . '/../views/report-card/view.php';
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to load report card", [
                'error' => $e->getMessage(),
                'pupil_id' => $pupilId,
                'term' => $term
            ]);
            ErrorHandler::setError('report', 'Failed to load report card');
            header('Location: ' . url('dashboard'));
            exit;
        }
    }

    public function download($pupilId, $term) {
        try {
            // Get current period
            $currentPeriod = $this->markingPeriod->getCurrentPeriod();
            $academicYear = $currentPeriod['academic_year'];
            
            $pupil = $this->pupil->getPupilById($pupilId);
            $results = $this->result->getPupilResults($pupilId, $academicYear, $term);
            $classStats = $this->result->getClassStatistics($pupil['class'], $academicYear, $term);
            $totalStudents = $this->pupil->getClassCount($pupil['class']);
            $position = $this->result->getPupilPosition($pupilId, $pupil['class'], $academicYear, $term);
            $teacherComment = $this->result->getTeacherComment($pupilId, $academicYear, $term);

            // Generate PDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($_ENV['SCHOOL_NAME']);
            $pdf->SetTitle('Report Card - ' . $pupil['first_name'] . ' ' . $pupil['last_name']);

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Add a page
            $pdf->AddPage();

            // Get HTML content from a template
            ob_start();
            require __DIR__ . '/../views/report-card/pdf-template.php';
            $html = ob_get_clean();

            // Print HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            // Output PDF
            $pdf->Output('report_card.pdf', 'D');
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to download report card", [
                'error' => $e->getMessage(),
                'pupil_id' => $pupilId,
                'term' => $term
            ]);
            ErrorHandler::setError('report', 'Failed to download report card');
            header('Location: ' . url('report-card/view/' . $pupilId . '/' . $term));
            exit;
        }
    }

    public function print($pupilId, $term) {
        // Similar to view but with a print-optimized template
        try {
            $currentPeriod = $this->markingPeriod->getCurrentPeriod();
            $academicYear = $currentPeriod['academic_year'];
            
            $pupil = $this->pupil->getPupilById($pupilId);
            $results = $this->result->getPupilResults($pupilId, $academicYear, $term);
            $classStats = $this->result->getClassStatistics($pupil['class'], $academicYear, $term);
            $totalStudents = $this->pupil->getClassCount($pupil['class']);
            $position = $this->result->getPupilPosition($pupilId, $pupil['class'], $academicYear, $term);
            $teacherComment = $this->result->getTeacherComment($pupilId, $academicYear, $term);

            require __DIR__ . '/../views/report-card/print.php';
        } catch (\Exception $e) {
            ErrorHandler::logError("Failed to print report card", [
                'error' => $e->getMessage()
            ]);
            echo "Failed to load print view";
        }
    }
} 