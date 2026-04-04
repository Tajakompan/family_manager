<?php
require_once __DIR__ . "/../config.php";

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    exit;
}

$name = trim($_POST["name"] ?? "");
$surname = trim($_POST["surname"] ?? "");
$email = trim($_POST["email"] ?? "");
$birthdate = trim($_POST["birthdate"] ?? "");
$password_1 = $_POST["password_1"] ?? "";
$password_2 = $_POST["password_2"] ?? "";

$user_id = (int)$_SESSION["user_id"];
$family_id = (int)$_SESSION["family_id"];
$today = date("Y-m-d");

if ($name === "" || $surname === "" || $email === "" || $birthdate === "") {
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

$stmt->execute();
$stmt->close();

$_SESSION["user_name"] = $name;
$_SESSION["user_surname"] = $surname;

echo json_encode([
    "ok" => true,
    "user" => [
        "name" => $name,
        "surname" => $surname,
        "email" => $email,
        "birthdate" => $birthdate
    ]
]);
exit;
?>
