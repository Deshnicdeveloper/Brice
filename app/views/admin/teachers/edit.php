<?php
$title = 'Edit Teacher';
ob_start();
?>

<div class="mb-6">
    <h2 class="text-xl font-semibold">Edit Teacher</h2>
</div>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= $error ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <form method="POST" action="/admin/teachers/edit/<?= $teacher['teacher_id'] ?>" class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Matricule</label>
            <input type="text" value="<?= $teacher['matricule'] ?>" disabled
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="<?= $teacher['name'] ?>" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" value="<?= $teacher['email'] ?>" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <input type="tel" name="phone" value="<?= $teacher['phone'] ?>" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Assigned Class</label>
            <select name="assigned_class" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <?php foreach(['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6'] as $class): ?>
                    <option value="<?= $class ?>" <?= $teacher['assigned_class'] === $class ? 'selected' : '' ?>>
                        <?= $class ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="active" <?= $teacher['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $teacher['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="/admin/teachers" 
               class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Cancel
            </a>
            <button type="submit" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Update Teacher
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 