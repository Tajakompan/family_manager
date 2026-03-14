<?php
require_once __DIR__ . "/../config.php";

function redirect_dashboard(?string $error = null): void
{
    $location = "Location: dashboard.php";
    if ($error !== null) {
        $location .= "?update_user_error=" . urlencode($error);
    }
    header($location);
    exit;
}

function wants_json_response(): bool
{
    $requested_with = strtolower($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "");
    $accept = strtolower($_SERVER["HTTP_ACCEPT"] ?? "");

    return $requested_with === "xmlhttprequest" || str_contains($accept, "application/json");
}

function respond_update_user(bool $ok, ?string $error = null, array $payload = [], int $status = 200): void
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

    if ($ok) {
        header("Location: dashboard.php");
        exit;
    }

    redirect_dashboard($error);
}

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond_update_user(false, "invalid_request", [], 405);
}

$family_id = (int)$_SESSION["family_id"];
$user_id = (int)$_SESSION["user_id"];

$name = trim($_POST["name"] ?? "");
$surname = trim($_POST["surname"] ?? "");
$email = trim($_POST["email"] ?? "");
$birthdate = trim($_POST["birthdate"] ?? "");
$password_1 = $_POST["password_1"] ?? "";
$password_2 = $_POST["password_2"] ?? "";

$today = date("Y-m-d");

if ($name === "" || $surname === "" || $email === "" || $birthdate === "") {
    respond_update_user(false, "required_fields", [], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond_update_user(false, "invalid_email", [], 422);
}

if ($birthdate > $today) {
    respond_update_user(false, "future_birthdate", [], 422);
}

$check_sql = "SELECT id FROM app_user WHERE email = ? AND id <> ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("si", $email, $user_id);
$check_stmt->execute();
$check_stmt->store_result();
$email_taken = $check_stmt->num_rows > 0;
$check_stmt->close();

if ($email_taken) {
    respond_update_user(false, "email_taken", [], 409);
}

$new_password_hash = null;
$password_filled = ($password_1 !== "" || $password_2 !== "");

if ($password_filled) {
    if ($password_1 !== $password_2) {
        respond_update_user(false, "password_mismatch", [], 422);
    }
    if (mb_strlen($password_1) < 8) {
        respond_update_user(false, "password_too_short", [], 422);
    }
    $new_password_hash = password_hash($password_1, PASSWORD_DEFAULT);
}

if ($new_password_hash === null) {
    $sql = "UPDATE app_user
            SET name = ?, surname = ?, email = ?, birthdate = ?
            WHERE id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $name, $surname, $email, $birthdate, $user_id, $family_id);
} else {
    $sql = "UPDATE app_user
            SET name = ?, surname = ?, email = ?, birthdate = ?, password = ?
            WHERE id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssii", $name, $surname, $email, $birthdate, $new_password_hash, $user_id, $family_id);
}

$execute_ok = $stmt && $stmt->execute();
if ($stmt) {
    $stmt->close();
}

if (!$execute_ok) {
    respond_update_user(false, "save_failed", [], 500);
}

$_SESSION["user_name"] = $name;
$_SESSION["user_surname"] = $surname;

respond_update_user(true, null, [
    "user" => [
        "name" => $name,
        "surname" => $surname,
        "email" => $email,
    ],
]);
?>