<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  header("Location: ../entry/login.php");
  exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id   = (int)$_SESSION["user_id"];
$row = null;

if ($user_id > 0) {
    $sql = "SELECT a.name, a.surname, a.email, a.birthdate, u.user_role_name,
                  a.profile_image, a.profile_image_type
            FROM app_user a
            INNER JOIN user_role u ON a.user_role_id = u.id
            WHERE a.family_id = ? AND a.id = ?";


  $stmt = $conn->prepare($sql);
  if ($stmt) {
    $stmt->bind_param("ii", $family_id, $user_id);
    if ($stmt->execute()) {
      $res = $stmt->get_result();
      $row = $res ? $res->fetch_assoc() : null;
    }
    $stmt->close();
  }
}

header("Content-Type: application/json; charset=utf-8");

if (!$row) {
  echo json_encode(["ok" => false, "error" => "User not found"]);
} else {
  $imageDataUrl = null;

if (!empty($row["profile_image"]) && !empty($row["profile_image_type"])) {
  $imageDataUrl = "data:" . $row["profile_image_type"] . ";base64," . base64_encode($row["profile_image"]);
} else {
  $assetSql = "SELECT asset_data, asset_type
               FROM app_asset
               WHERE asset_key = ?
               LIMIT 1";
  $assetStmt = $conn->prepare($assetSql);

  if ($assetStmt) {
    $assetKey = "default_user_avatar";
    $assetStmt->bind_param("s", $assetKey);

    if ($assetStmt->execute()) {
      $assetRes = $assetStmt->get_result();
      $assetRow = $assetRes ? $assetRes->fetch_assoc() : null;

      if ($assetRow && !empty($assetRow["asset_data"]) && !empty($assetRow["asset_type"])) {
        $imageDataUrl = "data:" . $assetRow["asset_type"] . ";base64," . base64_encode($assetRow["asset_data"]);
      }
    }

    $assetStmt->close();
  }
}


  echo json_encode([
    "ok" => true,
    "user" => [
      "name" => $row["name"],
      "surname" => $row["surname"],
      "email" => $row["email"],
      "birthdate" => $row["birthdate"],
      "user_role_name" => $row["user_role_name"],
      "profile_image" => $imageDataUrl
    ]
  ]);
}

exit;
?>
