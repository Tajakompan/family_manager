<?php
require_once __DIR__ . "/../config.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  http_response_code(401);
  echo json_encode(["error" => "Unauthorized"]);
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

$data = [];
while ($row = $res->fetch_assoc()) {
  $data[] = [
    "name" => $row["name"],
    "points" => (int)$row["user_points"],
  ];
}
$stmt->close();

echo json_encode($data);
?>