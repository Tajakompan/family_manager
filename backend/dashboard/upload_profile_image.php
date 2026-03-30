<?php
require_once __DIR__ . "/../config.php";

function redirect_dashboard_with_upload_error(?string $error = null): void
{
    $location = "Location: dashboard.php";

    if ($error !== null) {
        $location .= "?upload_image_error=" . urlencode($error);
    }

    header($location);
    exit;
}


if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Napačna metoda.");
}

$user_id = (int)$_SESSION["user_id"];
$family_id = (int)$_SESSION["family_id"];

if (!isset($_FILES["profile_image"])) {
    redirect_dashboard_with_upload_error("missing_file");
}

if ($_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK) {
    error_log("Profile image upload error code: " . $_FILES["profile_image"]["error"]);
    redirect_dashboard_with_upload_error("missing_file");
}

if (!is_uploaded_file($_FILES["profile_image"]["tmp_name"])) {
    error_log("Profile image was not uploaded through HTTP POST.");
    redirect_dashboard_with_upload_error("missing_file");
}


$tmpPath = $_FILES["profile_image"]["tmp_name"];
$fileSize = (int)$_FILES["profile_image"]["size"];

if ($fileSize <= 0 || $fileSize > 512 * 1024) {
    redirect_dashboard_with_upload_error("file_too_large");
}


$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($tmpPath);

$allowedTypes = [
    "image/jpeg",
    "image/jpg",
    "image/png",
    "image/webp",
    "image/gif",
    "image/bmp"
];

$imageData = file_get_contents($tmpPath);
if ($imageData === false) {
    error_log("Failed to read uploaded profile image.");
    redirect_dashboard_with_upload_error("read_failed");
}


$imageData = file_get_contents($tmpPath);
if ($imageData === false) {
    header("Location: dashboard.php");
    exit;
}

$sql = "UPDATE app_user
        SET profile_image = ?, profile_image_type = ?
        WHERE id = ? AND family_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Profile image prepare failed: " . $conn->error);
    redirect_dashboard_with_upload_error("save_failed");
}


$null = null;
$stmt->bind_param("bsii", $null, $mimeType, $user_id, $family_id);
$stmt->send_long_data(0, $imageData);


$ok = $stmt->execute();

if (!$ok) {
    error_log("Profile image save failed: " . $stmt->error);
    $stmt->close();
    redirect_dashboard_with_upload_error("save_failed");
}

$stmt->close();



header("Location: dashboard.php");
exit;

?>
