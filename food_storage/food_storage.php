<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../entry/login.php");
    exit;
}
$family_id = (int)$_SESSION["family_id"];

$sql = "SELECT * FROM storage_location WHERE family_id = ?;";
$stmt = $conn->prepare($sql);
$stmt -> bind_param("i", $family_id);
$stmt -> execute();
$result = $stmt->get_result();
$storage_location_table = $result->fetch_all(MYSQLI_ASSOC);
$stmt -> close();


$sql = "SELECT id, name FROM product_category WHERE family_id = ? ORDER BY name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$category_table = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


$chosen_id = isset($_GET['storage_id']) ? (int)$_GET['storage_id'] : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="food_storage.css">
    <link rel="stylesheet" href="../sidebar/sidebar.css">
    <link rel="stylesheet" href="../common_code/common_css.css">
    <link rel="stylesheet" href="../common_code/form_window.css">
    <link rel="stylesheet" href="../common_code/open_space_settings.css">
    <title>Zaloga hrane</title>
</head>
<body>
    <?php include "../sidebar/sidebar.php"; ?>
    <main>
        <div class="add_something_view" id="add_something_view">
            <div class="add_storage_location_window window">
                <div class="title">Dodaj novo lokacijo za beleženje zaloge:</div>
                <form method="post" action="add_storage_in_db.php">
                    <div class="form">
                            <label>Ime lokacije:</label> <input type="text" name="new_storage_location">
                    </div>
                    <div class="btns">
                        <button type="reset" id="cancel_new_storage_btn">Prekliči</button>
                        <button type="submit" id="add_new_storage_btn">Dodaj</button>
                    </div>
                </form>
            </div>
            
            <div class="add_category_window window">
                <div class="title">Dodaj novo kategorijo izdelkov:</div>
                <form method="post" action="add_category_in_db.php">
                    <div class="form">
                            <label>Ime kategorije:</label> <input type="text" name="new_category">
                    </div>
                    <div class="btns">
                        <button type="reset" id="cancel_category_btn">Prekliči</button>
                        <button type="submit" id="add_new_category_btn">Dodaj</button>
                    </div>
                </form>
            </div>

            <div class="add_product_window window">
                <div class="title">Dodaj nov izdelek v zalogo:</div>
                <form id="add_product_form" method="post" action="add_product_in_db.php">
                    <div class="form">
                            <input type="hidden" name="food_location_id" value="">
                            <input type="hidden" name="storage_id" id="storage_id_input">
                            <label>Ime izdelka:</label> <input type="text" name="product_name">
                            <label>Količina:</label> <input type="text" name="product_amount">
                            <label>Enota:</label> <input type="text" name="product_unit">
                            <label>Kvantiteta:</label> <input type="text" name="product_quantity">
                            <label>Kategorija:</label> 
                            <select name="product_category">
                                <?php 
                                    foreach ($category_table as $one)
                                        echo "<option value='".$one["id"]."'>".htmlspecialchars($one['name'])."</option>";
                                ?>
                            </select>
                            <label>Uporabno do:</label> <input type="date" name="product_expires_on">
                            <label>Status:</label>
                            <select name="product_status">
                                <option value="new">Novo</option>
                                <option value="open">Že odprto</option>
                                <option value="empty">Skoraj prazno</option>
                            </select>
                            <input type="hidden" name="product_id_existing" value="">

                    </div>
                    <div class="btns">
                        <button type="reset" id="cancel_new_product_btn">Prekliči</button>
                        <button type="submit" id="add_new_product_btn">Dodaj</button>
                    </div>
                </form>
            </div>
        </div>  
        
        
        <div class="center">
            <div class="title">ZALOGA</div>
            <div class="nav">
                <div class="add_storage_location" id="add_storage_location">NOVA LOKACIJA</div>
                <?php
                    $st = 0;
                foreach($storage_location_table as $k){
                    $is_chosen = ($chosen_id > 0 && (int)$k["id"] === $chosen_id);
                    // če ni storage_id v URL, izberi prvega
                    if ($chosen_id === 0 && $st === 0) $is_chosen = true;

                    $c = $is_chosen ? "chosen_storage" : "";
                    echo "<div class='nav_item $c' id='id_".$k["id"]."' data-storage-id='".$k['id']."'>".$k["name"]."</div>";
                    $st++;
                }
                ?>
            </div>
            <div class="content">
                <table class="food_table">
                    <colgroup>
                        <col style="width: 35%">
                        <col style="width: 8%">
                        <col style="width: 8%">
                        <col style="width: 8%">
                        <col style="width: 13%">
                        <col style="width: 15%">
                        <col style="width: 13%">
                    </colgroup>
                    <thead>
                        <tr>
                            <td class="sortable" data-col="0" data-type="text">Izdelek</td>
                            <td class="sortable" data-col="1" data-type="number">Količina</td>
                            <td class="sortable" data-col="2" data-type="text">Enota</td>
                            <td class="sortable" data-col="3" data-type="number">Kvantiteta</td>
                            <td class="sortable" data-col="4" data-type="text">Kategorija</td>
                            <td class="sortable" data-col="5" data-type="date">Uporabno do</td>
                            <td class="sortable" data-col="6" data-type="text">Status izdelka</td>
                        </tr>
                    </thead>        
                    <tbody id="storage_table_body">
                        <!-- load_storage.js napolni -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="right">
            <div class="add_product btn" id="add_product">POSPRAVI IZDELEK</div>
            <div class="add_category btn" id="add_category">DODAJ KATEGORIJO</div>
        </div>

        <div id="nav_menu" class="menu">
            <div class="menu_item delete">Izbriši lokacijo</div>
        </div>
        <div id="row_menu" class="menu">
            <div class="menu_item edit">Uredi</div>
            <div class="menu_item delete">Izbriši zapis</div>
        </div>

        
    </main>

    <script src="load_storage.js"></script>
    <script src="view_changer.js"></script>
    <script src="form_validation.js"></script>
    <script src="right_click.js"></script>
    <script src="../common_code/sortable.js"></script>
</body>
</html>