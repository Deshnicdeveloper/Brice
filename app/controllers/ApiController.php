<?php

namespace App\Controllers;

use App\Models\Pupil;
use App\Models\Subject;
use App\Models\Result;
use App\Helpers\AuthHelper;

class ApiController {
    private $pupil;
    private $subject;
    private $result;

    public function __construct() {
        $this->pupil = new Pupil();
        $this->subject = new Subject();
        $this->result = new Result();
    }

    public function getClassData() {
        AuthHelper::requireRole(['admin', 'teacher']);
        
        $class = $_GET['class'] ?? '';
        $year = $_GET['year'] ?? '';
        $term = $_GET['term'] ?? '';

        if (!$class || !$year || !$term) {
            $this->jsonResponse(['error' => 'Missing required parameters'], 400);
            return;
        }

        try {
            $pupils = $this->pupil->getPupilsByClass($class);
            $subjects = $this->subject->getSubjectsByClass($class);
            $existingResults = $this->result->getResultsByClass($class, $year, $term);

            $this->jsonResponse([
                'pupils' => $pupils,
                'subjects' => $subjects,
                'existingResults' => $existingResults
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch class data'], 500);
        }
    }

    private function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
} 