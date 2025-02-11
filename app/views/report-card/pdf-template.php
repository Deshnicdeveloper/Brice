<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th, td {
        border: 1px solid #000;
        padding: 8px;
        text-align: center;
    }
    th {
        background-color: #f0f0f0;
    }
    .header {
        text-align: center;
        margin-bottom: 20px;
    }
    .student-info {
        margin-bottom: 20px;
    }
    .red-text { color: #FF0000; }
    .blue-text { color: #0000FF; }
    .comments {
        margin-top: 20px;
        border: 1px solid #000;
        padding: 10px;
    }
    .footer {
        margin-top: 30px;
    }
</style>

<div class="header">
    <h1><?= $_ENV['SCHOOL_NAME'] ?></h1>
    <p>Academic Year: <?= $currentPeriod['academic_year'] ?><br>
    Term <?= $currentPeriod['term'] ?> Report Card</p>
</div>

<!-- Rest of the content similar to print.php but with PDF-specific adjustments -->
<!-- Continue with the same structure but remove onload="window.print()" --> 