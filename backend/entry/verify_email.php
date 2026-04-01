<?php
require_once __DIR__ . "/../config.php";

$message = "";
$message_class = "error";
$is_success = false;
$show_confirmation = false;

$token = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = trim($_POST["token"] ?? "");
} else {
    $token = trim($_GET["token"] ?? "");
}

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
        $message_class = "nice_gray";
        $is_success = true;
    } else if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $sql = "UPDATE app_user
                SET email_verified = 1,
                    email_verified_at = NOW(),
                    email_verification_token_hash = NULL
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user["id"]);

        if ($stmt->execute()) {
            $message = "Email je uspešno potrjen. Zdaj se lahko prijavite.";
            $message_class = "nice_gray";
            $is_success = true;
        } else {
            $message = "Potrditev emaila ni uspela.";
            $show_confirmation = true;
            $message_class = "error";
        }

        $stmt->close();
    } else {
        $message = "Povezava je veljavna. Za dokončanje registracije kliknite spodnji gumb.";
        $message_class = "nice_gray";
        $show_confirmation = true;
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
            <div class="<?= $message_class ?>">
                <?= htmlspecialchars($message) ?>
            </div>

            <?php if ($show_confirmation): ?>
                <form method="post">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
                    <button type="submit" id="submit">Potrdi email</button>
                </form>
            <?php endif; ?>

            <div class="nice_gray">
                <a href="login.php">Pojdi na prijavo</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
