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
    <link rel="stylesheet" href="../../frontend/food_storage/food_storage.css">
    <link rel="stylesheet" href="../../frontend/sidebar/sidebar.css">
    <link rel="stylesheet" href="../../frontend/common_code/common_css.css">
    <link rel="stylesheet" href="../../frontend/common_code/open_space_settings.css">
    <link rel="stylesheet" href="../../frontend/common_code/form_window.css">
    <title>Familoop - Zaloga hrane</title>
</head>
<body>
    <?php include "../sidebar/sidebar.php"; ?>
    <main>
        <div class="add_something_view" id="add_something_view">
            <div class="add_storage_location_window window">
                <div class="title">Dodaj novo lokacijo za beleženje zaloge:</div>
                <form id="add_storage_form" method="post" action="add_storage_in_db.php">
                    <input type="hidden" name="storage_id" value="">
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
                <form id="add_category_form" method="post" action="add_category_in_db.php">
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
                            <label>Količina:</label> <input type="number" name="product_amount" placeholder="označeno na izdelku" step="0.01" min="0.01">
                            <label>Enota:</label> <input type="text" name="product_unit" placeholder="npr. kg">
                            <label>Št. kosov:</label> <input type="number" name="product_quantity" step="1" min="1"  placeholder="npr. 2">
                            <label>Kategorija:</label> 
                            <select name="product_category">
                                <?php 
                                    foreach ($category_table as $one)
                                        echo "<option value='".$one["id"]."'>".htmlspecialchars($one['name'])."</option>";
                                ?>
                            </select>
                            <label>Uporabno do (ni obvezno):</label> <input type="date" name="product_expires_on">
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
            <div class="top_row">
                <div class="title"><h2>ZALOGA</h2></div>
            </div>
            
            <div class="storage_toolbar">
                <div class="nav">
                    <div class="add_storage_location" id="add_storage_location"><img src="../img/add_24dp_3F3F3F_FILL0_wght400_GRAD0_opsz24.svg" alt="+">NOVO</div>
                    <?php
                        $st = 0;
                    foreach($storage_location_table as $k){
                        $is_chosen = ($chosen_id > 0 && (int)$k["id"] === $chosen_id);
                        if ($chosen_id === 0 && $st === 0) $is_chosen = true;
                        $c = $is_chosen ? "chosen_storage" : "";
                        echo "<div class='nav_item $c' id='id_".$k["id"]."' data-storage-id='".$k['id']."'>".$k["name"]."</div>";
                        $st++;
                    }
                    ?>
                </div>

                <div class="storage_toolbar_actions">
                    <div class="storage_toolbar_action add_product" id="add_product">
                        <img src="../img/add_24dp_3F3F3F_FILL0_wght400_GRAD0_opsz24.svg" alt="+">
                        <span>IZDELEK</span>
                    </div>
                    <div class="storage_toolbar_action add_category" id="add_category">
                        <img src="../img/add_24dp_3F3F3F_FILL0_wght400_GRAD0_opsz24.svg" alt="+">
                        <span>KATEGORIJA</span>
                    </div>
                </div>

            </div>

            <div class="content">
                <div class="empty_text" id="storage_empty_text" hidden>Prazno</div>
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
                            <td class="sortable" data-col="3" data-type="number">Kos</td>
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
            <div class="on_right_panel">
                <div class="on_right_title">ROK UPORABE SE IZTEKA:</div>
                <ul id="eat_soon_list" class="on_right_list"></ul>
                <div id="eat_soon_empty" class="on_right_empty" hidden>Prazno</div>
            </div>
            <div class="on_right_panel">
                <div class="on_right_title">ROK UPORABE JE POTEKEL:</div>
                <ul id="expired_list" class="on_right_list"></ul>
                <div id="expired_empty" class="on_right_empty" hidden>Prazno</div>
            </div>
            <div class="on_right_panel" id="last_panel">
                <div class="on_right_title">KATEGORIJE:</div>

                <ul class="on_right_list category_list" <?= empty($category_table) ? 'hidden' : '' ?>>
                    <?php foreach ($category_table as $category): ?>
                        <li class="category_row">
                            <span class="category_name"><?= htmlspecialchars($category["name"]) ?></span>

                            <form method="post" action="delete_category.php" onsubmit="return confirm('Izbrišem kategorijo?');">
                                <input type="hidden" name="category_id" value="<?= (int)$category["id"] ?>">
                                <button
                                    type="submit"
                                    class="category_delete_btn"
                                    aria-label="Izbriši kategorijo"
                                    title="Izbriši kategorijo"
                                >
                                    <img src="../img/delete_category.svg" alt="Izbriši">
                                </button>
                            </form>
                        </li>

                    <?php endforeach; ?>
                </ul>

                <div class="on_right_empty" <?= empty($category_table) ? '' : 'hidden' ?>>Prazno</div>
            </div>

        </div>

        <div id="nav_menu" class="menu">
            <div class="menu_item edit">Uredi lokacijo</div>
            <div class="menu_item delete">Izbriši lokacijo</div>
        </div>
        <div id="row_menu" class="menu">
            <div class="menu_item edit">Uredi</div>
            <div class="menu_item delete">Izbriši zapis</div>
        </div>

        
    </main>

    <script src="../../frontend/food_storage/load_storage.js"></script>
    <script src="../../frontend/food_storage/view_changer.js"></script>
    <script src="../../frontend/food_storage/form_validation.js"></script>
    <script src="../../frontend/food_storage/right_click.js"></script>
    <script src="../../frontend/common_code/sortable.js"></script>
</body>
</html>
