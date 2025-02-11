<?php
$title = 'Admin Dashboard';
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-700">Total Teachers</h3>
        <p class="text-3xl font-bold text-blue-600 mt-2"><?= $teacherCount ?></p>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-700">Total Pupils</h3>
        <p class="text-3xl font-bold text-green-600 mt-2"><?= $pupilCount ?></p>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-700">Total Parents</h3>
        <p class="text-3xl font-bold text-purple-600 mt-2"><?= $parentCount ?></p>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-700">Active Classes</h3>
        <p class="text-3xl font-bold text-orange-600 mt-2"><?= $classCount ?></p>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Activities</h3>
        <div class="space-y-3">
            <?php foreach ($recentActivities as $activity): ?>
            <div class="flex items-center justify-between border-b pb-2">
                <span class="text-gray-600"><?= $activity['description'] ?></span>
                <span class="text-sm text-gray-500"><?= $activity['time'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Quick Actions</h3>
        <div class="space-y-4">
            <a href="/admin/teachers/new" class="block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Add New Teacher
            </a>
            <a href="/admin/pupils/new" class="block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Register New Pupil
            </a>
            <a href="/admin/results/generate" class="block bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                Generate Report Cards
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/dashboard.php';
?> 