<?php session_start(); session_write_close(); ?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ข้อมูลสถิติ→ความแม่นยำการประมาณเวลา</title>
        <link rel="stylesheet" type="text/css" href="styles.css">
        <?php
        include_once("../lib/lib.inc.php");
        include_once("../lib/app.inc.php");
        include_once("library.php");
        ?>
    </head>
    <body>
        <a href="../admin">← กลับ</a>
        <p><a href="estimation_accuracy.php">ความแม่นยำการประมาณเวลา</a></p>
        <p><a href="estimation_accuracy_run.php">คำนวณความแม่นยำการประมาณเวลา</a></p>
        <p><a href="reports.php">รายงานปัญหา</a></p>
    </body>
</html>