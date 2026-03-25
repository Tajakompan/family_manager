<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  header("Location: ../entry/login.php");
  exit;
}

$family_id = (int)$_SESSION["family_id"];

$sql = "SELECT name, user_points
        FROM app_user
        WHERE family_id = ?
        ORDER BY user_points DESC, name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

$data = [];
while ($row = $res->fetch_assoc()) {
  $data[] = [
    "name" => $row["name"],
    "points" => (int)$row["user_points"],
  ];
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($data);
?>