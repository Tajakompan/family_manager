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

//ali obstaja
$stmt = $conn->prepare("SELECT id FROM task WHERE id = ? AND family_id = ?");
$stmt->bind_param("ii", $task_id, $family_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) { http_response_code(403); exit; }
$stmt->close();

//najdi vse userje s tem taskom in pripadajoce tocke (zmeraj enake)
$sql = "SELECT a.app_user_id as user, b.points as points
        FROM who_is_doing_it a
        INNER JOIN task b ON a.task_id = b.id
        WHERE task_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$results = $stmt->get_result();
$stmt->close();
//vsem dodeli tocke
while ($row = $results->fetch_assoc()) {
  $sql = "UPDATE app_user SET user_points = user_points + ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $row["points"], $row["user"]);
  $stmt->execute();
  $stmt->close();
}

//brise iz povezave z userjem
$stmt = $conn->prepare("DELETE FROM who_is_doing_it WHERE task_id = ? AND app_user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$stmt->close();
//brise iz taskov
$stmt = $conn->prepare("DELETE FROM task WHERE id = ? AND family_id = ?");
$stmt->bind_param("ii", $task_id, $family_id);
$stmt->execute();
$stmt->close();
http_response_code(204);
?>