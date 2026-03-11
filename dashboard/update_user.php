<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit;
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
    header("Location: dashboard.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: dashboard.php");
    exit;
}

if ($birthdate > $today) {
    header("Location: dashboard.php");
    exit;
}

$check_sql = "SELECT id FROM app_user WHERE email = ? AND id <> ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("si", $email, $user_id);
$check_stmt->execute();
$check_stmt->store_result();
$email_taken = $check_stmt->num_rows > 0;
$check_stmt->close();

if ($email_taken) {
    header("Location: dashboard.php");
    exit;
}

$new_password_hash = null;
$password_filled = ($password_1 !== "" || $password_2 !== "");

if ($password_filled) {
    if ($password_1 !== $password_2) {
        header("Location: dashboard.php");
        exit;
    }
    if (mb_strlen($password_1) < 8) {
        header("Location: dashboard.php");
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

header("Location: dashboard.php");
exit;
?>
