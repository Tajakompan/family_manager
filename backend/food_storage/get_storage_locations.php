<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../entry/login.php");
    exit;
}
$family_id = (int)$_SESSION["family_id"];

$sql = "SELECT * FROM storage_location WHERE family_id = ?;";
$stmt = $conn->prepare($sql);
$stmt -> bind_param("i", $family_id);
$stmt -> execute();
$result = $stmt->get_result();
$out = $result->fetch_all(MYSQLI_ASSOC);
$stmt -> close();

header("Content-Type: application/json; charset=utf-8");
echo json_encode($out);
?>