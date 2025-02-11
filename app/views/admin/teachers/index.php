<?php
$title = 'Manage Teachers';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
    <h2 class="text-xl font-semibold">Teachers List</h2>
    <a href="/Brice/admin/teachers/add" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
        Add New Teacher
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= $_SESSION['success']; ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matricule</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($teachers as $teacher): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap"><?= $teacher['matricule'] ?></td>
                <td class="px-6 py-4 whitespace-nowrap"><?= $teacher['name'] ?></td>
                <td class="px-6 py-4 whitespace-nowrap"><?= $teacher['email'] ?></td>
                <td class="px-6 py-4 whitespace-nowrap"><?= $teacher['assigned_class'] ?></td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        <?= $teacher['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= ucfirst($teacher['status']) ?>
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a href="/admin/teachers/edit/<?= $teacher['teacher_id'] ?>" class="text-blue-600 hover:text-blue-900">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 