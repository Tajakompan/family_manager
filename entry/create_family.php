<?php
require_once __DIR__ . "/../config.php";
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $code = trim($_POST["code"]);


    if ($name === "" || $code === "" ) 
        $error = "Vsa polja so obvezna.";
    else {
        // preveri, če koda že obstaja
        $sql = "SELECT id FROM family WHERE code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
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
                $error = "Napaka pri ustvarjanju družina, poskusi znova.";
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
    <link rel="stylesheet" href="entry.css">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="../common_code/common_css.css">
    <link rel="stylesheet" href="../common_code/open_space_settings.css">
</head>
<body>
    <div class="left">
    
    </div>
    <div class="right">
        <div class="login_frame">

            <h2 class="title">Nova družina</h2>

            <?php if ($error): ?>
                <div class="nice_gray"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="nice_gray"><?php echo $success; ?></div>
                <a href="registration.php">Pojdi na registracijo</a>
            <?php else: ?>

            <form method="post">
                <div class="field">
                    <label>Ime družine:</label>
                    <input type="text" name="name">
                </div>
                <div class="field">
                    <label>Koda družine</label>
                    <input type="text" name="code">
                </div>
                <button type="submit" id="submit">Ustvari</button>
            </form>

            <div class="nice_gray">
                Je tvoja družina že ustvarjena? <a href="registration.php">Registriraj se</a>, ali pa se <a href="login.php">prijavi!</a>
            </div>

            <?php endif; ?>

</body>
</html>
