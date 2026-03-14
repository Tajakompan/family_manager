<?php
require_once __DIR__ . "/../config.php";

function wants_json_response(): bool
{
    $requested_with = strtolower($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "");
    $accept = strtolower($_SERVER["HTTP_ACCEPT"] ?? "");

    return $requested_with === "xmlhttprequest" || str_contains($accept, "application/json");
}

function respond_update_family(bool $ok, ?string $error = null, array $payload = [], int $status = 200): void
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
    respond_update_family(false, "invalid_request", [], 405);
}

$session_family_id = (int)$_SESSION["family_id"];
$family_id = (int)($_POST["family_id"] ?? 0);
$name = trim($_POST["name"] ?? "");
$code = trim($_POST["code"] ?? "");

if ($family_id <= 0 || $family_id !== $session_family_id) {
    respond_update_family(false, "missing_family", [], 422);
}

if ($name === "" || $code === "") {
    respond_update_family(false, "required_fields", [], 422);
}

$exists_sql = "SELECT id FROM family WHERE id = ?";
$exists_stmt = $conn->prepare($exists_sql);
$exists_stmt->bind_param("i", $family_id);
$exists_stmt->execute();
$exists_stmt->store_result();
$family_exists = $exists_stmt->num_rows > 0;
$exists_stmt->close();

if (!$family_exists) {
    respond_update_family(false, "missing_family", [], 404);
}

$code_sql = "SELECT id FROM family WHERE code = ? AND id <> ?";
$code_stmt = $conn->prepare($code_sql);
$code_stmt->bind_param("si", $code, $family_id);
$code_stmt->execute();
$code_stmt->store_result();
$code_taken = $code_stmt->num_rows > 0;
$code_stmt->close();

if ($code_taken) {
    respond_update_family(false, "code_taken", [], 409);
}

$sql = "UPDATE family SET name = ?, code = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $name, $code, $family_id);
$execute_ok = $stmt && $stmt->execute();
if ($stmt) {
    $stmt->close();
}

if (!$execute_ok) {
    respond_update_family(false, "save_failed", [], 500);
}

respond_update_family(true, null, [
    "family" => [
        "id" => $family_id,
        "name" => $name,
        "code" => $code,
    ],
]);
?>
