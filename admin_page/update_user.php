<?php
require_once __DIR__ . "/../config.php";

function redirect_admin_page(?string $error = null, ?int $target_user_id = null): void
{
    $params = [];

    if ($error !== null) {
        $params["update_user_error"] = $error;
    }

    if ($target_user_id !== null) {
        $params["update_user_target"] = (string)$target_user_id;
    }

    $location = "Location: admin_page.php";
    if ($params) {
        $location .= "?" . http_build_query($params);
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

function respond_update_user(bool $ok, ?string $error = null, array $payload = [], int $status = 200, ?int $target_user_id = null): void
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
        header("Location: admin_page.php");
        exit;
    }

    redirect_admin_page($error, $target_user_id);
}

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}
if (($_SESSION["user_role"] ?? "") !== "Starš - admin") {
    respond_update_user(false, "forbidden", [], 403);
}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond_update_user(false, "invalid_request", [], 405);
}

$family_id = (int)$_SESSION["family_id"];
$session_user_id = (int)$_SESSION["user_id"];
$user_id = (int)($_POST["user_id"] ?? 0);
$role_id = (int)($_POST["role"] ?? 0);

$name = trim($_POST["name"] ?? "");
$surname = trim($_POST["surname"] ?? "");
$email = trim($_POST["email"] ?? "");
$birthdate = trim($_POST["birthdate"] ?? "");
$password_1 = $_POST["password_1"] ?? "";
$password_2 = $_POST["password_2"] ?? "";

$today = date("Y-m-d");

if ($user_id <= 0) {
    respond_update_user(false, "missing_user", [], 422, $user_id);
}

$exists_sql = "SELECT id FROM app_user WHERE id = ? AND family_id = ?";
$exists_stmt = $conn->prepare($exists_sql);
$exists_stmt->bind_param("ii", $user_id, $family_id);
$exists_stmt->execute();
$exists_stmt->store_result();
$user_exists = $exists_stmt->num_rows > 0;
$exists_stmt->close();

if (!$user_exists) {
    respond_update_user(false, "missing_user", [], 404, $user_id);
}

if ($name === "" || $surname === "" || $email === "" || $birthdate === "" || $role_id <= 0) {
    respond_update_user(false, "required_fields", [], 422, $user_id);
}

$role_exists_sql = "SELECT user_role_name FROM user_role WHERE id = ?";
$role_exists_stmt = $conn->prepare($role_exists_sql);
$role_exists_stmt->bind_param("i", $role_id);
$role_exists_stmt->execute();
$role_result = $role_exists_stmt->get_result();
$role_row = $role_result ? $role_result->fetch_assoc() : null;
$role_exists_stmt->close();

if (!$role_row) {
    respond_update_user(false, "required_fields", [], 422, $user_id);
}

if ($role_row["user_role_name"] === "Starš - admin") {
    $parent_count_sql = "SELECT COUNT(*) AS total
                         FROM app_user
                         WHERE family_id = ? AND user_role_id = ? AND id <> ?";
    $parent_count_stmt = $conn->prepare($parent_count_sql);
    $parent_count_stmt->bind_param("iii", $family_id, $role_id, $user_id);
    $parent_count_stmt->execute();
    $parent_count_result = $parent_count_stmt->get_result();
    $parent_count_row = $parent_count_result ? $parent_count_result->fetch_assoc() : null;
    $parent_count_stmt->close();

    if ((int)($parent_count_row["total"] ?? 0) >= 2) {
        respond_update_user(false, "too_many_parents", [], 422, $user_id);
    }
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond_update_user(false, "invalid_email", [], 422, $user_id);
}

if ($birthdate > $today) {
    respond_update_user(false, "future_birthdate", [], 422, $user_id);
}

$birthdate_dt = DateTime::createFromFormat("Y-m-d", $birthdate);
$today_dt = new DateTime("today");
$age = $birthdate_dt ? $birthdate_dt->diff($today_dt)->y : null;

if ($age !== null && $age < 18 && $role_row["user_role_name"] !== "Otrok") {
    respond_update_user(false, "minor_must_be_child", [], 422, $user_id);
}

$check_sql = "SELECT id FROM app_user WHERE email = ? AND id <> ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("si", $email, $user_id);
$check_stmt->execute();
$check_stmt->store_result();
$email_taken = $check_stmt->num_rows > 0;
$check_stmt->close();

if ($email_taken) {
    respond_update_user(false, "email_taken", [], 409, $user_id);
}

$new_password_hash = null;
$password_filled = ($password_1 !== "" || $password_2 !== "");

if ($password_filled) {
    if ($password_1 !== $password_2) {
        respond_update_user(false, "password_mismatch", [], 422, $user_id);
    }
    if (mb_strlen($password_1) < 8) {
        respond_update_user(false, "password_too_short", [], 422, $user_id);
    }
    $new_password_hash = password_hash($password_1, PASSWORD_DEFAULT);
}

if ($new_password_hash === null) {
    $sql = "UPDATE app_user
            SET name = ?, surname = ?, email = ?, birthdate = ?, user_role_id = ?
            WHERE id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiii", $name, $surname, $email, $birthdate, $role_id, $user_id, $family_id);
} else {
    $sql = "UPDATE app_user
            SET name = ?, surname = ?, email = ?, birthdate = ?, user_role_id = ?, password = ?
            WHERE id = ? AND family_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisii", $name, $surname, $email, $birthdate, $role_id, $new_password_hash, $user_id, $family_id);
}

$execute_ok = $stmt && $stmt->execute();
if ($stmt) {
    $stmt->close();
}

if (!$execute_ok) {
    respond_update_user(false, "save_failed", [], 500, $user_id);
}

if ($user_id === $session_user_id) {
    $_SESSION["user_name"] = $name;
    $_SESSION["user_surname"] = $surname;
}

respond_update_user(true, null, [
    "user" => [
        "id" => $user_id,
        "name" => $name,
        "surname" => $surname,
        "email" => $email,
        "birthdate" => $birthdate,
        "user_role_id" => $role_id,
        "user_role_name" => $role_row["user_role_name"],
    ],
], 200, $user_id);
?>
