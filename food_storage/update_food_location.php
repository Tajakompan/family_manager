<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: ../entry/login.php");
  exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id   = (int)$_SESSION["user_id"];

$id = (int)($_POST["food_location_id"] ?? 0);
$storage_location = (int)($_POST["storage_id"] ?? 0);

$product_expires_on = $_POST["product_expires_on"] ?? "";
$product_quantity   = (int)($_POST["product_quantity"] ?? 0);
$product_status     = trim($_POST["product_status"] ?? "new");

if($id > 0 && $storage_location > 0 && !empty($product_expires_on) && $product_quantity > 0 && $product_status != ""){
  $sql = "UPDATE food_location
          SET expires_on = ?, quantity = ?, status = ?, app_user_id = ?
          WHERE id = ? AND family_id = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sisiii", $product_expires_on, $product_quantity, $product_status, $user_id, $id, $family_id);
  $stmt->execute();
  $stmt->close();
}

header("Location: food_storage.php?storage_id=".$storage_location);
exit;
?>
