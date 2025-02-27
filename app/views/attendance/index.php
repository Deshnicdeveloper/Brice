<?php
use App\Helpers\AuthHelper;

$title = 'Attendance Management';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Attendance Management</h1>
                <p class="text-gray-600 mt-1">Record and manage student attendance</p>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Class Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <?php if (AuthHelper::getRole() === 'admin'): ?>
                    <select name="class" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Select Class</option>
                        <?php foreach ($classList as $classOption): ?>
                            <option value="<?= htmlspecialchars($classOption) ?>" 
                                    <?= ($class ?? '') === $classOption ? 'selected' : '' ?>>
                                <?= htmlspecialchars($classOption) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="text" 
                           value="<?= htmlspecialchars($class ?? '') ?>" 
                           readonly
                           class="w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    <input type="hidden" name="class" value="<?= htmlspecialchars($class ?? '') ?>">
                <?php endif; ?>
            </div>

            <!-- Date Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                <input type="date" name="date" value="<?= htmlspecialchars($date) ?>"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>

            <!-- Filter Button -->
            <div class="flex items-end">
                <button type="submit" 
                        class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($class)): ?>
        <!-- Attendance Form -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-700">
                    Record Attendance - <?= htmlspecialchars($class) ?> (<?= htmlspecialchars($date) ?>)
                </h3>
            </div>
            
            <form method="POST" action="<?= url('attendance/record') ?>">
                <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
                <input type="hidden" name="term" value="<?= htmlspecialchars($currentPeriod['term'] ?? '') ?>">
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Student
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Reason (if absent/late)
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($pupils as $pupil): ?>
                                <?php
                                $attendance = array_filter($attendanceData, function($record) use ($pupil) {
                                    return $record['pupil_id'] == $pupil['pupil_id'];
                                });
                                $attendance = reset($attendance) ?: null;
                                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($pupil['first_name'] . ' ' . $pupil['last_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($pupil['matricule']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select name="attendance[<?= $pupil['pupil_id'] ?>][status]"
                                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <option value="present" <?= ($attendance['status'] ?? '') === 'present' ? 'selected' : '' ?>>Present</option>
                                            <option value="absent" <?= ($attendance['status'] ?? '') === 'absent' ? 'selected' : '' ?>>Absent</option>
                                            <option value="late" <?= ($attendance['status'] ?? '') === 'late' ? 'selected' : '' ?>>Late</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" 
                                               name="attendance[<?= $pupil['pupil_id'] ?>][reason]"
                                               value="<?= htmlspecialchars($attendance['reason'] ?? '') ?>"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50">
                    <button type="submit" 
                            class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Save Attendance
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Class Selected</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Please select a class to record attendance.
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../views/layouts/dashboard.php';
?> 