<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/send_password_reset_email.php";

$error = "";
$message = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");

    if ($email === "") {
        $error = "Vnesite email.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email ni v pravilnem formatu.";
    } else {
        $sql = "SELECT id, name, email_verified
            FROM app_user
            WHERE email = ?
            LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $error = "Uporabnik s tem emailom ne obstaja.";
        } else if ((int)$user["email_verified"] !== 1) {
            $error = "Email še ni potrjen, zato reset gesla ni mogoč.";
        } else {
            $raw_token = bin2hex(random_bytes(32));
            $token_hash = hash("sha256", $raw_token);

            $sql = "UPDATE app_user
                    SET password_reset_token_hash = ?, password_reset_sent_at = NOW()
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $token_hash, $user["id"]);

            if ($stmt->execute()) {
                if (!sendPasswordResetEmail($email, $user["name"], $raw_token)) {
                    $error = "Pošiljanje emaila ni uspelo. Poskusite znova.";
                }
            } else {
                $error = "Shranjevanje zahtevka za reset ni uspelo.";
            }

            $stmt->close();
        }

        if ($error === "") {
            $message = "Če račun s tem emailom obstaja in je potrjen, smo poslali povezavo za nastavitev novega gesla.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Pozabljeno geslo</title>
    <link rel="stylesheet" href="../../frontend/entry/entry.css">
    <link rel="stylesheet" href="../../frontend/entry/login.css">
    <link rel="stylesheet" href="../../frontend/common_code/common_css.css">
    <link rel="stylesheet" href="../../frontend/common_code/open_space_settings.css">
    <link rel="stylesheet" href="../../frontend/common_code/form_window.css">
</head>
<body>
<div class="left image_placeholder">
    <img src="../img/login_page.png.png" alt="Login ilustracija">
</div>
<div class="right">
    <div class="login_panel">
        <div class="panel_top">
            <h2 class="title">Pozabljeno geslo</h2>
            <p>Na email vam bomo poslali povezavo za nastavitev novega gesla.</p>
        </div>
        <div class="login_frame">
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="nice_gray"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="field">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>">
                </div>

                <button type="submit" id="submit">Pošlji povezavo</button>
            </form>

            <div class="nice_gray">
                <a href="logout.php">Nazaj na prijavo</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
