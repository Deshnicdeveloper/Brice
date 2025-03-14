<?php
// This should be your admin dashboard layout template
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - <?= $_ENV['APP_NAME'] ?></title>
    <link href="<?= url('public/css/styles.css') ?>" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 bg-blue-800 text-white">
        <div class="p-4">
            <h2 class="text-2xl font-bold"><?= $_ENV['APP_NAME'] ?></h2>
            <p class="text-white"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></p>
        </div>
        
        <nav class="mt-8">
            <?php if (isset($_SESSION['user_type'])): ?>
                <?php include __DIR__ . "/../partials/nav_{$_SESSION['user_type']}.php"; ?>
            <?php endif; ?>
            <a href="<?= url('admin/marking-periods') ?>" 
               class="<?= str_contains($_SERVER['REQUEST_URI'], 'marking-periods') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> 
               px-3 py-2 rounded-md text-sm font-medium">
                Marking Periods
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold"><?= $title ?? 'Dashboard' ?></h1>
            <a href="<?= url('logout') ?>" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                Logout
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Page Content -->
        <?= $content ?>
    </div>
</body>
</html> 