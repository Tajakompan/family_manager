<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id   = (int)$_SESSION["family_id"];
$storage_id = trim($_POST["storage_id"] ?? "");

if ($storage_id === "") {
    header("Location: food_storage.php");
    exit;
}

// Preveri, da obstaja 
$sql = "SELECT id FROM storage_location
        WHERE family_id = ?
          AND id = ?
        LIMIT 1;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $family_id, $storage_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($exists) {
    $sql = "DELETE FROM storage_location WHERE id = ? AND family_id = ?;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $storage_id, $family_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: food_storage.php");
exit;
?>
