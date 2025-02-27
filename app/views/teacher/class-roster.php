<?php
$title = 'Class Roster';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Class Roster - <?= htmlspecialchars($assignedClass) ?></h1>
                <p class="text-gray-600 mt-1">
                    Term <?= htmlspecialchars($currentPeriod['term']) ?>, 
                    <?= htmlspecialchars($currentPeriod['academic_year']) ?>
                </p>
            </div>
            <div class="flex space-x-4">
                <a href="<?= url('attendance') ?>" 
                   class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Take Attendance
                </a>
                <a href="<?= url('teacher/record-marks') ?>" 
                   class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                    Record Marks
                </a>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center space-x-4">
            <div class="flex-1">
                <input type="text" 
                       id="studentSearch" 
                       placeholder="Search students..." 
                       class="w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
    </div>

    <!-- Students List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Performance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($pupils as $pupil): ?>
                        <tr class="hover:bg-gray-50">
                            <!-- Student Info -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($pupil['first_name'] . ' ' . $pupil['last_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($pupil['matricule']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Attendance Stats -->
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-green-600">Present: <?= $pupil['attendance']['present_days'] ?? 0 ?></span>
                                        <span class="text-red-600">Absent: <?= $pupil['attendance']['absent_days'] ?? 0 ?></span>
                                        <span class="text-yellow-600">Late: <?= $pupil['attendance']['late_days'] ?? 0 ?></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Academic Performance -->
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <div class="flex items-center space-x-4">
                                        <div>
                                            <span class="font-medium">Average:</span>
                                            <span class="ml-1"><?= $pupil['term_average'] ?></span>
                                        </div>
                                        <div>
                                            <span class="font-medium">Position:</span>
                                            <span class="ml-1"><?= $pupil['position'] ?? 'N/A' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="flex space-x-3">
                                    <a href="<?= url('attendance/view/' . $pupil['pupil_id']) ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        View Attendance
                                    </a>
                                    <a href="<?= url('report-card/view/' . $pupil['pupil_id']) ?>" 
                                       class="text-green-600 hover:text-green-900">
                                        View Report Card
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($pupils)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No students found in this class.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Search Script -->
<script>
document.getElementById('studentSearch').addEventListener('input', function(e) {
    const searchText = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const studentName = row.querySelector('td:first-child').textContent.toLowerCase();
        const matricule = row.querySelector('.text-gray-500').textContent.toLowerCase();
        
        if (studentName.includes(searchText) || matricule.includes(searchText)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../views/layouts/dashboard.php';
?> 