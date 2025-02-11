<?php
$title = 'Edit Subject';
ob_start();
?>

<div class="mb-6">
    <a href="/admin/subjects" class="text-blue-600 hover:text-blue-900">← Back to Subjects</a>
    <h2 class="text-xl font-semibold mt-4">
        Edit Subject: <?= $subject['name'] ?>
        <span class="text-gray-500">(<?= $subject['code'] ?>)</span>
    </h2>
</div>

<?php if (isset($errors['database'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= $errors['database'] ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <form method="POST" action="/admin/subjects/edit/<?= $subject['subject_id'] ?>" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" required
                       value="<?= $_POST['name'] ?? $subject['name'] ?>"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <?php if (isset($errors['name'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['name'][0] ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Code</label>
                <input type="text" name="code" required
                       value="<?= $_POST['code'] ?? $subject['code'] ?>"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <?php if (isset($errors['code'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['code'][0] ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Class</label>
                <select name="class" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <?php foreach ($classList as $class => $label): ?>
                        <option value="<?= $class ?>" 
                                <?= $subject['class'] === $class ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['class'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['class'][0] ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Coefficient</label>
                <input type="number" name="coefficient" required step="0.5" min="0.5" max="5"
                       value="<?= $_POST['coefficient'] ?? $subject['coefficient'] ?>"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <?php if (isset($errors['coefficient'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['coefficient'][0] ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <?php foreach ($categories as $value => $label): ?>
                        <option value="<?= $value ?>" 
                                <?= $subject['category'] === $value ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['category'][0] ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex justify-end space-x-4 mt-6">
            <a href="/admin/subjects" 
               class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Cancel
            </a>
            <button type="submit" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Update Subject
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 