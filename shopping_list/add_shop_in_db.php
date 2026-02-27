<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$new_shop = trim($_POST["new_shop"] ?? "");

if ($new_shop === "") {
    header("Location: shopping_list.php");
    exit;
}

// Preveri duplikat
$sql = "SELECT id FROM shop
        WHERE family_id = ?
          AND LOWER(name) = LOWER(?)
        LIMIT 1;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $family_id, $new_shop);
$stmt->execute();
$existing_category = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$existing_category) {
    $sql = "INSERT INTO shop (name, family_id) VALUES (?, ?);";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_shop, $family_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: shopping_list.php");
exit;
?>
