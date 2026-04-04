<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id   = (int)$_SESSION["family_id"];
$shop_id = trim($_POST["shop_id"] ?? "");

if ($shop_id === "") {
    header("Location: shopping_list.php");
    exit;
}

$sql = "SELECT id FROM shop
        WHERE family_id = ?
          AND id = ?
        LIMIT 1;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $family_id, $shop_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($exists) {
    $sql = "DELETE FROM shop WHERE id = ? AND family_id = ?;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $shop_id, $family_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: shopping_list.php");
exit;

?>
