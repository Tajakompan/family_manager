<?php
include 'db_connect.php';

$message = ""; // tukaj bomo shranili opozorilo ali uspeh

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $code = $_POST['code'];

    // Preveri, če username že obstaja
    $check = $conn->query("SELECT id FROM family WHERE family_username = '$username'");
    if ($check->num_rows > 0) {
        $message = "<div>Uporabniško ime <strong>$username</strong> že obstaja. Izberi drugega.</div>";
    } else {
        $sql = "INSERT INTO family (name, family_username, code, created_on) VALUES ('$name', '$username', '$code', NOW())";
        if ($conn->query($sql) === TRUE) {
            $message = "<div>Družina uspešno dodana!</div>";
        } else {
            $message = "<div>Napaka: " . $conn->error . "</div>";
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="sl-SI">
<head>
  <meta charset="UTF-8">
  <title>Dodaj družino</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="../common_code/common_css.css">
  <link rel="stylesheet" href="../common_code/open_space_settings.css">
</head>
<body>

<div class="add_family">
  <h2>Dodaj družino</h2>

  <!-- Tukaj se izpiše opozorilo ali uspeh -->
  <?php if (!empty($message)) echo $message; ?>

  <form action="" method="post" class="mx-auto" style="max-width:400px;">
    <div>
      <label>Ime družine</label>
      <input type="text" name="name" required>
    </div>
    <div>
      <label>Uporabniško ime družine</label>
      <input type="text" name="username" required>
    </div>
    <div>
      <label>Koda</label>
      <input type="text" name="code" required>
    </div>
    <button type="submit">Shrani</button>
  </form>
</div>

</body>
</html>
