<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id = (int)$_SESSION["user_id"];

$new_task = trim($_POST["new_task"] ?? "");
$details = trim($_POST["details"] ?? "");
$no_date = isset($_POST["no_date"]) ? 1 : 0;
$points = (int)($_POST["points"] ?? 0);
$to_do_by = trim($_POST["to_do_by"] ?? "");
$to_do_by = ($no_date === 1 || $to_do_by === "") ? null : $to_do_by;


if ($new_task === "") {
    header("Location: tasks.php");
    exit;
}

$sql = "INSERT INTO task (name, to_do_by, no_date, details, points, created_by_app_user_id, family_id) VALUES (?, ?, ?, ?, ?, ?, ?);";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssisiii", $new_task, $to_do_by, $no_date, $details, $points, $user_id, $family_id);
$stmt->execute();
$stmt->close();

header("Location: tasks.php");
exit;
?>
