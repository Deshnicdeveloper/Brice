<?php
$title = 'Pupil Attendance';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    <?= htmlspecialchars($pupil['first_name'] . ' ' . $pupil['last_name']) ?>'s Attendance
                </h1>
                <p class="text-gray-600 mt-1">
                    Class: <?= htmlspecialchars($pupil['class']) ?> | 
                    Matricule: <?= htmlspecialchars($pupil['matricule']) ?>
                </p>
                <p class="text-gray-600">
                    Academic Year: <?= htmlspecialchars($academicYear) ?>
                </p>
            </div>
            <a href="javascript:history.back()" 
               class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                Back
            </a>
        </div>
    </div>

    <!-- Term Filter -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="flex items-center space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Term</label>
                <select name="term" 
                        onchange="this.form.submit()"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">All Terms</option>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <option value="<?= $i ?>" <?= ($term == $i) ? 'selected' : '' ?>>
                            Term <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total Days -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Days</p>
                    <p class="text-lg font-semibold"><?= $statistics['total_days'] ?? 0 ?></p>
                </div>
            </div>
        </div>

        <!-- Present Days -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Present Days</p>
                    <p class="text-lg font-semibold">
                        <?= $statistics['present_days'] ?? 0 ?>
                        <span class="text-sm text-gray-500">
                            (<?= $statistics['total_days'] ? round(($statistics['present_days'] / $statistics['total_days']) * 100) : 0 ?>%)
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Absent Days -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Absent Days</p>
                    <p class="text-lg font-semibold">
                        <?= $statistics['absent_days'] ?? 0 ?>
                        <span class="text-sm text-gray-500">
                            (<?= $statistics['total_days'] ? round(($statistics['absent_days'] / $statistics['total_days']) * 100) : 0 ?>%)
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Late Days -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Late Days</p>
                    <p class="text-lg font-semibold">
                        <?= $statistics['late_days'] ?? 0 ?>
                        <span class="text-sm text-gray-500">
                            (<?= $statistics['total_days'] ? round(($statistics['late_days'] / $statistics['total_days']) * 100) : 0 ?>%)
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Records -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-700">Attendance History</h3>
                <button onclick="window.print()" 
                        class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Print Report
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($attendanceRecords)): ?>
                        <?php foreach ($attendanceRecords as $record): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?= date('d M Y', strtotime($record['date'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php
                                        echo match($record['status']) {
                                            'present' => 'bg-green-100 text-green-800',
                                            'absent' => 'bg-red-100 text-red-800',
                                            'late' => 'bg-yellow-100 text-yellow-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        ?>">
                                        <?= ucfirst(htmlspecialchars($record['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Term <?= htmlspecialchars($record['term']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= $record['reason'] ? htmlspecialchars($record['reason']) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No attendance records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../views/layouts/dashboard.php';
?> 