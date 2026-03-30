<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../entry/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Familoop - Obroki</title>
    <link rel="stylesheet" href="../../frontend/meals/meals.css">
    <link rel="stylesheet" href="../../frontend/sidebar/sidebar.css">
    <link rel="stylesheet" href="../../frontend/common_code/common_css.css">
    <link rel="stylesheet" href="../../frontend/common_code/open_space_settings.css">
    <link rel="stylesheet" href="../../frontend/common_code/form_window.css">
</head>
<body>
    <?php include "../sidebar/sidebar.php"; ?>
    <main>
        <div class="top_row">
            <div class="title"> <h2>PLANIRANJE OBROKOV</h2> </div>
        </div>
        <div class="content">
            <table id="table_1">
                <thead>
                    <colgroup>
                        <col style="width: 25%">
                        <col style="width: 25%">
                        <col style="width: 25%">
                        <col style="width: 25%">
                    </colgroup>
                    <tr>
                        <th>Dan</th>
                        <th>Zajtrk</th>
                        <th>Kosilo</th>
                        <th>Večerja</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <!-- js napolni -->
                </tbody>
            </table>
        </div>
        <div class="add_something_view" id="add_something_view">
            <div class="add_meal window" id="add_meal_window">
                <div class="title">Dodaj obrok:</div>
                <form id="add_meal_form" class="form" method="post" action="add_meal_in_db.php">
                    <label>Naziv:</label> <input type="text" name="new_meal">
                    <input type="hidden" name="date_of_meal" id="meal_date">
                    <input type="hidden" name="meal_category" id="meal_type">
                    <input type="hidden" name="meal_id" id="meal_id">
                    <div class="btns">
                        <button type="reset" id="cancel_meal_btn">Prekliči</button>
                        <button type="submit" id="add_new_meal_btn">Dodaj</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <div id="row_menu" class="menu">
        <div class="menu_item edit">Uredi</div>
        <div class="menu_item delete">Izbriši</div>
    </div>
    <script src="../../frontend/meals/fill_table.js" ></script>
    <script src="../../frontend/meals/form.js" ></script>
    <script src="../../frontend/meals/right_click.js" ></script>
</body>
</html>