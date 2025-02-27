<div class="space-y-2">
    <a href="<?= url('teacher/dashboard') ?>" 
       class="block px-4 py-2 hover:bg-blue-700 <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'bg-blue-900' : '' ?>">
        Dashboard
    </a>
    <a href="<?= url('teacher/class-roster') ?>" 
       class="block px-4 py-2 hover:bg-blue-700 <?= str_contains($_SERVER['REQUEST_URI'], 'class-roster') ? 'bg-blue-900' : '' ?>">
        Class Roster
    </a>
    <a href="<?= url('teacher/record-marks') ?>" 
       class="block px-4 py-2 hover:bg-blue-700 <?= str_contains($_SERVER['REQUEST_URI'], 'record-marks') ? 'bg-blue-900' : '' ?>">
        Record Marks
    </a>
</div> 