<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  header("Location: ../entry/login.php");
  exit;
}

$family_id = (int)$_SESSION["family_id"];

$meal_id = (int)($_POST["meal_id"] ?? 0);
$name = trim($_POST["new_meal"] ?? "");

if ($meal_id > 0 && $name !== "") {
  $sql = "UPDATE meal
          SET name = ?
          WHERE id = ? AND family_id = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sii", $name, $meal_id, $family_id);
  $stmt->execute();
  $stmt->close();
}

header("Location: meals.php");
exit;
?>