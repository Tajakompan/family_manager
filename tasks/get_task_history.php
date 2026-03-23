<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  header("Location: ../entry/login.php");
  exit;
}

$family_id = (int)$_SESSION["family_id"];

$sql = "SELECT completed_by_name, task_name, completed_at
        FROM task_history
        WHERE family_id = ?
          AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY completed_by_name ASC, completed_at DESC, task_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

$grouped = [];

while ($row = $res->fetch_assoc()) {
  $name = $row["completed_by_name"];

  if (!isset($grouped[$name])) {
    $grouped[$name] = [];
  }

  $grouped[$name][] = [
    "task_name" => $row["task_name"],
    "completed_at" => $row["completed_at"],
  ];
}

$data = [];
foreach ($grouped as $name => $tasks) {
  $data[] = [
    "name" => $name,
    "tasks" => $tasks,
  ];
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($data);
