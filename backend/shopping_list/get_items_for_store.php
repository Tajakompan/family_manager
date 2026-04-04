<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];

$sql = "SELECT s.id,
               s.product_id,
               s.quantity,
               p.name,
               p.amount,
               p.unit
        FROM shopping_list s
        INNER JOIN product p ON s.product_id = p.id
        WHERE s.family_id = ?
          AND s.purchased = 1
        ORDER BY s.purchased_on DESC, s.last_time_modified DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}

$stmt->close();

header("Content-Type: application/json; charset=utf-8");
echo json_encode($items);

?>