<?php
require_once __DIR__ . "/../config.php";
header("Content-Type: application/json");

if (!isset($_SESSION["family_id"])) exit;

$id = (int)$_POST["id"];
$purchased = (int)$_POST["purchased"];
$family_id = (int)$_SESSION["family_id"];

if ($purchased === 1) {
    $sql = "UPDATE shopping_list
            SET purchased=1,
                purchased_on=NOW(),
                last_time_modified=NOW()
            WHERE id=? AND family_id=?";
} else {
    $sql = "UPDATE shopping_list
            SET purchased=0,
                purchased_on=NULL,
                last_time_modified=NOW()
            WHERE id=? AND family_id=?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $family_id);
$stmt->execute();

echo json_encode(["ok"=>true]);
?>
