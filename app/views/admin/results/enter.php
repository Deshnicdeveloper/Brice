<?php
$title = 'Enter Results';
ob_start();
?>

<div class="mb-6">
    <a href="/admin/results" class="text-blue-600 hover:text-blue-900">← Back to Results</a>
    <h2 class="text-xl font-semibold mt-4">Enter Results</h2>
</div>

<?php if (isset($errors['database'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= $errors['database'] ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Class Selection Form -->
    <form id="classSelectionForm" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Class</label>
            <select name="class" required id="classSelect"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Class</option>
                <?php foreach ($classes as $class => $label): ?>
                    <option value="<?= $class ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Academic Year</label>
            <select name="academic_year" required id="yearSelect"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <?php foreach ($academicYears as $year): ?>
                    <option value="<?= $year ?>"><?= $year ?>-<?= $year + 1 ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Term</label>
            <select name="term" required id="termSelect"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <?php foreach ($terms as $term): ?>
                    <option value="<?= $term ?>">Term <?= $term ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <!-- Results Entry Form (loaded dynamically) -->
    <div id="resultsForm" class="hidden">
        <form method="POST" action="/admin/results/enter" id="marksForm">
            <input type="hidden" name="class" id="hiddenClass">
            <input type="hidden" name="academic_year" id="hiddenYear">
            <input type="hidden" name="term" id="hiddenTerm">
            
            <div id="resultsTable" class="overflow-x-auto mt-6">
                <!-- Table will be loaded here -->
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" 
                        class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Save Results
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('classSelectionForm').addEventListener('change', function() {
    const classValue = document.getElementById('classSelect').value;
    const yearValue = document.getElementById('yearSelect').value;
    const termValue = document.getElementById('termSelect').value;

    if (classValue && yearValue && termValue) {
        // Update hidden fields
        document.getElementById('hiddenClass').value = classValue;
        document.getElementById('hiddenYear').value = yearValue;
        document.getElementById('hiddenTerm').value = termValue;

        // Fetch pupils and subjects for the selected class
        fetch(`/api/results/get-class-data?class=${classValue}&year=${yearValue}&term=${termValue}`)
            .then(response => response.json())
            .then(data => {
                const table = generateResultsTable(data.pupils, data.subjects, data.existingResults);
                document.getElementById('resultsTable').innerHTML = table;
                document.getElementById('resultsForm').classList.remove('hidden');
            })
            .catch(error => console.error('Error:', error));
    }
});

function generateResultsTable(pupils, subjects, existingResults) {
    // Generate the HTML table for entering results
    // This will be implemented in the next step
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 