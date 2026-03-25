<?php
require_once __DIR__ . "/../config.php";
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$id = (int)($_POST["id"] ?? 0);
$quantity = (int)($_POST["quantity"] ?? 0);

if ($id > 0 || $quantity >= 1) {
    $sql = "UPDATE shopping_list
            SET quantity=?, last_time_modified=NOW()
            WHERE id=? AND family_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantity, $id, $family_id);
    $stmt->execute();
}

header("Location: shopping_list.php");
    exit;

?>
