<?php
require_once __DIR__ . "/../config.php";
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $surname = trim($_POST["surname"] ?? "");
    $birthdate = trim($_POST["birthdate"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";
    $role = $_POST["role"] ?? "";
    $code = trim($_POST["code"] ?? "");

    if ($name === "" || $surname === "" || $birthdate === "" || $email === "" || $password === "" || $role === "") {
        $error = "Vsa polja so obvezna.";
    } 
    else if ($password !== $password2) {
        $error = "Gesli se ne ujemata.";
    }
    else {
        // preveri, če email že obstaja
        $sql = "SELECT id FROM app_user WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Uporabnik s tem emailom že obstaja.";
            $stmt->close();
        } 
        else {
            $stmt->close();
            // preveri, če družina obstaja, in pridobi family_id
            $sql = "SELECT id FROM family WHERE code = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $result = $stmt->get_result();
            $family = $result->fetch_assoc();
            
            if (!$family) {
                $error = "Napačna koda, ta družina ne obstaja.";
            } 
            else{
                $family_id = (int)$family["id"];
                $stmt->close();
                //dobi id role
                $sql = "SELECT id FROM user_role WHERE user_role_name = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $role);
                $stmt->execute();
                $result = $stmt->get_result();
                $role_row = $result->fetch_assoc();
                $role_id = (int)$role_row["id"];
                $stmt->close();

                $sql = "INSERT INTO app_user (name, surname, birthdate, email, password, user_role_id, family_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssii", $name, $surname, $birthdate, $email, $password, $role_id, $family_id);

                if ($stmt->execute()) 
                    $success = "Registracija uspešna. Sedaj se lahko prijaviš.";
                else 
                    $error = "Napaka pri registraciji.";
            }
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
    <link rel="stylesheet" href="../common_code/common_css.css">
    <link rel="stylesheet" href="../common_code/open_space_settings.css">
    <link rel="stylesheet" href="entry.css">
    <link rel="stylesheet" href="registration.css">
</head>
<body>
    <div class="left">
        
    </div>
    <div class="right">
        <div class="login_frame">
            <h2 class="title">Registracija</h2>

            <?php if ($error): ?>
                <div style="color:red;"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="color:green;"><?php echo $success; ?></div>
                <a href="login.php">Pojdi na prijavo</a>
            <?php else: ?>

            <form method="post">
                <div class="two_columns">
                    <div class="field">
                        <label>Ime:</label>
                        <input type="text" name="name">
                    </div>
                    <div class="field">
                        <label>Priimek:</label>
                        <input type="text" name="surname">
                    </div>
                    <div class="field">
                        <label>Datum rojstva:</label>
                        <input type="date" name="birthdate" id="birthdate" onchange="update_roles()">
                    </div>
                    <div class="field">
                        <label>Email:</label>
                        <input type="email" name="email">
                    </div>
                    <div class="field">
                        <label>Geslo:</label>
                        <input type="password" name="password">
                    </div>
                    <div class="field">
                        <label>Ponovi geslo:</label>
                        <input type="password" name="password2">
                    </div>
                    <div class="field">
                        <label>Koda družine</label>
                        <input type="text" name="code">
                    </div>
                    <div class="field">
                        <label>Vloga:</label>
                        <select name="role">
                            <option id="role_child" value="child">Otrok</option>
                            <option id="role_adult" value="adult">Odrasel</option>
                            <option id="role_parent" value="parent">Starš</option>
                        </select>
                    </div>
                </div>
                <div class="one_column">
                    <button type="submit" id="submit">Registriraj se</button>
                </div>
                
            </form>
            
            <div class="nice_gray">
                Družina še ni ustvarjena? <a href="create_family.php">Ustvari jo!</a> <br>
                Že imaš račun? <a href="login.php">Prijavi se</a>
            </div>

            <?php endif; ?>

</body>
</html>

<script>
function calculate_age(birthdate){
    if (!birthdate) 
        return null;
    const birth = new Date(birthdate);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    if(today.getMonth() == birth.getMonth() && today.getDate() < birth.getDate())
        age --;
    else if(today.getMonth() < birth.getMonth())
        age --;
    return age;
}
//prilagaja dovoljene role glede na starost
function update_roles(){
    const birthdate = document.getElementById("birthdate").value;
    const age = calculate_age(birthdate);

    const child = document.getElementById("role_child");
    const adult = document.getElementById("role_adult");
    const parent = document.getElementById("role_parent");

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
        if (adult.checked || parent.checked) {
            adult.checked = false;
            parent.checked = false;
            child.checked = true;
        }
    } 
    else {
        child.hidden = true;
        adult.hidden = false;
        parent.hidden = false;
        if (child.checked) child.checked = false;
    }
}
window.addEventListener("load", update_roles);

</script>
