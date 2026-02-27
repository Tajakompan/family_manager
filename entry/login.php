<?php
require_once __DIR__ . "/../config.php";
$error = "";
if(isset($_SESSION["user_id"])){
    header("location: ../dashboard/dashboard.php");
    exit;
}
else{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = trim($_POST['email'] ?? "");
        $password = $_POST["password"] ?? "";

        if ($email === "" || $password === "") {
            $error = "Vnos email-a in gesla je obvezen!";
        } else {
            $sql = "
                SELECT 
                    u.id,
                    u.name,
                    u.surname,
                    u.email,
                    u.password,
                    u.family_id,
                    r.user_role_name
                FROM app_user u
                JOIN user_role r ON r.id = u.user_role_id
                WHERE u.email = ?
                LIMIT 1
            ";
            //$stmt je prepared statement
            $stmt = $conn->prepare($sql); //mysql preveri, ce je poizvedba $sql pravilna
            $stmt->bind_param("s", $email); //s je string, $email bo namesto ?
            $stmt->execute(); //izvede poizvedbo
            $result = $stmt->get_result(); //rezultati poizvedbe
            $user = $result->fetch_assoc(); //spremeni rezultat v asociativno tabelo $user
            $stmt->close(); //zapre prepared statement

            if (!$user) {
                $error = "Uporabnik s tem emailom ne obstaja.";
            } else {
                // ZA ZDAJ: plain-text (ni se hash)
                if ($password !== $user["password"]) {
                    $error = "Napačno geslo.";
                } else {
                    // SESSION
                    $_SESSION["user_id"]   = $user["id"];
                    $_SESSION["family_id"] = $user["family_id"];
                    $_SESSION["user_role"] = $user["user_role_name"];
                    $_SESSION["user_name"] = $user["name"];
                    $_SESSION["user_surname"] = $user["surname"];

                    header("Location: ../dashboard/dashboard.php");
                    exit;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Prijava</title>
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
        <h2 class="title">Prijava</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label>Email:</label>
                <input type="email" name="email" >
            </div>
            <div class="field">
                <label>Geslo:</label>
                <input type="password" name="password" >
            </div>

            <button type="submit" id="submit">Prijava</button>
        </form>
        <div class="nice_gray">
            Še nimaš računa? <a href="registration.php">Registriraj se!</a>
        </div>
        
    </div>
</div>
</body>
</html>
