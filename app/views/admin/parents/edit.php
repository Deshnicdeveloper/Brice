<?php
$title = 'Edit Parent';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-2xl font-bold mb-6">Edit Parent</h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('admin/parents/edit/' . $parent['parent_id']) ?>" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                    Username
                </label>
                <input type="text" id="username" name="username" required
                       value="<?= htmlspecialchars($parent['username']) ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email
                </label>
                <input type="email" id="email" name="email" required
                       value="<?= htmlspecialchars($parent['email']) ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                    Phone
                </label>
                <input type="tel" id="phone" name="phone" required
                       value="<?= htmlspecialchars($parent['phone']) ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                    Status
                </label>
                <select id="status" name="status" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    <option value="active" <?= $parent['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $parent['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="flex items-center justify-between">
                <a href="<?= url('admin/parents') ?>" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
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