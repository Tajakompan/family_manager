<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/send_verification_email.php";

$error = "";
$success = "";
$invalid_fields = [];
$name = "";
$surname = "";
$birthdate = "";
$email = "";
$password = "";
$password2 = "";
$role = "";
$code = "";
$today = date("Y-m-d");
$verification_mail_sent = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $surname = trim($_POST["surname"] ?? "");
    $birthdate = trim($_POST["birthdate"] ?? "");
    $birthdate_dt = DateTime::createFromFormat("Y-m-d", $birthdate);
    $today_dt = new DateTime("today");
    $age = $birthdate_dt ? $birthdate_dt->diff($today_dt)->y : null;
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";
    $role = $_POST["role"] ?? "";
    $code = trim($_POST["code"] ?? "");

    if ($name === "" || $surname === "" || $birthdate === "" || $email === "" || $password === "" || $password2 === "" || $code === "" || $role === "") {
        if ($name === "") {
            $invalid_fields["name"] = true;
        }
        if ($surname === "") {
            $invalid_fields["surname"] = true;
        }
        if ($birthdate === "") {
            $invalid_fields["birthdate"] = true;
        }
        if ($email === "") {
            $invalid_fields["email"] = true;
        }
        if ($password === "") {
            $invalid_fields["password"] = true;
        }
        if ($password2 === "") {
            $invalid_fields["password2"] = true;
        }
        if ($code === "") {
            $invalid_fields["code"] = true;
        }
        if ($role === "") {
            $invalid_fields["role"] = true;
        }
        $error = "Vsa polja so obvezna.";
    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $invalid_fields["email"] = true;
        $error = "Email ni v pravilnem formatu.";
    }
    else if ($birthdate > $today) {
        $invalid_fields["birthdate"] = true;
        $error = "Datum rojstva ne sme biti v prihodnosti.";
    }
    else if (!in_array($role, ["Starš - admin", "Odrasel", "Otrok"], true)) {
        $invalid_fields["role"] = true;
        $error = "Izbrana vloga ni veljavna.";
    }
    else if ($age !== null && $age < 18 && $role !== "Otrok") {
        $invalid_fields["role"] = true;
        $error = "Mladoletni uporabnik je lahko le otrok.";
    }
    else if ($password !== $password2) {
        $invalid_fields["password"] = true;
        $invalid_fields["password2"] = true;
        $error = "Gesli se ne ujemata.";
    }
    else if (mb_strlen($password) < 8) {
        $invalid_fields["password"] = true;
        $invalid_fields["password2"] = true;
        $error = "Geslo mora vsebovati vsaj 8 znakov.";
    }
    else {
        $sql = "SELECT id FROM app_user WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $invalid_fields["email"] = true;
            $error = "Uporabnik s tem emailom že obstaja.";
            $stmt->close();
        } 
        else {
            $stmt->close();
            $sql = "SELECT id FROM family WHERE code = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $result = $stmt->get_result();
            $family = $result->fetch_assoc();
            
            if (!$family) {
                $invalid_fields["code"] = true;
                $error = "Napačna koda, ta družina ne obstaja.";
            } 
                        else{
                $family_id = (int)$family["id"];
                $stmt->close();
                $stmt = null;

                // dobi id role
                $sql = "SELECT id FROM user_role WHERE user_role_name = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $role);
                $stmt->execute();
                $result = $stmt->get_result();
                $role_row = $result->fetch_assoc();
                $stmt->close();
                $stmt = null;

                if (!$role_row) {
                    $invalid_fields["role"] = true;
                    $error = "Izbrana vloga ni veljavna.";
                } else {
                    $role_id = (int)$role_row["id"];

                    if ($role === "Starš - admin") {
                        $parent_count_sql = "SELECT COUNT(*) AS total
                                             FROM app_user
                                             WHERE family_id = ? AND user_role_id = ?";
                        $parent_count_stmt = $conn->prepare($parent_count_sql);
                        $parent_count_stmt->bind_param("ii", $family_id, $role_id);
                        $parent_count_stmt->execute();
                        $parent_count_result = $parent_count_stmt->get_result();
                        $parent_count_row = $parent_count_result ? $parent_count_result->fetch_assoc() : null;
                        $parent_count_stmt->close();

                        if ((int)($parent_count_row["total"] ?? 0) >= 2) {
                            $invalid_fields["role"] = true;
                            $error = "Družina ima lahko največ dva starša - admina.";
                        }
                    }

                    if ($error === "") {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $raw_token = bin2hex(random_bytes(32));
                        $token_hash = hash("sha256", $raw_token);

                        $sql = "INSERT INTO app_user (
                                    name, surname, birthdate, email, password, user_role_id, family_id,
                                    email_verified, email_verification_token_hash, email_verification_sent_at
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param(
                            "sssssiis",
                            $name,
                            $surname,
                            $birthdate,
                            $email,
                            $password_hash,
                            $role_id,
                            $family_id,
                            $token_hash
                        );

                        if ($stmt->execute()) {
                            $verification_mail_sent = sendVerificationEmail($email, $name, $raw_token);

                            if ($verification_mail_sent) {
                                $success = "Registracija uspešna. Na vaš email smo poslali povezavo za potrditev.";
                            } else {
                                $success = "Registracija uspešna, vendar pošiljanje potrditvenega emaila ni uspelo. Kontaktirajte skrbnika ali poskusite znova kasneje.";
                            }
                        } else {
                            $error = "Napaka pri registraciji.";
                        }

                        $stmt->close();
                        $stmt = null;
                    }
                }
            }
            if($stmt)   
                $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Registracija</title>
    <link rel="stylesheet" href="../../frontend/common_code/common_css.css">
    <link rel="stylesheet" href="../../frontend/common_code/open_space_settings.css">
    <link rel="stylesheet" href="../../frontend/entry/entry.css">
    <link rel="stylesheet" href="../../frontend/entry/registration.css">
    <link rel="stylesheet" href="../../frontend/common_code/form_window.css">
</head>
<body>
    <div class="left image_placeholder">
            <img src="../img/login_page.png.png" alt="Family Manager ilustracija">
    </div>
    <div class="right">
        <div class="login_panel">
            <div class="panel_top">
                <h2 class="title">Registracija</h2>
            </div>
            <div class="login_frame">

            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="nice_gray" style="text-align:center;">
                    <div style="color:#a7d65e; margin-bottom:8px;"><?= htmlspecialchars($success) ?></div>
                    <div style="margin-bottom:8px;">Po potrditvi emaila se lahko prijavite.</div>
                    <a href="login.php">Pojdi na prijavo</a>
                </div>
            <?php else: ?>


            <form method="post">
                <div class="two_columns">
                    <div class="field">
                        <label>Ime:</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES) ?>" class="<?= isset($invalid_fields["name"]) ? "red" : "" ?>">
                    </div>
                    <div class="field">
                        <label>Priimek:</label>
                        <input type="text" name="surname" value="<?= htmlspecialchars($surname, ENT_QUOTES) ?>" class="<?= isset($invalid_fields["surname"]) ? "red" : "" ?>">
                    </div>
                    <div class="field">
                        <label>Datum rojstva:</label>
                        <input type="date" name="birthdate" id="birthdate" max="<?= $today ?>" onchange="update_roles()" value="<?= htmlspecialchars($birthdate, ENT_QUOTES) ?>" class="<?= isset($invalid_fields["birthdate"]) ? "red" : "" ?>">
                    </div>
                    <div class="field">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES) ?>" class="<?= isset($invalid_fields["email"]) ? "red" : "" ?>">
                    </div>
                    <div class="field">
                        <label>Geslo:</label>
                        <input type="password" name="password" minlength="8" value="<?= htmlspecialchars($password, ENT_QUOTES) ?>" class="<?= isset($invalid_fields["password"]) ? "red" : "" ?>">
                    </div>
                    <div class="field">
                        <label>Ponovi geslo:</label>
                        <input type="password" name="password2" minlength="8" value="<?= htmlspecialchars($password2, ENT_QUOTES) ?>" class="<?= isset($invalid_fields["password2"]) ? "red" : "" ?>">
                    </div>
                    <div class="field">
                        <label>Vstopna koda družine</label>
                        <input type="text" name="code" value="<?= htmlspecialchars($code, ENT_QUOTES) ?>" class="<?= isset($invalid_fields["code"]) ? "red" : "" ?>">
                    </div>
                    <div class="field">
                        <label>Vloga:</label>
                        <select name="role" class="<?= isset($invalid_fields["role"]) ? "red" : "" ?>">
                            <option id="role_parent" value="Starš - admin" <?= $role === "parent" ? "selected" : "" ?>>Starš - admin</option>
                            <option id="role_adult" value="Odrasel" <?= $role === "adult" ? "selected" : "" ?>>Odrasel</option>
                            <option id="role_child" value="Otrok" <?= $role === "child" ? "selected" : "" ?>>Otrok</option>
                        </select>
                    </div>
                </div>
                <div class="one_column">
                    <button type="submit" id="submit">Registriraj se</button>
                </div>
                
            </form>
            
            <div class="nice_gray">
                Družina še ni ustvarjena? <a href="create_family.php">Ustvarite jo!</a> <br>
                Že imate račun? <a href="login.php">Prijavite se</a>
            </div>

            <?php endif; ?>
            </div>
        </div>
    </div>

 
</body>
</html>

<script>
function calculate_age(birthdate){
    if (!birthdate) return null;

    const birth = new Date(birthdate);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();

    if (
        today.getMonth() < birth.getMonth() ||
        (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate())
    ) {
        age--;
    }

    return age;
}

function update_roles() {
    const birthdate = document.getElementById("birthdate").value;
    const age = calculate_age(birthdate);

    const roleSelect = document.querySelector('select[name="role"]');
    const child = document.getElementById("role_child");
    const adult = document.getElementById("role_adult");
    const parent = document.getElementById("role_parent");

    if (!roleSelect || !child || !adult || !parent) return;

    if (age === null) {
        child.hidden = false;
        adult.hidden = false;
        parent.hidden = false;
        return;
    }

    if (age < 18) {
        child.hidden = false;
        adult.hidden = true;
        parent.hidden = true;

        if (roleSelect.value !== "Otrok") {
            roleSelect.value = "Otrok";
        }
    } else {
        child.hidden = true;
        adult.hidden = false;
        parent.hidden = false;

        if (roleSelect.value === "Otrok") {
            roleSelect.value = "Odrasel";
        }
    }
}

window.addEventListener("load", update_roles);
</script>


