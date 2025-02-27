<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Report Card</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 20px;
            margin-bottom: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-section {
            border: 1px solid #ddd;
            padding: 15px;
        }
        .info-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #000;
            margin: 50px auto 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">EDUCARE</div>
        <div class="report-title">STUDENT REPORT CARD</div>
    </div>

    <div class="info-grid">
        <div class="info-section">
            <div class="info-title">Student Information</div>
            <p>Name: <?= $data['pupil']['first_name'] . ' ' . $data['pupil']['last_name'] ?></p>
            <p>Matricule: <?= $data['pupil']['matricule'] ?></p>
            <p>Class: <?= $data['pupil']['class'] ?></p>
            <p>Academic Year: <?= $data['academic_year'] ?>-<?= $data['academic_year'] + 1 ?></p>
            <p>Term: <?= $data['term'] ?></p>
        </div>

        <div class="info-section">
            <div class="info-title">Class Statistics</div>
            <p>Class Average: <?= number_format($data['class_average'], 2) ?></p>
            <p>Highest Average: <?= number_format($data['highest_average'], 2) ?></p>
            <p>Lowest Average: <?= number_format($data['lowest_average'], 2) ?></p>
            <p>Position: <?= $data['rank'] ?> out of <?= $data['class_size'] ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Coef</th>
                <th>1st Seq</th>
                <th>2nd Seq</th>
                <th>Exam</th>
                <th>Average</th>
                <th>Weighted</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['subjects'] as $subject): ?>
                <tr>
                    <td><?= $subject['subject'] ?></td>
                    <td><?= $subject['coefficient'] ?></td>
                    <td><?= number_format($subject['first_sequence'], 2) ?></td>
                    <td><?= number_format($subject['second_sequence'], 2) ?></td>
                    <td><?= number_format($subject['exam'], 2) ?></td>
                    <td><?= number_format($subject['average'], 2) ?></td>
                    <td><?= number_format($subject['weighted_mark'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Term Average:</strong></td>
                <td colspan="2"><strong><?= number_format($data['term_average'], 2) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-line"></div>
        <div>Principal's Signature</div>
    </div>
</body>
</html> 