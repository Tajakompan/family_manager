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
  $sql = "SELECT a.name, a.surname, a.email, a.birthdate, u.user_role_name
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
  echo json_encode(["ok" => true, "user" => $row]);
}
exit;
?>
