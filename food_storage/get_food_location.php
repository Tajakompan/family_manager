<?php
require_once __DIR__ . "/../config.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"])) {
  http_response_code(401);
  echo json_encode(["ok"=>false, "error"=>"not_logged_in"]);
  exit;
}

$family_id = (int)$_SESSION["family_id"];
$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) { echo json_encode(["ok"=>false, "error"=>"bad_id"]); exit; }

// prilagodi imena tabel/kolon, če imaš drugače:
$sql = "
SELECT
  fl.id AS food_location_id,
  fl.storage_location_id AS storage_id,
  fl.expires_on AS product_expires_on,
  fl.quantity,
  fl.status AS product_status,
  p.id AS product_id,
  p.name AS product_name,
  p.amount AS product_amount,
  p.unit AS product_unit,
  p.product_category_id
FROM food_location fl
JOIN product p ON p.id = fl.product_id
WHERE fl.id = ? AND fl.family_id = ?
LIMIT 1";


$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $family_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$row){ echo json_encode(["ok"=>false, "error"=>"not_found"]); exit; }

echo json_encode(["ok"=>true, "row"=>$row]);
