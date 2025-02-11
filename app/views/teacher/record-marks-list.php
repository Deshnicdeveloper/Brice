<?php
$title = 'Record Marks';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold">Record Marks - <?= htmlspecialchars($assignedClass) ?></h2>
        <p class="text-gray-600">Select a subject to record marks</p>
    </div>

    <?php if (!empty($currentPeriod)): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4">
            <p>Current Period: Term <?= htmlspecialchars($currentPeriod['term']) ?>, 
               Sequence <?= htmlspecialchars($currentPeriod['sequence']) ?>
            </p>
            <p class="text-sm">
                (<?= htmlspecialchars($currentPeriod['start_date']) ?> to 
                <?= htmlspecialchars($currentPeriod['end_date']) ?>)
            </p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($subjects as $subject): ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-6 py-4">
                    <div class="font-bold text-xl mb-2"><?= htmlspecialchars($subject['name']) ?></div>
                    <p class="text-gray-600">
                        Coefficient: <?= htmlspecialchars($subject['coefficient']) ?>
                    </p>
                </div>
                <div class="px-6 py-4 bg-gray-50">
                    <a href="<?= url('teacher/record-marks/' . $subject['subject_id'] . '/' . $assignedClass) ?>" 
                       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 inline-block">
                        Record Marks
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../views/layouts/dashboard.php';
?> 