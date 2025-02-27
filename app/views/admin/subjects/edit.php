<?php
$title = 'Edit Subject';
ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="<?= url('admin/subjects') ?>" class="text-blue-600 hover:text-blue-900">← Back to Subjects</a>
        <h2 class="text-2xl font-bold mt-4">Edit Subject</h2>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php foreach ($errors as $error): ?>
                <p><?= $error[0] ?? $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                Subject Name
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="<?= htmlspecialchars($subject['name']) ?>" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="code">
                Subject Code
            </label>
            <input type="text" 
                   id="code" 
                   name="code" 
                   value="<?= htmlspecialchars($subject['code']) ?>" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="class">
                Class
            </label>
            <select id="class" 
                    name="class" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required>
                <?php foreach ($classList as $key => $value): ?>
                    <option value="<?= htmlspecialchars($key) ?>" 
                            <?= $key === $subject['class'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($value) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="coefficient">
                Coefficient
            </label>
            <input type="number" 
                   id="coefficient" 
                   name="coefficient" 
                   value="<?= htmlspecialchars($subject['coefficient']) ?>" 
                   step="0.5" 
                   min="0.5" 
                   max="5"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   required>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="category">
                Category
            </label>
            <select id="category" 
                    name="category" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required>
                <?php foreach ($categories as $key => $value): ?>
                    <option value="<?= htmlspecialchars($key) ?>" 
                            <?= $key === $subject['category'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($value) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" 
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Update Subject
            </button>
            <a href="<?= url('admin/subjects') ?>" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 