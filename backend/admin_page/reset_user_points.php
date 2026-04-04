<?php
require_once __DIR__ . "/../config.php";

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "unauthorized"]);
    exit;
}
if (($_SESSION["user_role"] ?? "") !== "Starš - admin") {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "forbidden"]);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "error" => "invalid_request"]);
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$user_id = (int)($_POST["user_id"] ?? 0);

if ($user_id <= 0) {
    http_response_code(422);
    echo json_encode(["ok" => false, "error" => "missing_user"]);
    exit;
}

$sql = "SELECT id FROM app_user WHERE id = ? AND family_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $family_id);
$stmt->execute();
$stmt->store_result();
$user_exists = $stmt->num_rows > 0;
$stmt->close();

if (!$user_exists) {
    http_response_code(404);
    echo json_encode(["ok" => false, "error" => "missing_user"]);
    exit;
}

$sql = "UPDATE app_user SET user_points = 0 WHERE id = ? AND family_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $family_id);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "save_failed"]);
    exit;
}

echo json_encode([
    "ok" => true,
    "user" => ["id" => $user_id, "user_points" => 0]
]);
exit;
