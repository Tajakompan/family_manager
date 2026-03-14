<?php
require_once __DIR__ . "/../config.php";

function wants_json_response(): bool
{
    $requested_with = strtolower($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "");
    $accept = strtolower($_SERVER["HTTP_ACCEPT"] ?? "");

    return $requested_with === "xmlhttprequest" || str_contains($accept, "application/json");
}

function respond_reset_points(bool $ok, ?string $error = null, array $payload = [], int $status = 200): void
{
    if (wants_json_response()) {
        http_response_code($status);
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array_merge([
            "ok" => $ok,
            "error" => $error,
        ], $payload), JSON_UNESCAPED_UNICODE);
        exit;
    }

    header("Location: admin_page.php");
    exit;
}

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond_reset_points(false, "invalid_request", [], 405);
}

$family_id = (int)$_SESSION["family_id"];
$user_id = (int)($_POST["user_id"] ?? 0);

if ($user_id <= 0) {
    respond_reset_points(false, "missing_user", [], 422);
}

$exists_sql = "SELECT id FROM app_user WHERE id = ? AND family_id = ?";
$exists_stmt = $conn->prepare($exists_sql);
$exists_stmt->bind_param("ii", $user_id, $family_id);
$exists_stmt->execute();
$exists_stmt->store_result();
$user_exists = $exists_stmt->num_rows > 0;
$exists_stmt->close();

if (!$user_exists) {
    respond_reset_points(false, "missing_user", [], 404);
}

$sql = "UPDATE app_user SET user_points = 0 WHERE id = ? AND family_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $family_id);
$execute_ok = $stmt && $stmt->execute();
if ($stmt) {
    $stmt->close();
}

if (!$execute_ok) {
    respond_reset_points(false, "save_failed", [], 500);
}

respond_reset_points(true, null, [
    "user" => [
        "id" => $user_id,
        "user_points" => 0,
    ],
]);
?>
