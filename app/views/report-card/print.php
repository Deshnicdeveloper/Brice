<?php
// Print-optimized view - no layout wrapper
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card - Print View</title>
    <style>
        @media print {
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
            }
            .student-info {
                display: grid;
                grid-template-columns: 1fr 1fr;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                border: 1px solid #000;
                padding: 8px;
                text-align: center;
            }
            th {
                background-color: #f0f0f0;
            }
            .red-text { color: red; }
            .blue-text { color: blue; }
            .comments {
                margin-top: 20px;
                border: 1px solid #000;
                padding: 10px;
            }
            .footer {
                margin-top: 30px;
                display: grid;
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1><?= $_ENV['SCHOOL_NAME'] ?></h1>
        <p>Academic Year: <?= htmlspecialchars($currentPeriod['academic_year']) ?></p>
        <p>Term <?= htmlspecialchars($currentPeriod['term']) ?> Report Card</p>
    </div>

    <div class="student-info">
        <div>
            <p><strong>Name:</strong> <?= htmlspecialchars($pupil['first_name'] . ' ' . $pupil['last_name']) ?></p>
            <p><strong>Class:</strong> <?= htmlspecialchars($pupil['class']) ?></p>
            <p><strong>Matricule:</strong> <?= htmlspecialchars($pupil['matricule']) ?></p>
        </div>
        <div style="text-align: right;">
            <p><strong>Class Average:</strong> <?= number_format($classStats['average'], 2) ?></p>
            <p><strong>Position:</strong> <?= $position ?> out of <?= $totalStudents ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Coef</th>
                <th>Seq 1</th>
                <th>Seq 2</th>
                <th>Exam</th>
                <th>Average</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalCoef = 0;
            $totalPoints = 0;
            foreach ($results as $result): 
                $average = ($result['first_sequence_marks'] + $result['second_sequence_marks'] + $result['exam_marks']) / 3;
                $totalForSubject = $average * $result['coefficient'];
                $totalCoef += $result['coefficient'];
                $totalPoints += $totalForSubject;
            ?>
                <tr>
                    <td><?= htmlspecialchars($result['subject_name']) ?></td>
                    <td><?= htmlspecialchars($result['coefficient']) ?></td>
                    <td class="<?= $result['first_sequence_marks'] < 10 ? 'red-text' : 'blue-text' ?>">
                        <?= $result['first_sequence_marks'] ? number_format($result['first_sequence_marks'], 2) : '-' ?>
                    </td>
                    <td class="<?= $result['second_sequence_marks'] < 10 ? 'red-text' : 'blue-text' ?>">
                        <?= $result['second_sequence_marks'] ? number_format($result['second_sequence_marks'], 2) : '-' ?>
                    </td>
                    <td class="<?= $result['exam_marks'] < 10 ? 'red-text' : 'blue-text' ?>">
                        <?= $result['exam_marks'] ? number_format($result['exam_marks'], 2) : '-' ?>
                    </td>
                    <td class="<?= $average < 10 ? 'red-text' : 'blue-text' ?>">
                        <?= number_format($average, 2) ?>
                    </td>
                    <td><?= number_format($totalForSubject, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td><strong>Totals</strong></td>
                <td><strong><?= $totalCoef ?></strong></td>
                <td colspan="4"></td>
                <td><strong><?= number_format($totalPoints, 2) ?></strong></td>
            </tr>
            <tr>
                <td colspan="6" style="text-align: right;"><strong>Term Average:</strong></td>
                <td class="<?= ($totalPoints/$totalCoef) < 10 ? 'red-text' : 'blue-text' ?>">
                    <strong><?= number_format($totalPoints/$totalCoef, 2) ?></strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="comments">
        <h3>Teacher's Comments:</h3>
        <p><?= htmlspecialchars($teacherComment ?? 'No comments') ?></p>
    </div>

    <div class="footer">
        <div>
            <p>Class Teacher's Signature</p>
            <br>
            _______________________
        </div>
        <div style="text-align: right;">
            <p>Principal's Signature</p>
            <br>
            _______________________
        </div>
    </div>
</body>
</html> 