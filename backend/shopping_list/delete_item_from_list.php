<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$id = trim($_POST["id"] ?? "");

if ($id != "") {
    $sql = "SELECT id FROM shopping_list
            WHERE family_id = ?
            AND id = ?
            LIMIT 1;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $family_id, $id);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($exists) {
        $sql = "DELETE FROM shopping_list WHERE id = ? AND family_id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $family_id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: shopping_list.php");
exit;


?>
