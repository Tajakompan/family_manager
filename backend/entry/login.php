<?php
require_once __DIR__ . "/../config.php";
$error = "";
$success = "";
$email = "";
$password = "";
$invalid_fields = [];
if (($_GET["reset"] ?? "") === "success") {
    $success = "Geslo je bilo uspešno spremenjeno. Zdaj se lahko prijavite.";
}
if(isset($_SESSION["user_id"])){
    header("location: ../dashboard/dashboard.php");
    exit;
}
else{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = trim($_POST['email'] ?? "");
        $password = $_POST["password"] ?? "";

        if ($email === "" || $password === "") {
            if ($email === "") {
                $invalid_fields["email"] = true;
            }
            if ($password === "") {
                $invalid_fields["password"] = true;
            }
            $error = "Vnos email-a in gesla je obvezen!";
        } else {
            $sql = "SELECT u.id, u.name, u.surname, u.email, u.password, u.family_id, u.email_verified, u.email_verification_sent_at, r.user_role_name
                FROM app_user u
                JOIN user_role r ON r.id = u.user_role_id
                WHERE u.email = ?
                LIMIT 1
            ";
            
            $stmt = $conn->prepare($sql); 
            $stmt->bind_param("s", $email); 
            $stmt->execute(); 
            $result = $stmt->get_result(); 
            $user = $result->fetch_assoc(); 
            $stmt->close(); 

            if (!$user) {
                $invalid_fields["email"] = true;
                $error = "Uporabnik s tem emailom ne obstaja.";
            } else {
                $stored_password = $user["password"];
                $is_password_valid = password_verify($password, $stored_password);

                if (
                    !$is_password_valid &&
                    empty(password_get_info($stored_password)["algo"]) &&
                    hash_equals($stored_password, $password)
                ) {
                    $is_password_valid = true;
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE app_user SET password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    if ($update_stmt) {
                        $update_stmt->bind_param("si", $new_hash, $user["id"]);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                }

                if (!$is_password_valid) {
                    $invalid_fields["password"] = true;
                    $error = "Napačno geslo.";
                }
                else if ((int)$user["email_verified"] !== 1) {
                    $invalid_fields["email"] = true;
                    $error = "Email še ni potrjen. Preverite svoj inbox in kliknite potrditveno povezavo.";
                }
                 else {
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
    <title>Familoop - Prijava</title>
    <link rel="stylesheet" href="../../frontend/entry/entry.css">
    <link rel="stylesheet" href="../../frontend/entry/login.css">
    <link rel="stylesheet" href="../../frontend/common_code/common_css.css">
    <link rel="stylesheet" href="../../frontend/common_code/open_space_settings.css">
    <link rel="stylesheet" href="../../frontend/common_code/form_window.css">
</head>
<body>
<div class="left image_placeholder">
        <img src="../img/login_page.png" alt="Login ilustracija">
</div>
<div class="right">
    <div class="login_panel">
        <div class="panel_top">
            <h2 class="title">Prijava</h2>
            <p>Dobrodošli nazaj v Family Manager.</p>
        </div>
        <div class="login_frame">
            <?php if ($success): ?>
                <div class="nice_gray"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="field">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>" class="<?= isset($invalid_fields["email"]) ? "red" : "" ?>">
                </div>
                <div class="field">
                    <label>Geslo:</label>
                    <input type="password" name="password" value="<?= htmlspecialchars($password, ENT_QUOTES) ?>" class="<?= isset($invalid_fields["password"]) ? "red" : "" ?>">
                </div>
                <button type="submit" id="submit">Prijava</button>
                <div class="nice_gray">
                    <a href="forgot_password.php">Ste pozabili geslo?</a>
                </div>
            </form>
            <div class="nice_gray">
                Še nimaš računa? <a href="registration.php">Registriraj se!</a><br>
                Nisi prejel potrditvenega emaila? <a href="resend_verification_email.php">Pošlji znova</a>
            </div>

        </div>
    </div>
</div>
</body>
</html>
