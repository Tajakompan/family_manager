<?php
require_once __DIR__ . "/../config.php";

$error = "";
$token = trim($_GET["token"] ?? $_POST["token"] ?? "");
$password = "";
$password2 = "";
$show_form = false;
$user_id = null;

if ($token === "") {
    $error = "Manjka token za ponastavitev gesla.";
} else {
    $token_hash = hash("sha256", $token);

    $sql = "SELECT id, password_reset_sent_at
            FROM app_user
            WHERE password_reset_token_hash = ?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $error = "Povezava za nastavitev novega gesla ni veljavna.";
    } else {
        $sent_at = strtotime((string)$user["password_reset_sent_at"]);
        $is_expired = !$sent_at || $sent_at < (time() - 3600);

        if ($is_expired) {
            $error = "Povezava za nastavitev novega gesla je potekla. Zahtevajte novo.";
        } else {
            $user_id = (int)$user["id"];
            $show_form = true;
        }
    }
}

if ($show_form && $_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";

    if ($password === "" || $password2 === "") {
        $error = "Obe polji za geslo sta obvezni.";
    } else if ($password !== $password2) {
        $error = "Gesli se ne ujemata.";
    } else if (mb_strlen($password) < 8) {
        $error = "Geslo mora vsebovati vsaj 8 znakov.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE app_user
                SET password = ?,
                    password_reset_token_hash = NULL,
                    password_reset_sent_at = NULL
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $user_id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: login.php?reset=success");
            exit;
        }

        $stmt->close();
        $error = "Shranjevanje novega gesla ni uspelo.";
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Novo geslo</title>
    <link rel="stylesheet" href="entry.css">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="../common_code/common_css.css">
    <link rel="stylesheet" href="../common_code/open_space_settings.css">
</head>
<body>
<div class="left image_placeholder">
    <img src="../img/login_page.png.png" alt="Login ilustracija">
</div>
<div class="right">
    <div class="login_panel">
        <div class="panel_top">
            <h2 class="title">Nastavi novo geslo</h2>
            <p>Vnesite novo geslo za svoj račun.</p>
        </div>
        <div class="login_frame">
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($show_form): ?>
                <form method="post">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">

                    <div class="field">
                        <label>Novo geslo:</label>
                        <input type="password" name="password" minlength="8" value="<?= htmlspecialchars($password, ENT_QUOTES) ?>">
                    </div>

                    <div class="field">
                        <label>Ponovi novo geslo:</label>
                        <input type="password" name="password2" minlength="8" value="<?= htmlspecialchars($password2, ENT_QUOTES) ?>">
                    </div>

                    <button type="submit" id="submit">Shrani novo geslo</button>
                </form>
            <?php else: ?>
                <div class="nice_gray">
                    <a href="forgot_password.php">Zahtevaj novo povezavo</a>
                </div>
            <?php endif; ?>

            <div class="nice_gray">
                <a href="logout.php">Nazaj na prijavo</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
