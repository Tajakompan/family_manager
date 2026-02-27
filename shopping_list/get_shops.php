<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];

$sql = "SELECT id, name
        FROM shop
        WHERE family_id = ?
        ORDER BY name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$res = $stmt->get_result();

$shops = [];
while ($row = $res->fetch_assoc()) {
    $shops[] = $row;
}
$stmt->close();

header("Content-Type: application/json; charset=utf-8");
echo json_encode($shops);
?>