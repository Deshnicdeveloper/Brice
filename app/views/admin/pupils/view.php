<?php
$title = 'View Pupil';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="<?= url('admin/pupils') ?>" class="text-blue-600 hover:text-blue-900">
            ← Back to Pupils List
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">
                <?= htmlspecialchars($pupil['first_name'] . ' ' . $pupil['last_name']) ?>
                <span class="text-sm text-gray-500 ml-2">
                    (<?= htmlspecialchars($pupil['matricule']) ?>)
                </span>
            </h2>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Personal Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                <dl class="grid grid-cols-1 gap-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                        <dd class="text-sm text-gray-900"><?= htmlspecialchars($pupil['date_of_birth']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                        <dd class="text-sm text-gray-900"><?= $pupil['gender'] === 'M' ? 'Male' : 'Female' ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Current Class</dt>
                        <dd class="text-sm text-gray-900"><?= htmlspecialchars($pupil['class']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Admission Date</dt>
                        <dd class="text-sm text-gray-900"><?= htmlspecialchars($pupil['admission_date']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $pupil['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= ucfirst(htmlspecialchars($pupil['status'])) ?>
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Parent Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Parent Information</h3>
                <dl class="grid grid-cols-1 gap-3">
                    <?php if (isset($parent)): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Parent Name</dt>
                            <dd class="text-sm text-gray-900"><?= htmlspecialchars($parent['username']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900"><?= htmlspecialchars($parent['email']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="text-sm text-gray-900"><?= htmlspecialchars($parent['phone']) ?></dd>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">No parent information available</p>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-end space-x-3">
                <a href="<?= url('admin/pupils/edit/' . $pupil['pupil_id']) ?>" 
                   class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Edit Pupil
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 