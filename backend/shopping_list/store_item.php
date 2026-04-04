<?php
require_once __DIR__ . "/../config.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id = (int)$_SESSION["user_id"];
$list_id = (int)($_POST["id"] ?? 0);
$storage_id = (int)($_POST["storage_id"] ?? 0);
$expires_raw = trim($_POST["expires_on"] ?? "");
$expires_on = ($expires_raw === "") ? null : $expires_raw;

if ($list_id <= 0 || $storage_id <= 0) {
    echo json_encode(["ok" => false, "message" => "Neveljavni podatki."]);
    exit;
}

$sql = "SELECT product_id, quantity
        FROM shopping_list
        WHERE id = ? AND family_id = ? AND purchased = 1
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param("ii", $list_id, $family_id);
if (!$stmt->execute()) {
    $stmt->close();
    echo json_encode(["ok" => false, "message" => "Napaka pri SELECT."]);
    exit;
}

$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    echo json_encode(["ok" => false, "message" => "Artikel ni bil najden."]);
    exit;
}

$product_id = (int)$item["product_id"];
$qty = (int)$item["quantity"];

$conn->begin_transaction();

$purchased_on = date("Y-m-d");
$product_status = "new";

$sql = "INSERT INTO food_location
        (family_id, storage_location_id, product_id, purchased_on, expires_on, quantity, app_user_id, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            quantity = quantity + VALUES(quantity),
            status = VALUES(status),
            purchased_on = LEAST(purchased_on, VALUES(purchased_on)),
            app_user_id = VALUES(app_user_id)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiissiis", $family_id, $storage_id, $product_id, $purchased_on, $expires_on, $qty, $user_id, $product_status);
if (!$stmt->execute()) {
    $stmt->close();
    $conn->rollback();
    echo json_encode(["ok" => false, "message" => "Napaka pri shranjevanju v shrambo."]);
    exit;
}
$stmt->close();

$sql = "DELETE FROM shopping_list
        WHERE id = ? AND family_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $list_id, $family_id);
if (!$stmt->execute() || $stmt->affected_rows !== 1) {
    $stmt->close();
    $conn->rollback();
    echo json_encode(["ok" => false, "message" => "Napaka pri brisanju iz seznama."]);
    exit;
}
$stmt->close();

$conn->commit();

echo json_encode([
    "ok" => true,
    "message" => "Item successfully stored"
]);
exit;
?>
