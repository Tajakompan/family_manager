<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/reminder_helpers.php";

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

$selected_user_id = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : 0;

$events_by_date = [];
for ($d = 1; $d <= $days_in_month; $d++) {
    $dateKey = sprintf('%04d-%02d-%02d', $year_now, $month_now, $d);
    $events_by_date[$dateKey] = [];
}

$users = [];
$user_sql = "SELECT id, name, surname
             FROM app_user
             WHERE family_id = ?
             ORDER BY name, surname";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $family_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

while ($row = $user_result->fetch_assoc()) {
    $users[] = $row;
}
$user_stmt->close();

$events_by_date = [];

for ($d = 1; $d <= $days_in_month; $d++) {
    $dateKey = sprintf('%04d-%02d-%02d', $year_now, $month_now, $d);

    if ($selected_user_id > 0) {
        if ($selected_user_id === $user_id) {
            $sql = "SELECT 
                        e.id,
                        e.name,
                        e.event_time,
                        e.whole_day,
                        e.location,
                        e.description,
                        a.name AS user_name,
                        e.reminder,
                        e.just_for_creator
                    FROM event e
                    INNER JOIN app_user a ON e.created_by_app_user_id = a.id
                    WHERE e.family_id = ?
                      AND e.event_date = ?
                      AND e.created_by_app_user_id = ?
                    ORDER BY (e.event_time IS NULL), e.event_time";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $family_id, $dateKey, $selected_user_id);
        } else {
            $sql = "SELECT 
                        e.id,
                        e.name,
                        e.event_time,
                        e.whole_day,
                        e.location,
                        e.description,
                        a.name AS user_name,
                        e.reminder,
                        e.just_for_creator
                    FROM event e
                    INNER JOIN app_user a ON e.created_by_app_user_id = a.id
                    WHERE e.family_id = ?
                      AND e.event_date = ?
                      AND e.created_by_app_user_id = ?
                      AND e.just_for_creator = 0
                    ORDER BY (e.event_time IS NULL), e.event_time";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $family_id, $dateKey, $selected_user_id);
        }
    } else {
        $sql = "SELECT 
                    e.id,
                    e.name,
                    e.event_time,
                    e.whole_day,
                    e.location,
                    e.description,
                    a.name AS user_name,
                    e.reminder,
                    e.just_for_creator
                FROM event e
                INNER JOIN app_user a ON e.created_by_app_user_id = a.id
                WHERE e.family_id = ?
                  AND e.event_date = ?
                  AND (
                        e.created_by_app_user_id = ?
                        OR e.just_for_creator = 0
                  )
                ORDER BY (e.event_time IS NULL), e.event_time";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $family_id, $dateKey, $user_id);
    }

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
            "user_name" => $e["user_name"],
            "reminder_input" => formatReminderForInput($e["reminder"] ?? null),
            "reminder_display" => formatReminderForDisplay($e["reminder"] ?? null),
            "just_for_creator" => (int)($e["just_for_creator"] ?? 0)
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Familoop - koledar</title>

    <link rel="stylesheet" href="../../frontend/calendar/calendar.css">
    <link rel="stylesheet" href="../../frontend/sidebar/sidebar.css">
    <link rel="stylesheet" href="../../frontend/common_code/common_css.css">
    <link rel="stylesheet" href="../../frontend/common_code/open_space_settings.css">
    <link rel="stylesheet" href="../../frontend/common_code/form_window.css">
</head>

<body>
<?php include "../sidebar/sidebar.php"; ?>

<div class="sredina">

    <div class="page-header">
        <div class="title"><h2>KOLEDAR</h2></div>

        <div class="mesec">
            <div class="space"></div>
            <a class="nav-btn" href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>&user_id=<?= $selected_user_id ?>">
                <span class="nav-arrow" aria-hidden="true">&#8249;</span>
            </a>
            <div class="ime"><?= $months[$month_now - 1] . " " . $year_now ?></div>
            <a class="nav-btn" href="?month=<?= $next_month ?>&year=<?= $next_year ?>&user_id=<?= $selected_user_id ?>">
                <span class="nav-arrow" aria-hidden="true">&#8250;</span>
            </a>            
            <div class="space" id="select_user">
                <form method="get" class="calendar-filter">
                    <input type="hidden" name="month" value="<?= $month_now ?>">
                    <input type="hidden" name="year" value="<?= $year_now ?>">

                    <select name="user_id" id="user_filter" class="calendar-select" onchange="this.form.submit()">
                        <option value="0" <?= $selected_user_id === 0 ? "selected" : "" ?>>Dogodki vseh</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= (int)$user["id"] ?>" <?= $selected_user_id === (int)$user["id"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($user["name"] . " " . $user["surname"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
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

                while ((($day + $start_day - 2) % 7) != 0) {
                    echo "<td class='prazno outer_td'></td>";
                    $day++;
                }

                echo "</tr>";
                ?>
        </table>
    </div>
</div>

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

<div class="desno" id="add_event_form">
    <h3>Dodaj dogodek</h3>

    <form method="post" data-mode="add" action="add_event.php?month=<?= $month_now ?>&year=<?= $year_now ?>&user_id=<?= $selected_user_id ?>">
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
    window.eventsByDate = <?= json_encode($events_by_date); ?>;
    window.calMonth = <?= (int)$month_now ?>;
    window.calYear  = <?= (int)$year_now ?>;
    window.selectedUserId = <?= (int)$selected_user_id ?>;
</script>

<script src="../../frontend/calendar/calendar_render.js"></script>
<script src="../../frontend/calendar/form&btn_work.js"></script>
</body>
</html>
