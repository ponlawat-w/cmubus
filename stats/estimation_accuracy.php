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
    <h3>ข้อมูลความแม่นยำการประมาณเวลา</h3>

    <?php
    if(isset($_GET['session']))
    {
        $sql = "SELECT * FROM `sessions` WHERE `id` = ?";
        $data = sql_query($sql, "i", array($_GET['session']))->fetch_array();

        $estimatedData = calculateSessionAccuracy($data);
        ?>
        <table border="1" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>ป้ายหยุด</th>
                    <th>ค่าประมาณสัมบูรณ์</th>
                    <th>ค่าประมาณสัมพัทธ์</th>
                    <th>เวลาถึงจริง</th>
                    <th>ล่าช้าสัมบูรณ์ (วินาที)</th>
                    <th>ล่าช้าสัมพัทธ์ (วินาที)</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach($estimatedData as $estimatedDatum)
            {
                echo "<tr>";
                echo "<td>{$estimatedDatum['stopName']}</td>";
                if($estimatedDatum['absoluteEstimatedTime'] == null)
                {
                    echo "<td>-</td>";
                }
                else
                {
                    echo "<td>" . date("H:i:s", $estimatedDatum['absoluteEstimatedTime']) . "</td>";
                }
                if($estimatedDatum['relativeEstimatedTime'] == null)
                {
                    echo "<td>-</td>";
                }
                else
                {
                    echo "<td>" . date("H:i:s", $estimatedDatum['relativeEstimatedTime']) . "</td>";
                }
                echo "<td><strong>" . date("H:i:s", $estimatedDatum['recordedArrivalTime']) . "</strong></td>";
                echo "<td>{$estimatedDatum['absoluteDelay']}</td>";
                echo "<td>{$estimatedDatum['relativeDelay']}</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        <?php
    }
    else if(isset($_POST['submit']))
    {
        $sessions = getSessionsBetween(new Day($_POST['date_from']), new Day($_POST['date_to']), $_POST['types'], $_POST['routes']);
        $sessions = sort_by($sessions, "start_datetime", SORT_ASC);

        ?>
        <a href="estimation_accuracy.php">ยกเลิก</a>
        <table border="1" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>เส้นทาง</th>
                    <th>เลขรถ</th>
                    <th>เวลาออก</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach($sessions as $session)
            {
                echo "<tr>";
                echo "<td><a href='estimation_accuracy.php?session={$session['id']}' target='_blank'>{$session['id']}</a></td>";
                echo "<td>" . get_text("route", $session['route'], "th") . "</td>";
                echo "<td>{$session['busno']}</td>";
                echo "<td>" . thai_date("฿ว ฿วท ฿ด ฿ปป H:i:s", $session['start_datetime']) . "</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        <?php
    }
    else
    {
        ?>
        <form action="estimation_accuracy.php" method="post">
            <strong>จากวันที่</strong>
            <select name="date_from">
                <?php
                $sql = "SELECT `date` FROM `days` ORDER BY `date` DESC";
                $results = sql_query($sql);
                while($dateData = $results->fetch_array())
                {
                    echo "<option value='{$dateData['date']}'>" . thai_date("฿วท ฿ด ฿ปป (฿ว)", $dateData['date']) . "</option>";
                }
                ?>
            </select>
            <strong>ถึง</strong>
            <select name="date_to">
                <?php
                $sql = "SELECT `date` FROM `days` ORDER BY `date` DESC";
                $results = sql_query($sql);
                while($dateData = $results->fetch_array())
                {
                    echo "<option value='{$dateData['date']}'>" . thai_date("฿วท ฿ด ฿ปป (฿ว)", $dateData['date']) . "</option>";
                }
                ?>
            </select>

            <br>

            <strong>ประเภทวัน:</strong>
            <?php
            $sql = "SELECT `id`, `name` FROM `day_types` ORDER BY `name` ASC";
            $results = sql_query($sql);
            while($dayTypeData = $results->fetch_array())
            {
                echo "<label><input type='checkbox' name='types[]' value='{$dayTypeData['id']}'> {$dayTypeData['name']}</label>";
            }
            ?>

            <br>

            <strong>เส้นทาง:</strong><br>
            <?php
            $sql = "SELECT `id`, `name`, `detail` FROM `routes` ORDER BY `name` ASC";
            $results = sql_query($sql);
            while($routeData = $results->fetch_array())
            {
                echo "<label><input type='checkbox' name='routes[]' value='{$routeData['id']}'> {$routeData['name']} ({$routeData['detail']})</label><br>";
            }
            ?>

            <br>

            <input type="submit" name="submit" value="ตกลง">
        </form>
        <?php
    }
    ?>
    </body>
</html>