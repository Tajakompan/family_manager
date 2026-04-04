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
$role_id = (int)($_POST["role"] ?? 0);

$name = trim($_POST["name"] ?? "");
$surname = trim($_POST["surname"] ?? "");
$email = trim($_POST["email"] ?? "");
$birthdate = trim($_POST["birthdate"] ?? "");
$password_1 = $_POST["password_1"] ?? "";
$password_2 = $_POST["password_2"] ?? "";

$family_id = (int)$_SESSION["family_id"];
$session_user_id = (int)$_SESSION["user_id"];
$today = date("Y-m-d");

if ($user_id <= 0) {
    echo json_encode(["ok" => false, "error" => "missing_user"]);
    exit;
}

$sql = "SELECT id
        FROM app_user
        WHERE id = ? AND family_id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $family_id);
$stmt->execute();
$user_exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user_exists) {
    echo json_encode(["ok" => false, "error" => "missing_user"]);
    exit;
}

if ($name === "" || $surname === "" || $email === "" || $birthdate === "" || $role_id <= 0) {
    echo json_encode(["ok" => false, "error" => "required_fields"]);
    exit;
}

$sql = "SELECT user_role_name
        FROM user_role
        WHERE id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $role_id);
$stmt->execute();
$role_row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$role_row) {
    echo json_encode(["ok" => false, "error" => "required_fields"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["ok" => false, "error" => "invalid_email"]);
    exit;
}

if ($birthdate > $today) {
    echo json_encode(["ok" => false, "error" => "future_birthdate"]);
    exit;
}

$birthdate_dt = DateTime::createFromFormat("Y-m-d", $birthdate);
$today_dt = new DateTime("today");
$age = $birthdate_dt ? $birthdate_dt->diff($today_dt)->y : null;

if ($age !== null && $age < 18 && $role_row["user_role_name"] !== "Otrok") {
    echo json_encode(["ok" => false, "error" => "minor_must_be_child"]);
    exit;
}

if ($role_row["user_role_name"] === "Starš - admin") {
    $sql = "SELECT COUNT(*) AS total
            FROM app_user
            WHERE family_id = ? AND user_role_id = ? AND id <> ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $family_id, $role_id, $user_id);
    $stmt->execute();
    $parent_count_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ((int)($parent_count_row["total"] ?? 0) >= 2) {
        echo json_encode(["ok" => false, "error" => "too_many_parents"]);
        exit;
    }
}

$sql = "SELECT id
        FROM app_user
        WHERE email = ? AND id <> ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
$email_taken = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($email_taken) {
    echo json_encode(["ok" => false, "error" => "email_taken"]);
    exit;
}

$new_password_hash = null;
$password_filled = ($password_1 !== "" || $password_2 !== "");

if ($password_filled) {
    if ($password_1 !== $password_2) {
        echo json_encode(["ok" => false, "error" => "password_mismatch"]);
        exit;
    }

    if (mb_strlen($password_1) < 8) {
        echo json_encode(["ok" => false, "error" => "password_too_short"]);
        exit;
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

$stmt->execute();
$stmt->close();

if ($user_id === $session_user_id) {
    $_SESSION["user_name"] = $name;
    $_SESSION["user_surname"] = $surname;
}

echo json_encode([
    "ok" => true,
    "user" => [
        "id" => $user_id,
        "name" => $name,
        "surname" => $surname,
        "email" => $email,
        "birthdate" => $birthdate,
        "user_role_id" => $role_id,
        "user_role_name" => $role_row["user_role_name"]
    ]
]);
exit;
?>
