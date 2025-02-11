<?php
$title = 'Report Card';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="<?= url('admin/report-cards') ?>" class="text-blue-600 hover:text-blue-900">
            ← Back to Report Cards
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">
                Report Card: <?= htmlspecialchars($pupil['first_name'] . ' ' . $pupil['last_name']) ?>
                <span class="text-sm text-gray-500 ml-2">(<?= htmlspecialchars($pupil['matricule']) ?>)</span>
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Class: <?= htmlspecialchars($pupil['class']) ?> | 
                Academic Year: <?= htmlspecialchars($academic_year) ?> | 
                Term: <?= htmlspecialchars($term) ?>
            </p>
        </div>

        <!-- Results Table -->
        <div class="px-6 py-4">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left">Subject</th>
                        <th class="px-4 py-2 text-center">Mark</th>
                        <th class="px-4 py-2 text-center">Coefficient</th>
                        <th class="px-4 py-2 text-center">Total</th>
                        <th class="px-4 py-2 text-left">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td class="px-4 py-2"><?= htmlspecialchars($result['subject_name']) ?></td>
                            <td class="px-4 py-2 text-center"><?= htmlspecialchars($result['mark']) ?></td>
                            <td class="px-4 py-2 text-center"><?= htmlspecialchars($result['coefficient']) ?></td>
                            <td class="px-4 py-2 text-center">
                                <?= htmlspecialchars($result['mark'] * $result['coefficient']) ?>
                            </td>
                            <td class="px-4 py-2"><?= htmlspecialchars($result['remarks'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-4 py-2 font-semibold">Average:</td>
                        <td class="px-4 py-2 text-center font-semibold"><?= number_format($average, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Class Statistics -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Class Statistics</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Position</p>
                    <p class="text-lg font-semibold"><?= htmlspecialchars($position) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Class Average</p>
                    <p class="text-lg font-semibold"><?= number_format($class_stats['average'], 2) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Highest Average</p>
                    <p class="text-lg font-semibold"><?= number_format($class_stats['highest'], 2) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Lowest Average</p>
                    <p class="text-lg font-semibold"><?= number_format($class_stats['lowest'], 2) ?></p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-end space-x-3">
                <a href="<?= url('admin/report-cards/generate/' . $pupil['pupil_id'] . '?format=pdf' .
                    '&academic_year=' . $academic_year . '&term=' . $term) ?>" 
                   class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Download PDF
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 