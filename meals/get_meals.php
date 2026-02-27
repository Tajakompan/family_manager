<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  http_response_code(401);
  exit;
}

$family_id = (int)$_SESSION["family_id"];

$sql = "SELECT id, name, meal_category, meal_eating_date as date
        FROM meal a
        INNER JOIN meal_eating b on a.id = b.meal_id
        WHERE a.family_id = ? AND b.family_id = ?
        AND datediff(date, current_date()) BETWEEN 0 AND 13";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $family_id, $family_id);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($row = $res->fetch_assoc()) {
  $out[] = $row;
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($out);
?>
