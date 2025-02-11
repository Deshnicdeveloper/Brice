<?php
$title = 'Entity Audit Trail';
ob_start();
?>

<div class="mb-6">
    <a href="/admin/audit" class="text-blue-600 hover:text-blue-900">← Back to Audit Logs</a>
    <h2 class="text-xl font-semibold mt-4">
        Audit Trail for <?= ucfirst($entityType) ?>: <?= $entity['name'] ?> 
        <span class="text-gray-500">(<?= $entity['matricule'] ?>)</span>
    </h2>
</div>

<div class="space-y-4">
    <?php foreach ($logs as $log): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start">
                <div>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        <?= $log['action'] === 'create' ? 'bg-green-100 text-green-800' : 
                           ($log['action'] === 'update' ? 'bg-blue-100 text-blue-800' : 
                            'bg-red-100 text-red-800') ?>">
                        <?= ucfirst($log['action']) ?>
                    </span>
                    <span class="ml-2 text-sm text-gray-500">
                        <?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?>
                    </span>
                </div>
                <div class="text-sm text-gray-500">
                    by <?= $log['user_type'] ?> #<?= $log['user_id'] ?>
                </div>
            </div>

            <?php if ($log['action'] === 'update' && $log['old_values'] && $log['new_values']): ?>
                <div class="mt-4">
                    <h4 class="text-sm font-medium text-gray-900">Changes:</h4>
                    <div class="mt-2 text-sm text-gray-600">
                        <?php
                        $oldValues = json_decode($log['old_values'], true);
                        $newValues = json_decode($log['new_values'], true);
                        foreach ($newValues as $key => $value):
                            if ($oldValues[$key] !== $value):
                        ?>
                            <div class="flex space-x-2 items-start">
                                <span class="font-medium"><?= ucfirst($key) ?>:</span>
                                <span class="line-through text-red-600"><?= $oldValues[$key] ?></span>
                                <span class="text-green-600"><?= $value ?></span>
                            </div>
                        <?php 
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/dashboard.php';
?> 