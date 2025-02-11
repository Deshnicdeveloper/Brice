<?php
$title = 'View Results';
ob_start();
?>

<div class="mb-6">
    <a href="/admin/results" class="text-blue-600 hover:text-blue-900">← Back to Results</a>
    <h2 class="text-xl font-semibold mt-4">
        Results for <?= $pupil['first_name'] . ' ' . $pupil['last_name'] ?>
        <span class="text-gray-500">(<?= $pupil['matricule'] ?>)</span>
    </h2>
</div>

<!-- Period Selection -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="hidden" name="pupil_id" value="<?= $pupil['pupil_id'] ?>">
        
        <div>
            <label class="block text-sm font-medium text-gray-700">Academic Year</label>
            <select name="academic_year" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <?php foreach ($academicYears as $year): ?>
                    <option value="<?= $year ?>" <?= $selectedYear == $year ? 'selected' : '' ?>>
                        <?= $year ?>-<?= $year + 1 ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Term</label>
            <select name="term" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <?php foreach ($terms as $term): ?>
                    <option value="<?= $term ?>" <?= $selectedTerm == $term ? 'selected' : '' ?>>
                        Term <?= $term ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" 
                    class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                View Results
            </button>
        </div>
    </form>
</div>

<!-- Results Display -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <?php if (!empty($results)): ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coefficient</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">1st Sequence</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">2nd Sequence</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weighted</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php 
                $totalWeighted = 0;
                $totalCoef = 0;
                foreach ($results as $result): 
                    $average = ($result['first_sequence_marks'] + $result['second_sequence_marks'] + $result['exam_marks']) / 3;
                    $weighted = $average * $result['coefficient'];
                    $totalWeighted += $weighted;
                    $totalCoef += $result['coefficient'];
                ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= $result['subject_name'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= $result['coefficient'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= number_format($result['first_sequence_marks'], 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= number_format($result['second_sequence_marks'], 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= number_format($result['exam_marks'], 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= number_format($average, 2) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= number_format($weighted, 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="bg-gray-50 font-medium">
                    <td colspan="6" class="px-6 py-4 text-right">Term Average:</td>
                    <td class="px-6 py-4">
                        <?= number_format($totalWeighted / $totalCoef, 2) ?>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-gray-500 text-center py-6">No results found for this period.</p>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 