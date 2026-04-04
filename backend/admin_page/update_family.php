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

$family_id = (int)($_POST["family_id"] ?? 0);
$name = trim($_POST["name"] ?? "");
$code = trim($_POST["code"] ?? "");

if ($family_id !== (int)$_SESSION["family_id"] || $name === "" || $code === "") {
    echo json_encode(["ok" => false, "error" => "required_fields"]);
    exit;
}

$sql = "SELECT id FROM family WHERE code = ? AND id <> ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $code, $family_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($exists) {
    echo json_encode(["ok" => false, "error" => "code_taken"]);
    exit;
}

$sql = "UPDATE family SET name = ?, code = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $name, $code, $family_id);
$stmt->execute();
$stmt->close();

echo json_encode([
    "ok" => true,
    "family" => ["id" => $family_id, "name" => $name, "code" => $code]
]);
exit;
?>
