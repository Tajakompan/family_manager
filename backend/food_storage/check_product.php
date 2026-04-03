<?php
require_once __DIR__ . "/../config.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "not_logged_in"]);
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$name = trim($_GET["name"] ?? "");

if ($name === "") {
    echo json_encode(["ok" => true, "exists" => false, "ambiguous" => false]);
    exit;
}

$sql = "SELECT id, amount, unit, product_category_id
        FROM product
        WHERE family_id = ?
          AND LOWER(name) = LOWER(?)
        ORDER BY amount ASC, unit ASC, id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $family_id, $name);
$stmt->execute();
$res = $stmt->get_result();

$variants = [];
while ($row = $res->fetch_assoc()) {
    $variants[] = [
        "id" => (int)$row["id"],
        "amount" => $row["amount"],
        "unit" => $row["unit"],
        "product_category_id" => $row["product_category_id"] !== null ? (int)$row["product_category_id"] : null,
    ];
}
$stmt->close();

$count = count($variants);

if ($count === 0) {
    echo json_encode([
        "ok" => true,
        "exists" => false,
        "ambiguous" => false
    ]);
    exit;
}

if ($count === 1) {
    echo json_encode([
        "ok" => true,
        "exists" => true,
        "ambiguous" => false,
        "product" => $variants[0]
    ]);
    exit;
}

echo json_encode([
    "ok" => true,
    "exists" => true,
    "ambiguous" => true,
    "product" => (object)[],
    "variants" => $variants
]);
exit;
?>
