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
$product_id = (int)($_POST["product_id_existing"] ?? 0);

$product_quantity = (int)($_POST["product_quantity"] ?? 0);
$product_status   = trim($_POST["product_status"] ?? "new");

$product_expires_on = trim($_POST["product_expires_on"] ?? "");
if ($product_expires_on === "") {
  $product_expires_on = null;
}

if ($id > 0 && $storage_location > 0 && $product_id > 0 && $product_quantity > 0 && $product_status !== "") {

  $sql = "SELECT id
          FROM food_location
          WHERE family_id = ?
            AND storage_location_id = ?
            AND product_id = ?
            AND id <> ?
            AND (
              (expires_on = ?)
              OR (expires_on IS NULL AND ? IS NULL)
            )
          LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "iiiiss",
    $family_id,
    $storage_location,
    $product_id,
    $id,
    $product_expires_on,
    $product_expires_on
  );
  $stmt->execute();
  $conflict = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($conflict) {
    header("Location: food_storage.php?storage_id=" . $storage_location . "&edit_error=duplicate");
    exit;
  }

  $sql = "UPDATE food_location
          SET expires_on = ?, quantity = ?, status = ?, app_user_id = ?
          WHERE id = ? AND family_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "sisiii",
    $product_expires_on,
    $product_quantity,
    $product_status,
    $user_id,
    $id,
    $family_id
  );
  $stmt->execute();
  $stmt->close();
}

header("Location: food_storage.php?storage_id=" . $storage_location);
exit;
?>
