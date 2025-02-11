<?php

namespace App\Helpers;

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFGenerator {
    private $dompdf;

    public function __construct() {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $this->dompdf = new Dompdf($options);
    }

    public function loadHtml($html) {
        $this->dompdf->loadHtml($html);
    }

    public function setPaper($size = 'A4', $orientation = 'portrait') {
        $this->dompdf->setPaper($size, $orientation);
    }

    public function render() {
        $this->dompdf->render();
    }

    public function stream($filename, $options = []) {
        $this->dompdf->stream($filename, $options);
    }

    public function output() {
        return $this->dompdf->output();
    }
} 