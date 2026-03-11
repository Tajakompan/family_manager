<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id   = (int)$_SESSION["user_id"];

$months = ['Januar', 'Februar', 'Marec', 'April', 'Maj', 'Junij', 'Julij', 'Avgust', 'September', 'Oktober', 'November', 'December'];

$year_now  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$month_now = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');

$prev_year  = $year_now;
$next_year  = $year_now;
$prev_month = $month_now - 1;
$next_month = $month_now + 1;

if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month_now, $year_now);
$start_day = (int)date('N', strtotime("$year_now-$month_now-01")); // 1=pon ... 7=ned

$events_by_date = [];
for ($d = 1; $d <= $days_in_month; $d++) {
    $dateKey = sprintf('%04d-%02d-%02d', $year_now, $month_now, $d);
    $events_by_date[$dateKey] = [];
}

// pobere dogodke na datum
for ($d = 1; $d <= $days_in_month; $d++) {
    $dateKey = sprintf('%04d-%02d-%02d', $year_now, $month_now, $d);

    $sql = "SELECT id, name, event_time, whole_day, location, description, created_by_app_user_id, reminder
            FROM event
            WHERE family_id = ? AND event_date = ?
            ORDER BY (event_time IS NULL), event_time";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $family_id, $dateKey);
    $stmt->execute();
    $result = $stmt->get_result();
    $dayEvents = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($dayEvents as $e) {
        $events_by_date[$dateKey][] = [
            "id" => $e["id"],
            "name" => $e["name"],
            "event_time" => ((int)$e["whole_day"] === 1) ? "celodnevni dogodek" : ($e["event_time"] ?? ""),
            "event_time_raw" => $e["event_time"] ?? "",
            "whole_day" => (int)$e["whole_day"],
            "location" => ($e["location"] ?? "") === "" ? "/" : $e["location"],
            "description" => ($e["description"] ?? "") === "" ? "/" : $e["description"],
            "user_id" => (int)$e["created_by_app_user_id"],
            "reminder" => $e["reminder"] ?? ""
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koledar</title>

    <link rel="stylesheet" href="calendar.css">
    <link rel="stylesheet" href="../sidebar/sidebar.css">
    <link rel="stylesheet" href="../common_code/common_css.css">
    <link rel="stylesheet" href="../common_code/open_space_settings.css">
</head>

<body>
<?php include "../sidebar/sidebar.php"; ?>

<div class="sredina">

    <div class="page-header">
        <div class="title"><h2>KOLEDAR</h2></div>

        <div class="mesec">
            <a class="nav-btn" href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>"><span class="nav-arrow" aria-hidden="true">&#8249;</span></a>
            <div class="ime"><?= $months[$month_now - 1] . " " . $year_now ?></div>
            <a class="nav-btn" href="?month=<?= $next_month ?>&year=<?= $next_year ?>"><span class="nav-arrow" aria-hidden="true">&#8250;</span></a>
        </div>
    </div>

    <div class="calendar-card">
        <table id="outer_table">
            <tr>
                <th>PON</th><th>TOR</th><th>SRE</th><th>ČET</th><th>PET</th><th>SOB</th><th>NED</th>
            </tr>
            <tr>
                <?php
                $day = 1;

                // prazne celice pred 1. v mesecu
                for ($i = 1; $i < $start_day; $i++) {
                    echo "<td class='prazno outer_td'></td>";
                }

                while ($day <= $days_in_month) {
                    if ($day != 1 && (($day + $start_day - 2) % 7) == 0) {
                        echo "</tr><tr>";
                    }

                    $is_today = ($day == (int)date('j') && $month_now == (int)date('n') && $year_now == (int)date('Y'));
                    $class = $is_today ? 'danes' : '';

                    $dateKey = sprintf('%04d-%02d-%02d', $year_now, $month_now, $day);

                    $first  = $events_by_date[$dateKey][0]['name'] ?? "";
                    $second = $events_by_date[$dateKey][1]['name'] ?? "";
                    $third  = $events_by_date[$dateKey][2]['name'] ?? "";
                    $fourth = $events_by_date[$dateKey][3]['name'] ?? "";

                    echo "
                        <td class='dan $class outer_td' data-date='$dateKey'>
                            <table class='inner_table'>
                                <tr><td class='date inner_td'>$day</td></tr>
                                <tr><td class='first inner_td'>" . htmlspecialchars($first) . "</td></tr>
                                <tr><td class='second inner_td'>" . htmlspecialchars($second) . "</td></tr>
                                <tr><td class='third inner_td'>" . htmlspecialchars($third) . "</td></tr>
                                <tr><td class='fourth inner_td'>" . htmlspecialchars($fourth) . "</td></tr>
                            </table>
                        </td>";
                    $day++;
                }

                // prazne celice na koncu
                while ((($day + $start_day - 2) % 7) != 0) {
                    echo "<td class='prazno outer_td'></td>";
                    $day++;
                }

                echo "</tr>";
                ?>
        </table>
    </div>
</div>

<!-- DESNI PANEL: podrobnosti dneva -->
<div class="desno active" id="podrobnosti_dneva">
    <div id="view-empty" class="view active tekst">
        <div class="center">Klikni na dan v koledarju.</div>
    </div>

    <div id="view-day-selected" class="view tekst">
        <div class="chosen_date_data" id="date_and_day"></div>
        <div class="this_day_events">
            <!-- javascript -->
        </div>
    </div>

    <div class="add_event">Dodaj dogodek</div>
</div>

<!-- DESNI PANEL: obrazec -->
<div class="desno" id="add_event_form">
    <h3>Dodaj dogodek</h3>

    <form method="post" data-mode="add" action="add_event.php?month=<?= $month_now ?>&year=<?= $year_now ?>">
        <input type="hidden" name="event_id" id="event_id" value="">

        <div id="error_name" class="error"></div>
        <div class="input_row">
            <label>Naziv</label>
            <input type="text" name="name">
        </div>

        <div id="error_date" class="error"></div>
        <div class="input_row">
            <label>Datum</label>
            <input type="date" name="date" id="date_input">
        </div>

        <div id="error_time" class="error"></div>
        <div class="input_row">
            <label>Ura</label>
            <input type="time" name="time">
        </div>

        <div class="input_row checkbox">
            <input type="checkbox" name="whole_day" id="whole_day">
            <label for="whole_day">Celodnevni dogodek</label>
        </div>

        <div class="input_row">
            <label>Lokacija</label>
            <input type="text" name="location">
        </div>

        <div class="input_row">
            <label>Opis</label>
            <input type="text" name="description">
        </div>

        <div class="input_row">
            <label>Opomnik</label>
            <input type="datetime-local" name="reminder">
        </div>

        <div class="input_row checkbox">
            <input type="checkbox" name="just_for_creator" id="just_cre">
            <label for="just_cre">Dogodek vidim le jaz</label>
        </div>

        <div class="submit-buttons">
            <button type="reset" id="cancel_btn">Prekliči</button>
            <button type="submit" id="submit">Shrani</button>
        </div>
    </form>
</div>

<script>
    window.eventsByDate = <?= json_encode($events_by_date, JSON_UNESCAPED_UNICODE); ?>;
    window.calMonth = <?= (int)$month_now ?>;
    window.calYear  = <?= (int)$year_now ?>;
</script>

<script src="calendar_render.js"></script>
<script src="form&btn_work.js"></script>
</body>
</html>
