<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$new_meal = trim($_POST["new_meal"] ?? "");
$date_of_meal = trim($_POST["date_of_meal"] ?? "");
$meal_category = trim($_POST["meal_category"] ?? "");

if ($new_meal === "") {
    header("Location: meals.php");
    exit;
}

$sql = "SELECT id FROM meal
        WHERE family_id = ?
          AND meal_category = ?
          AND date_of_meal = ?
        LIMIT 1;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $family_id, $meal_category, $date_of_meal);
$stmt->execute();
$existing_category = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$existing_category) {
    $sql = "INSERT INTO meal (name, family_id, date_of_meal, meal_category) VALUES (?, ?, ?, ?);";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siss", $new_meal, $family_id, $date_of_meal, $meal_category);
    $stmt->execute();
    $stmt->close();
}

header("Location: meals.php");
exit;
?>
