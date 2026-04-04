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

$user_id = (int)$_SESSION["user_id"];
$family_id = (int)$_SESSION["family_id"];

if (!isset($_FILES["profile_image"])) {
    header("Location: dashboard.php?upload_image_error=missing_file");
    exit;
}

if ($_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK) {
    header("Location: dashboard.php?upload_image_error=missing_file");
    exit;
}

$tmp_path = $_FILES["profile_image"]["tmp_name"];
$file_size = (int)$_FILES["profile_image"]["size"];

if (!is_uploaded_file($tmp_path)) {
    header("Location: dashboard.php?upload_image_error=missing_file");
    exit;
}

if ($file_size <= 0 || $file_size > 512 * 1024) {
    header("Location: dashboard.php?upload_image_error=file_too_large");
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($tmp_path);

$allowed_types = [
    "image/jpeg",
    "image/png",
    "image/webp"
];

if (!in_array($mime_type, $allowed_types, true)) {
    header("Location: dashboard.php?upload_image_error=invalid_type");
    exit;
}

$image_data = file_get_contents($tmp_path);
if ($image_data === false) {
    header("Location: dashboard.php?upload_image_error=read_failed");
    exit;
}

$sql = "UPDATE app_user
        SET profile_image = ?, profile_image_type = ?
        WHERE id = ? AND family_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: dashboard.php?upload_image_error=save_failed");
    exit;
}

$null = null;
$stmt->bind_param("bsii", $null, $mime_type, $user_id, $family_id);
$stmt->send_long_data(0, $image_data);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    header("Location: dashboard.php?upload_image_error=save_failed");
    exit;
}

header("Location: dashboard.php");
exit;
?>
