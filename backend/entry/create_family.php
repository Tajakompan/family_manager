<?php
require_once __DIR__ . "/../config.php";
$error = "";
$success = "";
$invalid_fields = [];
$name = "";
$code = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $code = trim($_POST["code"] ?? "");


    if ($name === "" || $code === "" ) {
        if ($name === "") {
            $invalid_fields["name"] = true;
        }
        if ($code === "") {
            $invalid_fields["code"] = true;
        }
        $error = "Vsa polja so obvezna.";
    }
    else {
        $sql = "SELECT id FROM family WHERE code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $invalid_fields["code"] = true;
            $error = "Ta varnostna koda je že zasedena.";
            $stmt->close();
        } else {
             $stmt->close();
            $sql = "INSERT INTO family (name, code)
                    VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $name, $code);

            if ($stmt->execute()) {
                $success = "Družina uspešno ustvarjena! Uporabniki se zdaj lahko vpišejo vanjo.";
            } else {
                $error = "Napaka pri ustvarjanju družine, poskusite znova.";
            }
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Nova družina</title>
    <link rel="stylesheet" href="../../frontend/entry/entry.css">
    <link rel="stylesheet" href="../../frontend/entry/login.css">
    <link rel="stylesheet" href="../../frontend/common_code/common_css.css">
    <link rel="stylesheet" href="../../frontend/common_code/open_space_settings.css">
    <link rel="stylesheet" href="../../frontend/common_code/form_window.css">
</head>
<body>
    <div class="left image_placeholder">
         <img src="../img/login_page.png.png" alt="Family Manager ilustracija">
    </div>
    <div class="right">
        <div class="login_panel">
            <div class="panel_top">
                <h2 class="title">Nova družina</h2>
            </div>
            <div class="login_frame">

            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="nice_gray"><?php echo $success; ?></div>
                <a href="registration.php">Pojdi na registracijo</a>
            <?php else: ?>

            <form method="post">
                <div class="field">
                    <label>Ime družine:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES) ?>" class="<?= isset($invalid_fields["name"]) ? "red" : "" ?>">
                </div>
                <div class="field">
                    <label>Koda družine (geslo za vstop v družino)</label>
                    <input type="text" name="code" value="<?= htmlspecialchars($code, ENT_QUOTES) ?>" class="<?= isset($invalid_fields["code"]) ? "red" : "" ?>">
                </div>
                <button type="submit" id="submit">Ustvari</button>
            </form>

            <div class="nice_gray">
                Je vaša družina že ustvarjena? <a href="registration.php">Registrirajte se</a>, ali pa se <a href="login.php">prijavite!</a>
            </div>

            <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>

