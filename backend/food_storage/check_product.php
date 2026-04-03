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

$sql = "SELECT p.id, p.amount, p.unit, p.product_category_id,
               COALESCE(sl.cnt, 0) + COALESCE(fl.cnt, 0) AS usage_score
        FROM product p
        LEFT JOIN (
            SELECT product_id, COUNT(*) AS cnt
            FROM shopping_list
            WHERE family_id = ?
            GROUP BY product_id
        ) sl ON sl.product_id = p.id
        LEFT JOIN (
            SELECT product_id, COUNT(*) AS cnt
            FROM food_location
            WHERE family_id = ?
            GROUP BY product_id
        ) fl ON fl.product_id = p.id
        WHERE p.family_id = ?
          AND LOWER(p.name) = LOWER(?)
        ORDER BY usage_score DESC, p.id DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiis", $family_id, $family_id, $family_id, $name);
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
