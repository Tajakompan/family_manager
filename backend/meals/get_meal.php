<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  http_response_code(401);
  exit;
}

$family_id = (int)$_SESSION["family_id"];
$meal_id = (int)$_GET["meal_id"];

$sql = "SELECT id, name, meal_category, date_of_meal as date
        FROM meal 
        WHERE family_id = ? 
        AND id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $family_id, $meal_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if ($meal_id <= 0) {
  echo json_encode(["ok" => false, "meal" => null]);
  exit;
}
header("Content-Type: application/json; charset=utf-8");
echo json_encode(["ok" => (bool)$row, "meal" => $row ?: null]);
?>
