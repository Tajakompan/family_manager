<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: calendar.php");
    exit;
}

$id = (int)($_POST["event_id"] ?? 0);
$name = trim($_POST["name"] ?? "");
$date = $_POST["date"] ?? "";
$whole_day = isset($_POST["whole_day"]) ? 1 : 0;
$time = $_POST["time"] ?? "";
$location = trim($_POST["location"] ?? "");
$description = trim($_POST["description"] ?? "");
$reminder = $_POST["reminder"] ?? "";
$just_for_creator= isset($_POST["just_for_creator"]) ? 1 : 0;

if ($id <= 0 || $name === "" || $date === "") {
    $m = (int)($_GET["month"] ?? date("n"));
    $y = (int)($_GET["year"] ?? date("Y"));
    header("Location: calendar.php?month=$m&year=$y");
    exit;
}

// Če celodnevni -> time NULL
if ($whole_day === 1) {
    $time = null;
} else {
    if (trim($time) === "") $time = null;
}

if (trim((string)$reminder) === "") $reminder = null;

$sql = "UPDATE event
        SET name = ?,
            event_date = ?,
            event_time = ?,
            whole_day = ?,
            location = ?,
            description = ?,
            reminder = ?,
            just_for_creator = ?
        WHERE id = ? AND family_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssisssiii",
    $name,
    $date,
    $time,
    $whole_day,
    $location,
    $description,
    $reminder,
    $just_for_creator,
    $id,
    $family_id
);
$stmt->execute();
$stmt->close();

$month_now = (int)($_GET["month"] ?? date("n"));
$year_now  = (int)($_GET["year"] ?? date("Y"));
$selected_user_id = (int)($_GET["user_id"] ?? 0);

header("Location: calendar.php?month=$month_now&year=$year_now&user_id=$selected_user_id");
exit;

