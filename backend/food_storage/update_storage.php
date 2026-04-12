<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$storage_id = (int)($_POST["storage_id"] ?? 0);
$new_storage = trim($_POST["new_storage_location"] ?? "");

if ($storage_id <= 0 || $new_storage === "") {
    header("Location: food_storage.php");
    exit;
}

$sql = "SELECT id
        FROM storage_location
        WHERE id = ? AND family_id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $storage_id, $family_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    header("Location: food_storage.php");
    exit;
}

$sql = "SELECT id
        FROM storage_location
        WHERE family_id = ?
          AND LOWER(name) = LOWER(?)
          AND id <> ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $family_id, $new_storage, $storage_id);
$stmt->execute();
$duplicate = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$duplicate) {
    $sql = "UPDATE storage_location
            SET name = ?
            WHERE id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $new_storage, $storage_id, $family_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: food_storage.php?storage_id=" . $storage_id);
exit;
?>
