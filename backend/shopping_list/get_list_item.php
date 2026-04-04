<?php
require_once __DIR__ . "/../config.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["family_id"])) { 
        header("Location: ../entry/login.php");
        exit; 
}

$family_id = (int)$_SESSION["family_id"];
$id = (int)($_GET["id"] ?? 0);

$sql = "SELECT s.id, shop_id, p.name, amount, unit, quantity, necessity
        FROM shopping_list s
        INNER JOIN product p on p.id = s.product_id
        WHERE s.id=? AND s.family_id=?;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $family_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

echo json_encode($row ?: null);
?>
