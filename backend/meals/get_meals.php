<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  http_response_code(401);
  exit;
}

$family_id = (int)$_SESSION["family_id"];

$sql = "SELECT id, name, meal_category, date_of_meal as date
        FROM meal 
        WHERE family_id = ? 
        AND datediff(date_of_meal, current_date()) BETWEEN 0 AND 9";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($row = $res->fetch_assoc()) {
  $out[] = $row;
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($out);
?>
