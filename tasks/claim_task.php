<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  http_response_code(401);
  exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id   = (int)$_SESSION["user_id"];
$task_id   = (int)($_POST["task_id"] ?? 0);

if ($task_id <= 0) { http_response_code(400); exit; }

/* varnost: task mora biti v isti family */
$chk = $conn->prepare("SELECT id FROM task WHERE id = ? AND family_id = ?");
$chk->bind_param("ii", $task_id, $family_id);
$chk->execute();
if ($chk->get_result()->num_rows === 0) { http_response_code(403); exit; }

/* insert (prepreči duplikate z unique key (task_id, app_user_id) ali z INSERT IGNORE) */
$stmt = $conn->prepare("INSERT IGNORE INTO who_is_doing_it (task_id, app_user_id) VALUES (?, ?)");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();

http_response_code(204);
?>
