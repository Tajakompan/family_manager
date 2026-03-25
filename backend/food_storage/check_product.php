<?php
require_once __DIR__ . "/../config.php"; // prilagodi pot

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"])) {
  http_response_code(401);
  echo json_encode(["ok" => false, "error" => "not_logged_in"]);
  exit;
}

$family_id = (int)$_SESSION["family_id"];
$name = trim($_GET["name"] ?? "");

if ($name === "") {
  echo json_encode(["ok" => true, "exists" => false]);
  exit;
}

$sql = "SELECT id, amount, unit, product_category_id
        FROM product
        WHERE family_id = ? AND name = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $family_id, $name);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
  echo json_encode([
    "ok" => true,
    "exists" => true,
    "product" => [
      "id" => (int)$row["id"],
      "amount" => $row["amount"],
      "unit" => $row["unit"],
      "product_category_id" => (int)$row["product_category_id"],
    ]
  ]);
} else {
  echo json_encode(["ok" => true, "exists" => false]);
}
exit;
