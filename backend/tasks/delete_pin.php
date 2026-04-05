<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];

$task_id = trim($_POST["task_id"] ?? "");

if ($task_id === "") {
    header("Location: tasks.php");
    exit;
}

$sql = "DELETE FROM task WHERE id = ? AND family_id = ?;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $task_id, $family_id);
$stmt->execute();
$stmt->close();

header("Location: tasks.php");
exit;
?>