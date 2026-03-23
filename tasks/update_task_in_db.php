<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  header("Location: ../entry/login.php");
  exit;
}
$user_role = $_SESSION["user_role"] ?? "";
$can_change_task_points = in_array($user_role, ["Odrasel", "Starš - admin"], true);

$family_id = (int)$_SESSION["family_id"];

$task_id = (int)($_POST["task_id"] ?? 0);
$name = trim($_POST["new_task"] ?? "");
$details = trim($_POST["details"] ?? "");
$no_date = isset($_POST["no_date"]) ? 1 : 0;
$points = $can_change_task_points ? (int)($_POST["points"] ?? 2) : 2;

if (!$can_change_task_points && $task_id > 0) {
  $points_sql = "SELECT points FROM task WHERE id = ? AND family_id = ?";
  $points_stmt = $conn->prepare($points_sql);
  $points_stmt->bind_param("ii", $task_id, $family_id);
  $points_stmt->execute();
  $points_result = $points_stmt->get_result();
  $points_row = $points_result ? $points_result->fetch_assoc() : null;
  $points_stmt->close();

  $points = (int)($points_row["points"] ?? 0);
}

$to_do_by = trim($_POST["to_do_by"] ?? "");
if ($no_date === 1 || $to_do_by === "") $to_do_by = null;

if ($task_id > 0 && $name !== "") {
  $sql = "UPDATE task
          SET name = ?, details = ?, no_date = ?, points = ?, to_do_by = ?
          WHERE id = ? AND family_id = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssiisii", $name, $details, $no_date, $points, $to_do_by, $task_id, $family_id);
  $stmt->execute();
  $stmt->close();
}

header("Location: tasks.php");
exit;
?>