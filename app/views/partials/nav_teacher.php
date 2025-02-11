<div class="space-y-2">
    <a href="<?= url('teacher/dashboard') ?>" 
       class="block px-4 py-2 hover:bg-blue-700 <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'bg-blue-900' : '' ?>">
        Dashboard
    </a>
    <a href="<?= url('teacher/record-marks') ?>" 
       class="block px-4 py-2 hover:bg-blue-700 <?= str_contains($_SERVER['REQUEST_URI'], 'record-marks') ? 'bg-blue-900' : '' ?>">
        Record Marks
    </a>
    <a href="<?= url('teacher/view-results') ?>" 
       class="block px-4 py-2 hover:bg-blue-700 <?= str_contains($_SERVER['REQUEST_URI'], 'view-results') ? 'bg-blue-900' : '' ?>">
        View Results
    </a>
</div> 