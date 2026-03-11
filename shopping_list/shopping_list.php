<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../entry/login.php");
    exit;
}
$family_id = (int)$_SESSION["family_id"];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nakupovalni seznami</title>
    <link rel="stylesheet" href="shopping_list.css">
    <link rel="stylesheet" href="../sidebar/sidebar.css">
    <link rel="stylesheet" href="../common_code/common_css.css">
    <link rel="stylesheet" href="../common_code/form_window.css">
    <link rel="stylesheet" href="../common_code/open_space_settings.css">
</head>
<body>
    <?php include "../sidebar/sidebar.php"; ?>
    <main>
        <div class="top_row">
            <div class="title"> <h2>NAKUPOVALNI SEZNAM</h2> </div>
            <div class="button_space"> <button id="add_shop_btn">Dodaj seznam</button> </div>
        </div>
        <div class="content">
            <div id="pins_container"></div>

            <template id="shopping_pin_template">
            <div class="pin">
                <table class="shopping_lists_container">
                <colgroup>
                    <col style="width: 52%">
                    <col style="width: 20%">
                    <col style="width: 14%">
                    <col style="width: 14%">
                </colgroup>

                <thead>
                    <tr>
                        <td class="ctr shop_name" colspan="3">TRGOVINA</td>
                        <td class="ctr add_btn"><img class="table_icon" src="../img/add_24dp_3F3F3F_FILL0_wght400_GRAD0_opsz24.svg" alt="Dodaj izdelek"></td>
                    </tr>
                    <tr>
                        <td class="sortable" data-col="0" data-type="text">Izdelek</td>
                        <td class="ctr sortable" data-col="1" data-type="number">Kos</td>
                        <td class="ctr sortable" data-col="2" data-type="text">Nuja</td>
                        <td class="ctr"><img class="table_icon" src="../img/check_24dp_3F3F3F_FILL0_wght400_GRAD0_opsz24.svg" alt="Kupljeno"></td>
                    </tr>
                </thead>

                <tbody class="shopping_list_table_body">
                    <tr><td colspan="4">Nalagam…</td></tr>
                </tbody>
                </table>
            </div>
            </template>

            <div id="scheduling_container">
                <div class="small_title">POSPRAVI:</div>
                <div class="item_pins">

                </div>
            </div>

            <template id="store_item_template">
                <div class="store_pin">
                    <table>
                        <colgroup>
                            <col style="width: 50%">
                            <col style="width: 15%">
                            <col style="width: 15%">
                            <col style="width: 20%">
                        </colgroup>
                        <tr>
                            <td class="pname" style="font-weight: bold"></td>
                            <td class="pamount" style="text-align: right; padding-right: 5px"></td>
                            <td class="punit"></td>
                            <td class="pqty"><td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <label>Rok uporabe:</label>
                                <input type="date" class="expires_on">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <label>Lokacija:</label>
                                <select class="storage_id"></select>
                            </td>
                            <td colspan="2">
                                <button class="store_btn" type="button">Pospravi</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </template>
            
            <div id="add_something_view" class="add_something_view">
                <div class="add_shop window" id="add_shop_window">
                    <div class="title">Dodaj nov izdelek v zalogo:</div>
                    <form id="add_shop_form" class="form" method="post" action="add_shop_in_db.php">
                        <label>Ime seznama:</label> <input type="text" name="new_shop">
                        <div class="btns">
                            <button type="reset" id="cancel_shop_btn">Prekliči</button>
                            <button type="submit" id="add_new_shop_btn">Dodaj</button>
                        </div>
                    </form>
                </div>

                <div class="add_product window" id="add_product_window">
                    <div class="title">Dodaj nov izdelek na seznam:</div>
                    <form id="add_product_form" class="form" method="post" action="add_product_on_list.php">
                        <input type="hidden" name="shop_id" id="product_shop_id">
                        <label>Naziv izdelka:</label> <input type="text" name="product_name">
                        <label>Količina:</label> <input type="text" name="product_amount">
                        <label>Enota:</label> <input type="text" name="product_unit">
                        <label>Kvantiteta:</label> <input type="number" name="product_quantity" min="1" step="1" required>
                        <label>Nujnost:</label>
                        <select name="product_necessity" required>
                            <option value="low">low</option>
                            <option value="medium" selected>medium</option>
                            <option value="high">high</option>
                        </select>
                        <div class="btns">
                            <button type="reset" id="cancel_product_btn">Prekliči</button>
                            <button type="submit" id="add_product_on_list_btn">Dodaj</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="nav_menu" class="menu">
                <div class="menu_item delete">Izbriši lokacijo</div>
            </div>
            <div id="row_menu" class="menu">
                <div class="menu_item delete">Izbriši zapis</div>
            </div>
        </div>
    </main>
    <script src="shopping_list.js"></script>
    <script src="shopping_list_table.js"></script>
    <script src="right_click.js"></script>
    <script src="product_form_validation.js"></script>
    <script src="store_items_div.js"></script>
    <script src="../common_code/sortable.js"></script>
</body>
</html>


