<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$new_storage = trim($_POST["new_storage_location"] ?? "");

if ($new_storage === "") {
    header("Location: food_storage.php");
    exit;
}


$sql = "SELECT id FROM storage_location
        WHERE family_id = ?
          AND LOWER(name) = LOWER(?)
        LIMIT 1;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $family_id, $new_storage);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    $sql = "INSERT INTO storage_location (name, family_id) VALUES (?, ?);";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_storage, $family_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: food_storage.php");
exit;
?>
