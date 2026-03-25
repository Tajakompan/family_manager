<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../entry/login.php");
    exit;
}
$family_id = (int)$_SESSION["family_id"];
$shop_id   = (int)($_GET["shop_id"] ?? 0);

$sql = "SELECT s.id, s.shop_id, p.name, p.amount, p.unit, s.quantity, s.necessity, s.purchased
        FROM shopping_list s
        INNER JOIN product p ON s.product_id = p.id
        WHERE s.family_id = ? 
        AND s.shop_id = ?
        AND purchased = 0
        AND necessity = 'high'
        ORDER BY purchased ASC, purchased_on ASC, id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $family_id, $shop_id);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($k = $res->fetch_assoc()) {
    $out[] = [
        "id" => (int)$k["id"],
        "shop_id" => (int)$k["shop_id"],
        "name" => $k["name"],
        "amount" => (float)$k["amount"],
        "unit" => $k["unit"],
        "quantity" => (int)$k["quantity"],
        "necessity" => $k["necessity"],
        "purchased" => (int)$k["purchased"]
    ];
}
$stmt->close();

header("Content-Type: application/json; charset=utf-8");
echo json_encode($out);
?>
