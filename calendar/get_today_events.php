<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  header("Location: ../entry/login.php");
  exit;
}

$family_id = (int)$_SESSION["family_id"];
$dateKey = trim($_POST["date_key"]);
if ($dateKey === "") {
  header("Location: ../entry/login.php");
  exit;
}

$sql = "SELECT id, name, event_time, whole_day, location, description, created_by_app_user_id, reminder
            FROM event
            WHERE family_id = ? AND event_date = ?
            ORDER BY (event_time IS NULL), event_time";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $family_id, $dateKey);
$stmt->execute();
$result = $stmt->get_result();
$out = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


header("Content-Type: application/json; charset=utf-8");
echo json_encode($out);
?>