<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["family_id"])) {
  http_response_code(401);
  exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id = (int)$_SESSION["user_id"];

$sql = "SELECT
          t.id, t.name, t.to_do_by, points, creator.name AS created_by,
          GROUP_CONCAT(DISTINCT doer.name ORDER BY doer.name SEPARATOR ', ') AS doers
        FROM task t
        JOIN app_user creator ON creator.id = t.created_by_app_user_id
        LEFT JOIN who_is_doing_it w_all ON w_all.task_id = t.id
        LEFT JOIN app_user doer ON doer.id = w_all.app_user_id
        WHERE t.family_id = ?
          AND NOT EXISTS (
            SELECT 1
            FROM who_is_doing_it w_me
            WHERE w_me.task_id = t.id
              AND w_me.app_user_id = ?
          )
        GROUP BY t.id, t.name, t.to_do_by, creator.name
        ORDER BY (t.to_do_by IS NULL), t.to_do_by ASC, t.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $family_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($row = $res->fetch_assoc()) {
  $out[] = $row;
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($out);
?>
