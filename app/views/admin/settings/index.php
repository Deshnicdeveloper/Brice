<?php
$title = 'System Settings';
ob_start();
?>

<div class="container mx-auto px-4">
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-2xl font-bold mb-6">System Settings</h2>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-6">
            <!-- Academic Settings -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-4">Academic Settings</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="academic_year">
                            Current Academic Year
                        </label>
                        <select id="academic_year" name="academic_year" 
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <?php 
                            $currentYear = date('Y');
                            for ($year = $currentYear; $year >= $currentYear - 5; $year--): 
                            ?>
                                <option value="<?= $year ?>" <?= $year == $academicYear ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="current_term">
                            Current Term
                        </label>
                        <select id="current_term" name="current_term" 
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="1" <?= $currentTerm == 1 ? 'selected' : '' ?>>First Term</option>
                            <option value="2" <?= $currentTerm == 2 ? 'selected' : '' ?>>Second Term</option>
                            <option value="3" <?= $currentTerm == 3 ? 'selected' : '' ?>>Third Term</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- System Settings -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-4">System Settings</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="school_name">
                            School Name
                        </label>
                        <input type="text" id="school_name" name="school_name" 
                               value="<?= $_ENV['APP_NAME'] ?>"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="results_per_page">
                            Results Per Page
                        </label>
                        <select id="results_per_page" name="results_per_page" 
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 