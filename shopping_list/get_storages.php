<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];

$sql = "SELECT id, name
        FROM storage_location
        WHERE family_id = ?
        ORDER BY name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$res = $stmt->get_result();

$storages = [];
while ($row = $res->fetch_assoc()) {
    $storages[] = $row;
}
$stmt->close();

header("Content-Type: application/json; charset=utf-8");
echo json_encode($storages);
?>