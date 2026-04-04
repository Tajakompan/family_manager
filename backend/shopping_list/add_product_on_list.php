<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$shop = (int)($_POST["shop_id"] ?? 0);
$product_name = trim($_POST["product_name"] ?? "");
$product_amount_raw = str_replace(",", ".", trim($_POST["product_amount"] ?? ""));
$product_amount = (float)$product_amount_raw;
$product_unit = trim($_POST["product_unit"] ?? "");
$product_quantity = (int)($_POST["product_quantity"] ?? 0);
$product_necessity = strtolower(trim($_POST["product_necessity"] ?? ""));
if ($product_necessity !== "low" && $product_necessity !== "medium" && $product_necessity !== "high")
    $product_necessity = "medium";

if ($shop <= 0 || $product_name === "" || $product_amount_raw === "" || $product_unit === "" || $product_amount <= 0 || $product_quantity <= 0) {
    header("Location: shopping_list.php");
    exit;
}

$sql = "SELECT id
        FROM product
        WHERE family_id = ?
          AND LOWER(name) = LOWER(?)
          AND amount = ?
          AND LOWER(unit) = LOWER(?)
        LIMIT 1;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isds", $family_id, $product_name, $product_amount, $product_unit);
$stmt->execute();
$existing_product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing_product) {
    $product_id = (int)$existing_product["id"];
} 
else {
    $sql = "INSERT INTO product (name, amount, unit, family_id)
            VALUES (?, ?, ?, ?);";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsi", $product_name, $product_amount, $product_unit, $family_id);
    $stmt->execute();
    $product_id = (int)$conn->insert_id;
    $stmt->close();
}

$sql = "INSERT INTO shopping_list (shop_id, product_id, family_id, quantity, necessity)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        quantity = quantity + VALUES(quantity),
        necessity = VALUES(necessity);";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiis", $shop, $product_id, $family_id, $product_quantity, $product_necessity);
$stmt->execute();
$stmt->close();

header("Location: shopping_list.php");
exit;
?>
