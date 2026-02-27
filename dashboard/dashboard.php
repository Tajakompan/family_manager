<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../entry/login.php");
    exit;
}

$family_id = (int)$_SESSION["family_id"];
$hour = date("H");
if($hour >= 18)
    $greeting = "Dober večer";
else if($hour >= 12)
    $greeting = "Dober dan";
else
    $greeting = "Dobro jutro";
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard (test)</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="../sidebar/sidebar.css">
    <link rel="stylesheet" href="../common_code/common_css.css">
    <link rel="stylesheet" href="../common_code/open_space_settings.css">
</head>
<body>
    <?php include "../sidebar/sidebar.php"; ?>
    <div class="vertical_flex">
        <span class="greeting"><span style="color: #96CA43"><?=$greeting?>,</span> <span style="color: #45A35B"><?=htmlspecialchars($_SESSION["user_name"])?>!</span></span>
        <main class="content">
            <div>
                <h2>Živjo,&nbsp; <?= htmlspecialchars($_SESSION["user_name"])?>!</h2>
            </div>
            <div>
                User: <b><?= htmlspecialchars($_SESSION["user_name"] . " " . $_SESSION["user_surname"]) ?></b><br>
                Role: <b><?= htmlspecialchars($_SESSION["user_role"]) ?></b><br>
                Family ID: <b><?= $family_id ?></b><br>
                <a href="../entry/logout.php">Odjava</a>
            </div>
            <hr>
            <div class="pin">
                <h3> nakupovalni seznam </h3>
                <?php
                $sql = "
                    select p.name as pname, quantity, necessity, u.name as uname
                    from shopping_list s inner join product p on s.product_id = p.id
                    inner join app_user u on u.id = s.app_user_id
                    where s.family_id = ?;";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $family_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if($result->num_rows === 0) {
                    echo "nic ni za kupit";
                }
                else{
                    echo "<table>";
                    while($row = $result->fetch_assoc()){
                        echo "<tr><td>".$row['pname']."</td><td>".$row['quantity']."</td><td>".$row['necessity']."</td></tr>";
                    }
                    echo "</table>";
                }
                ?>
            </div>
            <div class="pin">
                <h3> nakupovalni seznam </h3>
                <?php
                $sql = "
                    select p.name as pname, quantity, necessity, u.name as uname
                    from shopping_list s inner join product p on s.product_id = p.id
                    inner join app_user u on u.id = s.app_user_id
                    where s.family_id = ?;";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $family_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if($result->num_rows === 0) {
                    echo "nic ni za kupit";
                }
                else{
                    echo "<table>";
                    while($row = $result->fetch_assoc()){
                        echo "<tr><td>".$row['pname']."</td><td>".$row['quantity']."</td><td>".$row['necessity']."</td></tr>";
                    }
                    echo "</table>";
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html>
