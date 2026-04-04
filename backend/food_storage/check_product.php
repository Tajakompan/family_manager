<?php
require_once __DIR__ . "/../config.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$name = trim($_GET["name"] ?? "");

if ($name === "") {
    echo json_encode([
        "ok" => true,
        "exists" => false
    ]);
    exit;
}

$sql = "SELECT id, amount, unit, product_category_id
        FROM product
        WHERE family_id = ?
          AND LOWER(name) = LOWER(?)
        ORDER BY id DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $family_id, $name);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode([
        "ok" => true,
        "exists" => false
    ]);
    exit;
}

echo json_encode([
    "ok" => true,
    "exists" => true,
    "product" => [
        "id" => (int)$row["id"],
        "amount" => $row["amount"],
        "unit" => $row["unit"],
        "product_category_id" => $row["product_category_id"] !== null ? (int)$row["product_category_id"] : null,
    ]
]);
exit;
?>
