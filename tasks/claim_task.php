<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  header("Location: ../entry/login.php");
  exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id   = (int)$_SESSION["user_id"];

$task_id   = (int)($_POST["task_id"] ?? 0);

if ($task_id <= 0) { http_response_code(400); exit; }

$sql = "INSERT INTO who_is_doing_it (task_id, app_user_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$stmt->close();

header("Location: tasks.php");
exit;
?>
