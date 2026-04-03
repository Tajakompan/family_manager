<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id = (int)$_SESSION["user_id"];

$storage_location   = (int)($_POST["storage_id"] ?? 0);
$product_name       = trim($_POST["product_name"] ?? "");
$product_amount_raw = str_replace(",", ".", trim($_POST["product_amount"] ?? ""));
$product_amount     = (float)$product_amount_raw;
$product_unit       = trim($_POST["product_unit"] ?? "");
$product_quantity   = (int)($_POST["product_quantity"] ?? 0);
$product_category   = (int)($_POST["product_category"] ?? 0);
$product_expires_on = trim($_POST["product_expires_on"] ?? "");
if ($product_expires_on === "") {
    $product_expires_on = null;
}
$product_status = trim($_POST["product_status"] ?? "new");

if (
    $storage_location <= 0 ||
    $product_category <= 0 ||
    $product_name === "" ||
    $product_amount <= 0 ||
    $product_unit === "" ||
    $product_quantity <= 0 ||
    $product_status === ""
) {
    header("Location: food_storage.php?storage_id=" . $storage_location);
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
} else {
    $sql = "INSERT INTO product (name, amount, unit, product_category_id, family_id)
            VALUES (?, ?, ?, ?, ?);";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsii", $product_name, $product_amount, $product_unit, $product_category, $family_id);
    $stmt->execute();
    $product_id = (int)$conn->insert_id;
    $stmt->close();
}

$purchased_on = date("Y-m-d");

$sql = "INSERT INTO food_location (family_id, storage_location_id, product_id, purchased_on, expires_on, quantity, app_user_id, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        quantity = quantity + VALUES(quantity),
        status = VALUES(status),
        purchased_on = LEAST(purchased_on, VALUES(purchased_on)),
        app_user_id = VALUES(app_user_id)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iiissiis",
    $family_id,
    $storage_location,
    $product_id,
    $purchased_on,
    $product_expires_on,
    $product_quantity,
    $user_id,
    $product_status
);
$stmt->execute();
$stmt->close();

header("Location: food_storage.php?storage_id=" . $storage_location);
exit;
?>
