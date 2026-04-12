<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$session_user_id = (int)$_SESSION["user_id"];
$user_id = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : $session_user_id;

if ($user_id !== $session_user_id && (($_SESSION["user_role"] ?? "") !== "Starš - admin")) {
    header("Location: ../dashboard/dashboard.php");
    exit;
}

$conn->begin_transaction();


$sql = "DELETE FROM app_user WHERE id = ? AND family_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $family_id);
$stmt->execute();
$stmt->close();
$conn->commit();

if ($user_id === $session_user_id) {
    header("Location: ../entry/logout.php");
    exit;
}

header("Location: ../admin_page/admin_page.php");
exit;
?>
