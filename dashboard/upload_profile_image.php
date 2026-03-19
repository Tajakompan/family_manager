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

if (
    !isset($_FILES["profile_image"]) ||
    !is_uploaded_file($_FILES["profile_image"]["tmp_name"]) ||
    $_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK
) {
    header("Location: dashboard.php");
    exit;
}

$tmpPath = $_FILES["profile_image"]["tmp_name"];
$fileSize = (int)$_FILES["profile_image"]["size"];

if ($fileSize <= 0 || $fileSize > 512 * 1024 ) {
    header("Location: dashboard.php");
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($tmpPath);

$allowedTypes = [
    "image/jpeg",
    "image/png",
    "image/webp"
];

if (!in_array($mimeType, $allowedTypes, true)) {
    header("Location: dashboard.php");
    exit;
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
    http_response_code(500);
    exit("Napaka pri pripravi poizvedbe.");
}

$null = null;
$stmt->bind_param("bsii", $null, $mimeType, $user_id, $family_id);
$stmt->send_long_data(0, $imageData);


$ok = $stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if (!$ok || $affected < 1) {
    http_response_code(500);
    exit("Shranjevanje slike ni uspelo.");
}


header("Location: dashboard.php");
exit;

?>
