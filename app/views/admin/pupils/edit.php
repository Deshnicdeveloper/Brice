<?php
$title = 'Edit Pupil';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="<?= url('admin/pupils/view/' . $pupil['pupil_id']) ?>" 
           class="text-blue-600 hover:text-blue-900">
            ← Back to Pupil Details
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">
                Edit Pupil: <?= htmlspecialchars($pupil['first_name'] . ' ' . $pupil['last_name']) ?>
            </h2>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 mx-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm leading-5 font-medium text-red-800">
                            There were errors with your submission
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <?php foreach ($errors as $field => $error): ?>
                                    <li><?= $error[0] ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= url('admin/pupils/edit/' . $pupil['pupil_id']) ?>" method="POST" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="first_name">
                        First Name
                    </label>
                    <input type="text" id="first_name" name="first_name" 
                           value="<?= htmlspecialchars($pupil['first_name']) ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="last_name">
                        Last Name
                    </label>
                    <input type="text" id="last_name" name="last_name" 
                           value="<?= htmlspecialchars($pupil['last_name']) ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="date_of_birth">
                        Date of Birth
                    </label>
                    <input type="date" id="date_of_birth" name="date_of_birth" 
                           value="<?= htmlspecialchars($pupil['date_of_birth']) ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="gender">
                        Gender
                    </label>
                    <select id="gender" name="gender" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="M" <?= $pupil['gender'] === 'M' ? 'selected' : '' ?>>Male</option>
                        <option value="F" <?= $pupil['gender'] === 'F' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="parent_id">
                        Parent
                    </label>
                    <select id="parent_id" name="parent_id" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <?php foreach ($parents as $parent): ?>
                            <option value="<?= $parent['parent_id'] ?>" 
                                    <?= $pupil['parent_id'] === $parent['parent_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($parent['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="class">
                        Class
                    </label>
                    <select id="class" name="class" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <?php foreach ($classList as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $pupil['class'] === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($value) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="status">
                        Status
                    </label>
                    <select id="status" name="status" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="active" <?= $pupil['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $pupil['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="<?= url('admin/pupils/view/' . $pupil['pupil_id']) ?>" 
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 