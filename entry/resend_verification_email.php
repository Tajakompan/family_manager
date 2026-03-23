<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/send_verification_email.php";

$message = "";
$error = "";
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
        } else if ((int)$user["email_verified"] === 1) {
            $message = "Email je že potrjen.";
        } else {
            $raw_token = bin2hex(random_bytes(32));
            $token_hash = hash("sha256", $raw_token);

            if (sendVerificationEmail($email, $user["name"], $raw_token)) {
                $sql = "UPDATE app_user
                        SET email_verification_token_hash = ?, email_verification_sent_at = NOW()
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $token_hash, $user["id"]);

                if ($stmt->execute()) {
                    $message = "Nov potrditveni email je bil poslan.";
                } else {
                    $error = "Email je bil poslan, vendar shranjevanje novega tokena ni uspelo.";
                }

                $stmt->close();
            } else {
                $error = "Pošiljanje emaila ni uspelo. Stara povezava ostaja veljavna.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Ponovno pošiljanje potrditve</title>
    <link rel="stylesheet" href="entry.css">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="../common_code/common_css.css">
    <link rel="stylesheet" href="../common_code/open_space_settings.css">
</head>
<body>
<div class="right" style="width:100%;">
    <div class="login_panel">
        <div class="panel_top">
            <h2 class="title">Ponovno pošiljanje potrditve</h2>
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
                <button type="submit" id="submit">Pošlji znova</button>
            </form>

            <div class="nice_gray">
                <a href="login.php">Nazaj na prijavo</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
