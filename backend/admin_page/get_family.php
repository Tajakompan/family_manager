<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

if (($_SESSION["user_role"] ?? "") !== "Starš - admin") {
    header("Location: ../dashboard/dashboard.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
session_write_close();


$sql = "SELECT id, name, code
        FROM family
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$res = $stmt->get_result();
$tab = $res->fetch_assoc();
$stmt->close();

header("Content-Type: application/json; charset=utf-8");

if (!$tab) {
  http_response_code(404);
  echo json_encode(["error" => "Family not found"]);
  exit;
}

$data = [
  "id" => (int)$tab["id"],
  "name" => $tab["name"],
  "code" => $tab["code"],
];

echo json_encode($data);
?>