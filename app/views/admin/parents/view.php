<?php
$title = 'View Parent Details';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Parent Details</h2>
            <div class="space-x-2">
                <a href="<?= url('admin/parents') ?>" 
                   class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    Back to List
                </a>
                <a href="<?= url('admin/parents/edit/' . $parent['parent_id']) ?>" 
                   class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Edit Parent
                </a>
            </div>
        </div>
    </div>

    <!-- Parent Information Card -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Matricule</p>
                    <p class="font-semibold"><?= htmlspecialchars($parent['matricule']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Username</p>
                    <p class="font-semibold"><?= htmlspecialchars($parent['username']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="font-semibold"><?= htmlspecialchars($parent['email']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Phone</p>
                    <p class="font-semibold"><?= htmlspecialchars($parent['phone']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        <?= $parent['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= ucfirst(htmlspecialchars($parent['status'])) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Registered Pupils Section -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Registered Pupils</h3>
        </div>
        
        <?php if (!empty($pupils)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matricule</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pupils as $pupil): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= htmlspecialchars($pupil['matricule']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= htmlspecialchars($pupil['first_name'] . ' ' . $pupil['last_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= htmlspecialchars($pupil['class']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?= $pupil['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= ucfirst(htmlspecialchars($pupil['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="<?= url('admin/pupils/view/' . $pupil['pupil_id']) ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                    <a href="<?= url('admin/pupils/edit/' . $pupil['pupil_id']) ?>" 
                                       class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="px-6 py-4 text-center text-gray-500">
                No pupils registered under this parent.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 