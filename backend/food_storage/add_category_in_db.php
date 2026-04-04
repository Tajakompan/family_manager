<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$new_category = trim($_POST["new_category"] ?? "");

if ($new_category === "") {
    header("Location: food_storage.php");
    exit;
}


$sql = "SELECT id FROM product_category
        WHERE family_id = ?
          AND LOWER(name) = LOWER(?)
        LIMIT 1;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $family_id, $new_category);
$stmt->execute();
$existing_category = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$existing_category) {
    $sql = "INSERT INTO product_category (name, family_id) VALUES (?, ?);";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_category, $family_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: food_storage.php");
exit;
?>
