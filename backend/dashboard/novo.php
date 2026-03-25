<?php
require_once "../config.php";

$filePath ="../img/default-user.jpg";
$assetKey = "default_user_avatar";
$assetType = "image/png";

$data = file_get_contents($filePath);
if ($data === false) {
    exit("Napaka pri branju datoteke.");
}

$sql = "INSERT INTO app_asset (asset_key, asset_data, asset_type)
        VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

$null = null;
$stmt->bind_param("sbs", $assetKey, $null, $assetType);
$stmt->send_long_data(1, $data);

if ($stmt->execute()) {
    echo "OK";
} else {
    echo "Napaka: " . $stmt->error;
}

$stmt->close();
