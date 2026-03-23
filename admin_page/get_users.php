<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
  header("Location: ../entry/login.php");
  exit;
}

if (($_SESSION["user_role"] ?? "") !== "Starš - admin") {
    http_response_code(403);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode([
        "ok" => false,
        "error" => "forbidden"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}


$family_id = (int)$_SESSION["family_id"];

$sql = "SELECT a.id, name, surname, email, user_role_id, birthdate, user_points, user_role_name
        FROM app_user a INNER JOIN user_role r ON a.user_role_id = r.id
        WHERE family_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($row = $res->fetch_assoc()) {
  $out[] = $row;
}

$stmt->close();

header("Content-Type: application/json; charset=utf-8");
echo json_encode($out);
?>

