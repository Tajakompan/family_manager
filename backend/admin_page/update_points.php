<?php
require_once __DIR__ . "/../config.php";

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    exit;
}

if (($_SESSION["user_role"] ?? "") !== "Starš - admin") {
    echo json_encode(["ok" => false, "error" => "forbidden"]);
    exit;
}

$user_id = (int)($_POST["user_id"] ?? 0);
$points_raw = trim((string)($_POST["points"] ?? ""));

if ($user_id <= 0) {
    echo json_encode(["ok" => false, "error" => "missing_user"]);
    exit;
}

if ($points_raw === "" || filter_var($points_raw, FILTER_VALIDATE_INT) === false || (int)$points_raw < 0) {
    echo json_encode(["ok" => false, "error" => "invalid_points"]);
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$points = (int)$points_raw;

$sql = "SELECT id
        FROM app_user
        WHERE id = ? AND family_id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $family_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    echo json_encode(["ok" => false, "error" => "missing_user"]);
    exit;
}

$sql = "UPDATE app_user
        SET user_points = ?
        WHERE id = ? AND family_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $points, $user_id, $family_id);
$stmt->execute();
$stmt->close();

echo json_encode([
    "ok" => true,
    "user" => ["id" => $user_id, "user_points" => $points]
]);
exit;
?>
