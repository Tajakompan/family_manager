<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}
if (($_SESSION["user_role"] ?? "") !== "Starš - admin") {
    header("Location: ../dashboard/dashboard.php");
    exit;
}


$family_id = (int)$_SESSION["family_id"];

$sql = "DELETE FROM family WHERE id = ?;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$stmt->close();

header("Location: ../entry/logout.php");
exit;

?>