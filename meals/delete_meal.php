<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  header("Location: ../entry/login.php");
  exit;
}

$family_id = (int)$_SESSION["family_id"];

$meal_id = (int)($_POST["meal_id"] ?? 0);

if ($meal_id > 0) {
  $sql = "DELETE FROM meal
          WHERE id = ? AND family_id = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $meal_id, $family_id);
  $stmt->execute();
  $stmt->close();
}

header("Location: meals.php");
exit;
?>