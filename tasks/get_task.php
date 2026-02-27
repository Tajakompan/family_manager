<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  http_response_code(401);
  exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id   = (int)$_SESSION["user_id"];

$task_id = isset($_GET["task_id"]) ? (int)$_GET["task_id"] : 0;
if ($task_id <= 0) {
  http_response_code(400);
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode(["ok" => false, "error" => "Missing task_id"]);
  exit;
}

$sql = "SELECT
          t.id,
          t.name,
          t.details,
          t.no_date,
          t.to_do_by,
          t.points,
          creator.name AS created_by,
          GROUP_CONCAT(DISTINCT doer.name ORDER BY doer.name SEPARATOR ', ') AS doers
        FROM task t
        INNER JOIN app_user creator ON creator.id = t.created_by_app_user_id
        LEFT JOIN who_is_doing_it w_all ON w_all.task_id = t.id
        LEFT JOIN app_user doer ON doer.id = w_all.app_user_id
        WHERE t.family_id = ?
          AND t.id = ?
        GROUP BY t.id, t.name, t.details, t.no_date, t.to_do_by, t.points, creator.name
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $family_id, $task_id);
$stmt->execute();
$res = $stmt->get_result();

$row = $res->fetch_assoc();

header("Content-Type: application/json; charset=utf-8");

if (!$row) {
  http_response_code(404);
  echo json_encode(["ok" => false, "error" => "Task not found"]);
  exit;
}

echo json_encode(["ok" => true, "task" => $row]);
?>