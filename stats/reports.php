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
    <a href="index.php">← กลับ</a>
    <table border="1" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th>วันที่ เวลา</th>
                <th>ชนิดปัญหา</th>
                <th>ชื่อผู้รายงาน</th>
                <th>อีเมลผู้รายงาน</th>
                <th>เนื้อหา</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT `type`, `datetime`, `name`, `email`, `message` FROM `reports` ORDER BY `datetime` ASC";
        $results = sql_query($sql);
        while($reportData = $results->fetch_array())
        {
            echo "<tr>";
            echo "<td style='white-space: nowrap'>" . thai_date("฿ว ฿วท ฿ด ฿ปป H:i", $reportData['datetime']) . "</td>";
            echo "<td style='white-space: nowrap'>{$reportData['type']}</td>";
            echo "<td style='white-space: nowrap'>{$reportData['name']}</td>";
            echo "<td style='white-space: nowrap'>{$reportData['email']}</td>";
            echo "<td>" . nl2br($reportData['message']) . "</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</body>
</html>