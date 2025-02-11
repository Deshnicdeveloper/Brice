<div class="space-y-2">
    <a href="<?= url('admin/dashboard') ?>" class="block px-4 py-2 hover:bg-blue-700">Dashboard</a>
    <a href="<?= url('admin/teachers') ?>" class="block px-4 py-2 hover:bg-blue-700">Teachers</a>
    <a href="<?= url('admin/pupils') ?>" class="block px-4 py-2 hover:bg-blue-700">Pupils</a>
    <a href="<?= url('admin/parents') ?>" class="block px-4 py-2 hover:bg-blue-700">Parents</a>
    <a href="<?= url('admin/subjects') ?>" class="block px-4 py-2 hover:bg-blue-700">Subjects</a>
    <a href="<?= url('admin/report-cards') ?>" class="block px-4 py-2 hover:bg-blue-700">Report Cards</a>
    <a href="<?= url('admin/settings') ?>" class="block px-4 py-2 hover:bg-blue-700">Settings</a>
    <a href="<?= url('admin/marking-periods') ?>" 
       class="<?= str_contains($_SERVER['REQUEST_URI'], 'marking-periods') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> 
       block px-3 py-2 rounded-md text-base font-medium">
        Marking Periods
    </a>
</div> 