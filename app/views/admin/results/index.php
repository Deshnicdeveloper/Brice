<?php
$title = 'Results Management';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
    <h2 class="text-xl font-semibold">Results Management</h2>
    <?php if (in_array($_SESSION['user_type'], ['admin', 'teacher'])): ?>
        <a href="/admin/results/enter" 
           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Enter Results
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= $_SESSION['success']; ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<!-- Filter Form -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" action="/admin/results" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Class</label>
            <select name="class" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Class</option>
                <?php foreach ($classes as $class => $label): ?>
                    <option value="<?= $class ?>" <?= $selectedClass === $class ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

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

<?php if (isset($results)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4">
            <input type="text" id="searchInput" 
                   placeholder="Search pupils..." 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matricule</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    $currentPupil = null;
                    $pupilResults = [];
                    foreach ($results as $result) {
                        if ($currentPupil !== $result['pupil_id']) {
                            if ($currentPupil !== null) {
                                // Display the previous pupil's row
                                $average = $this->result->calculateTermAverage($pupilResults);
                                ?>
                                <tr class="searchable-row">
                                    <td class="px-6 py-4 whitespace-nowrap"><?= $pupilResults[0]['matricule'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= $pupilResults[0]['first_name'] . ' ' . $pupilResults[0]['last_name'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= number_format($average, 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= $pupilResults[0]['ranking'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="/admin/results/pupil/<?= $currentPupil ?>" 
                                           class="text-blue-600 hover:text-blue-900">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                            $currentPupil = $result['pupil_id'];
                            $pupilResults = [];
                        }
                        $pupilResults[] = $result;
                    }
                    
                    // Display the last pupil's row
                    if (!empty($pupilResults)) {
                        $average = $this->result->calculateTermAverage($pupilResults);
                        ?>
                        <tr class="searchable-row">
                            <td class="px-6 py-4 whitespace-nowrap"><?= $pupilResults[0]['matricule'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= $pupilResults[0]['first_name'] . ' ' . $pupilResults[0]['last_name'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= number_format($average, 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= $pupilResults[0]['ranking'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="/admin/results/pupil/<?= $currentPupil ?>" 
                                   class="text-blue-600 hover:text-blue-900">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('.searchable-row');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchValue) ? '' : 'none';
        });
    });
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 