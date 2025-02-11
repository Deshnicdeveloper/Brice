<?php

namespace App\Helpers;

class GradeCalculator {
    private static $grades = [
        ['min' => 80, 'max' => 100, 'grade' => 'A', 'remark' => 'Excellent'],
        ['min' => 70, 'max' => 79.99, 'grade' => 'B', 'remark' => 'Very Good'],
        ['min' => 60, 'max' => 69.99, 'grade' => 'C', 'remark' => 'Good'],
        ['min' => 50, 'max' => 59.99, 'grade' => 'D', 'remark' => 'Average'],
        ['min' => 40, 'max' => 49.99, 'grade' => 'E', 'remark' => 'Below Average'],
        ['min' => 0, 'max' => 39.99, 'grade' => 'F', 'remark' => 'Fail']
    ];

    public static function calculateGrade($score) {
        foreach (self::$grades as $grade) {
            if ($score >= $grade['min'] && $score <= $grade['max']) {
                return [
                    'grade' => $grade['grade'],
                    'remark' => $grade['remark']
                ];
            }
        }
        return ['grade' => 'N/A', 'remark' => 'Invalid Score'];
    }

    public static function generateTermRemark($average) {
        $grade = self::calculateGrade($average);
        
        switch ($grade['grade']) {
            case 'A':
                return 'Outstanding performance! Keep up the excellent work.';
            case 'B':
                return 'Very good performance. Continue working hard.';
            case 'C':
                return 'Good performance with room for improvement.';
            case 'D':
                return 'Fair performance. More effort needed.';
            case 'E':
                return 'Below average. Significant improvement needed.';
            case 'F':
                return 'Poor performance. Urgent attention and support required.';
            default:
                return 'Unable to generate remark.';
        }
    }

    public static function getProgressIndicator($currentAverage, $previousAverage) {
        if ($previousAverage === null) return 'N/A';
        
        $difference = $currentAverage - $previousAverage;
        if ($difference > 5) return 'Significant Improvement';
        if ($difference > 0) return 'Slight Improvement';
        if ($difference < -5) return 'Significant Decline';
        if ($difference < 0) return 'Slight Decline';
        return 'Stable';
    }
} 