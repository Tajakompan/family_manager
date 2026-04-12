<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$category_id = trim($_POST["category_id"] ?? "");

if ($category_id === "") {
    header("Location: food_storage.php");
    exit;
}

$sql = "SELECT id FROM product_category
        WHERE family_id = ?
          AND id = ?
        LIMIT 1;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $family_id, $category_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($exists) {
    $sql = "UPDATE product
            SET product_category_id = NULL
            WHERE family_id = ?
              AND product_category_id = ?;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $family_id, $category_id);
    $stmt->execute();
    $stmt->close();

    $sql = "DELETE FROM product_category
            WHERE id = ?
              AND family_id = ?;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $category_id, $family_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: food_storage.php");
exit;
?>
