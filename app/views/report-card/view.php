<?php
$title = 'Report Card';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- School Header -->
        <div class="text-center p-6 bg-blue-50 border-b">
            <h1 class="text-2xl font-bold text-gray-800"><?= $_ENV['SCHOOL_NAME'] ?? 'School Management System' ?></h1>
            <p class="text-gray-600">Academic Year: <?= htmlspecialchars($academicYear) ?></p>
            <p class="text-gray-600">Term <?= htmlspecialchars($term) ?> Report Card</p>
        </div>

        <!-- Student Info -->
        <div class="grid grid-cols-2 gap-4 p-6 bg-gray-50">
            <div>
                <p class="font-semibold">Name: <?= htmlspecialchars($pupil['first_name'] . ' ' . $pupil['last_name']) ?></p>
                <p>Class: <?= htmlspecialchars($pupil['class']) ?></p>
                <p>Matricule: <?= htmlspecialchars($pupil['matricule']) ?></p>
            </div>
            <div class="text-right">
                <p>Class Average: <?= number_format($classStats['average'], 2) ?></p>
                <p>Position: <?= $position ?> out of <?= $totalStudents ?></p>
            </div>
        </div>

        <!-- Results Table -->
        <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Coef</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Seq 1</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Seq 2</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Exam</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Average</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($result['subject_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                <?= htmlspecialchars($result['coefficient']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center <?= $result['first_sequence_marks'] < 10 ? 'text-red-600' : 'text-blue-600' ?>">
                                <?= $result['first_sequence_marks'] ? number_format($result['first_sequence_marks'], 2) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center <?= $result['second_sequence_marks'] < 10 ? 'text-red-600' : 'text-blue-600' ?>">
                                <?= $result['second_sequence_marks'] ? number_format($result['second_sequence_marks'], 2) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center <?= $result['exam_marks'] < 10 ? 'text-red-600' : 'text-blue-600' ?>">
                                <?= $result['exam_marks'] ? number_format($result['exam_marks'], 2) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold <?= $average < 10 ? 'text-red-600' : 'text-blue-600' ?>">
                                <?= number_format($average, 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold">
                                <?= number_format($totalForSubject, 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">Totals</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold"><?= $totalCoef ?></td>
                        <td colspan="4"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold"><?= number_format($totalPoints, 2) ?></td>
                    </tr>
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right">Term Average:</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold <?= ($totalPoints/$totalCoef) < 10 ? 'text-red-600' : 'text-blue-600' ?>">
                            <?= number_format($totalPoints/$totalCoef, 2) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Teacher's Comment -->
        <div class="p-6 bg-gray-50">
            <h3 class="font-semibold mb-2">Teacher's Comments:</h3>
            <p class="text-gray-700"><?= htmlspecialchars($teacherComment ?? 'No comments') ?></p>
        </div>

        <!-- Action Buttons -->
        <div class="p-6 flex justify-end space-x-4">
            
            <a href="<?= url('report-card/print/' . $pupil['pupil_id'] . '/' . $term) ?>" 
               class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700" 
               target="_blank">
                Print / Download ReportCard
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
?> 