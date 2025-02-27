<?php
$title = 'Report Cards';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">EDUCARE's Report Cards Management</h1>
                <p class="text-gray-600 mt-1">Generate and manage student report cards</p>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Filter Options</h2>
        <form class="grid grid-cols-1 md:grid-cols-4 gap-6" method="GET">
            <!-- Class Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select name="class" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Select Class</option>
                    <?php foreach ($classList as $classOption): ?>
                        <option value="<?= htmlspecialchars($classOption) ?>" 
                                <?= $class === $classOption ? 'selected' : '' ?>>
                            <?= htmlspecialchars($classOption) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Academic Year Selection -->
            

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
        <!-- Results Section -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-700">
                    Student List - <?= htmlspecialchars($class) ?>
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Matricule
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Student Name
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pupils as $pupil): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($pupil['matricule']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($pupil['first_name'] . ' ' . $pupil['last_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= $pupil['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= ucfirst(htmlspecialchars($pupil['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="<?= url('report-card/view/' . $pupil['pupil_id'] . '/' . $term) ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-4">
                                        View
                                    </a>
                                    
                                    <a href="<?= url('report-card/print/' . $pupil['pupil_id'] . '/' . $term) ?>" 
                                       target="_blank"
                                       class="text-gray-600 hover:text-gray-900">
                                        Print
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Class Selected</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Please select a class from the filter options above to view student report cards.
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 