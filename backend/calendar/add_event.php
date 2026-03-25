<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/reminder_helpers.php";


if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id   = (int)$_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Če nekdo odpre direktno
    header("Location: calendar.php");
    exit;
}

// --- preberi podatke iz forme ---
$name            = trim($_POST["name"] ?? "");
$date            = $_POST["date"] ?? "";
$whole_day       = isset($_POST["whole_day"]) ? 1 : 0;
$time            = $_POST["time"] ?? "";
$location        = trim($_POST["location"] ?? "");
$description     = trim($_POST["description"] ?? "");
$reminder        = $_POST["reminder"] ?? "";
$just_for_creator= isset($_POST["just_for_creator"]) ? 1 : 0;

// --- minimalna validacija (JS že dela večino) ---
if ($name === "" || $date === "") {
    // fallback redirect
    $m = (int)($_GET["month"] ?? date("n"));
    $y = (int)($_GET["year"] ?? date("Y"));
    header("Location: calendar.php?month=$m&year=$y");
    exit;
}

// Če je celodnevni dogodek, ura nima smisla
if ($whole_day === 1) {
    $time = null; // bo šel v bazo kot NULL
} else {
    // če ni celodneven, pa user ni vpisal časa -> prazno v NULL (da ne zafrkava ORDER BY)
    if (trim($time) === "") $time = null;
}

$reminder = normalizeReminderInput($reminder);

if (($reminder === null) && trim((string)($_POST["reminder"] ?? "")) !== "") {
    $m = (int)($_GET["month"] ?? date("n"));
    $y = (int)($_GET["year"] ?? date("Y"));
    header("Location: calendar.php?month=$m&year=$y");
    exit;
}


// --- INSERT ---
$sql = "INSERT INTO event
    (name, event_date, event_time, whole_day, location, description, reminder, just_for_creator, family_id, created_by_app_user_id)
    VALUES (?,?,?,?,?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    exit("Napaka pri prepare().");
}

// bind_param: s = string, i = int
// event_time in reminder sta lahko NULL -> še vedno bindamo kot "s", mysqli bo sprejel null
$stmt->bind_param(
    "sssissssii",
    $name,
    $date,
    $time,
    $whole_day,
    $location,
    $description,
    $reminder,
    $just_for_creator,
    $family_id,
    $user_id
);

$stmt->execute();
$stmt->close();

// --- redirect nazaj na isti mesec/leto (prenesemo prek GET) ---
$month_now = (int)($_GET["month"] ?? date("n"));
$year_now  = (int)($_GET["year"] ?? date("Y"));
$selected_user_id = (int)($_GET["user_id"] ?? 0);

header("Location: calendar.php?month=$month_now&year=$year_now&user_id=$selected_user_id");
exit;

