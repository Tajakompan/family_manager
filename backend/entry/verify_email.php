<?php
require_once __DIR__ . "/../config.php";

$message = "";
$is_success = false;

$token = trim($_GET["token"] ?? "");

if ($token === "") {
    $message = "Manjka verifikacijski token.";
} else {
    $token_hash = hash("sha256", $token);

    $sql = "SELECT id, email_verified
            FROM app_user
            WHERE email_verification_token_hash = ?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $message = "Povezava za potrditev ni veljavna ali pa je že uporabljena.";
    } else if ((int)$user["email_verified"] === 1) {
        $message = "Email je že potrjen.";
        $is_success = true;
    } else {
        $sql = "UPDATE app_user
                SET email_verified = 1,
                    email_verified_at = NOW(),
                    email_verification_token_hash = NULL
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user["id"]);

        if ($stmt->execute()) {
            $message = "Email je uspešno potrjen. Zdaj se lahko prijavite.";
            $is_success = true;
        } else {
            $message = "Potrditev emaila ni uspela.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Potrditev emaila</title>
    <link rel="stylesheet" href="../../frontend/entry/entry.css">
    <link rel="stylesheet" href="../../frontend/entry/login.css">
    <link rel="stylesheet" href="../../frontend/common_code/common_css.css">
    <link rel="stylesheet" href="../../frontend/common_code/open_space_settings.css">
</head>
<body>
<div class="right" style="width:100%;">
    <div class="login_panel">
        <div class="panel_top">
            <h2 class="title">Potrditev emaila</h2>
        </div>
        <div class="login_frame">
            <div class="<?= $is_success ? 'nice_gray' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
            <div class="nice_gray">
                <a href="login.php">Pojdi na prijavo</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
