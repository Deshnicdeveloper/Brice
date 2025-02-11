<?php
$title = 'Record Marks';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold"><?= htmlspecialchars($subject['name']) ?></h2>
            <p class="text-gray-600">Class: <?= htmlspecialchars($class) ?></p>
        </div>
        <a href="<?= url('teacher/dashboard') ?>" 
           class="text-blue-600 hover:text-blue-900">← Back to Dashboard</a>
    </div>

    <?php if (!$markingPeriod->canTeacherRecord()): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            Marking period is not active. You cannot record marks at this time.
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($currentPeriod)): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4">
            <p>Current Period: Term <?= htmlspecialchars($currentPeriod['term']) ?>, 
               Sequence <?= htmlspecialchars($currentPeriod['sequence']) ?></p>
            <p class="text-sm">
                (<?= htmlspecialchars($currentPeriod['start_date']) ?> to 
                <?= htmlspecialchars($currentPeriod['end_date']) ?>)
            </p>
        </div>

        <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subject['subject_id']) ?>">
            <input type="hidden" name="academic_year" value="<?= htmlspecialchars($currentPeriod['academic_year']) ?>">
            <input type="hidden" name="term" value="<?= htmlspecialchars($currentPeriod['term']) ?>">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Sequence (/20)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Second Sequence (/20)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam (/20)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pupils as $pupil): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= htmlspecialchars($pupil['first_name'] . ' ' . $pupil['last_name']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" step="0.25" min="0" max="20" 
                                           name="marks[<?= $pupil['pupil_id'] ?>][first_sequence]"
                                           value="<?= $existingMarks[$pupil['pupil_id']]['first_sequence_marks'] ?? '' ?>"
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                           <?= !$markingPeriod->canTeacherRecord() ? 'disabled' : '' ?>>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" step="0.25" min="0" max="20"
                                           name="marks[<?= $pupil['pupil_id'] ?>][second_sequence]"
                                           value="<?= $existingMarks[$pupil['pupil_id']]['second_sequence_marks'] ?? '' ?>"
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                           <?= !$markingPeriod->canTeacherRecord() ? 'disabled' : '' ?>>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" step="0.25" min="0" max="20"
                                           name="marks[<?= $pupil['pupil_id'] ?>][exam]"
                                           value="<?= $existingMarks[$pupil['pupil_id']]['exam_marks'] ?? '' ?>"
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                           <?= !$markingPeriod->canTeacherRecord() ? 'disabled' : '' ?>>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text"
                                           name="marks[<?= $pupil['pupil_id'] ?>][comment]"
                                           value="<?= $existingMarks[$pupil['pupil_id']]['teacher_comment'] ?? '' ?>"
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                           <?= !$markingPeriod->canTeacherRecord() ? 'disabled' : '' ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
                        <?= !$markingPeriod->canTeacherRecord() ? 'disabled' : '' ?>>
                    Save Marks
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../views/layouts/dashboard.php';
?> 