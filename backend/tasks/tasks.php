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
    <link rel="stylesheet" href="../../frontend/tasks/tasks.css">
    <link rel="stylesheet" href="../../frontend/sidebar/sidebar.css">
    <link rel="stylesheet" href="../../frontend/common_code/common_css.css">
    <link rel="stylesheet" href="../../frontend/common_code/form_window.css">
    <link rel="stylesheet" href="../../frontend/common_code/open_space_settings.css">
    <title>Opravila</title>
</head>
<body>
    <?php include __DIR__ . "/../sidebar/sidebar.php"; ?>
    <main>
        <div class="top_row">
            <div class="title"> <h2>OPRAVILA</h2> </div>
            <div class="button_space"> <button id="add_task_btn">Dodaj opravilo</button> </div>
        </div>

        <div id="add_something_view" class="add_something_view">
            <div class="add_task window" id="add_task_window">
                <div class="title">Ustvari novo opravilo:</div>
                <form id="add_task_form" class="form" method="post" action="add_task_in_db.php">
                    <input type="hidden" name="task_id">
                    <label>Opravilo:</label> <input type="text" name="new_task">
                    <label>Podrobnosti:</label> <input type="textarea" name="details">
                    <label>Opraviti do:</label> <input type="date" name="to_do_by">
                    <label>Točke za uspeh:</label> <input type="number" name="points" value="2">
                    <div class="check_row">
                        <input type="checkbox" id="no_date" name="no_date">
                        <label for="no_date" class="check_label">Ni časovne omejitve</label>
                    </div>
                    <div class="btns">
                        <button type="reset" id="cancel_task_btn">Prekliči</button>
                        <button type="submit" id="add_new_task_btn">Dodaj</button>
                    </div>
                </form>
            </div>
            <div class="details_frame window" id="details_frame">
                <div class="small_title">Podrobnosti</div>
                <div class="details"></div>
            </div>
        </div>
        <div class="tasks_bottom">
            <div class="center_content" id="big_container">
                <div class="small_title left">Moja opravila</div>
                <div class="my_tasks gray" id="my_tasks_container"></div>

                <template id="pin_template"> 
                    <div class="pin">
                        <div class="task_title"></div>
                        <div class="task_data">
                            <span class="atr">Ustvaril: </span>
                            <span class="val created_by"></span>
                        </div>
                        <div class="task_data">
                            <span class="atr">Izvesti do: </span>
                            <span class="val to_do_by"></span>
                        </div>
                        <div class="last">
                            <div class="left_data">
                                <div class="task_data">
                                    <span class="atr">Točke: </span>
                                    <span class="val points"></span>
                                </div>
                                <div class="task_data">
                                    <span class="atr">Izvaja: </span>
                                    <span class="val doers"></span>
                                </div>
                            </div>
                            <div class="right_data btns one">
                                <button type="button" class="action_btn">
                                    <img class="icon_img" src="../img/add_24dp_3F3F3F_FILL0_wght400_GRAD0_opsz24.svg" alt="Prevzemi opravilo">
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="small_title left">Ostala opravila</div>
                <div class="all_tasks gray" id="other_tasks_container"></div>
            </div>
            <div class="right_column">
                <div class="points_container">
                    <div class="small_title">TOČKE</div>
                    <table class="points_table">
                        <thead>
                            <tr>
                                <th>Ime</th>
                                <th>Točke</th>
                            </tr>
                        </thead>
                        <tbody id="points_tbody"></tbody>
                    </table>
                </div>

                <div class="history_container">
                    <div class="small_title">ZGODOVINA OPRAVIL</div>
                    <div id="task_history_list" class="task_history_list"></div>
                </div>
            </div>

        </div>

        <div id="row_menu" class="menu">
            <div class="menu_item details">Podrobnosti</div>
            <div class="menu_item edit">Uredi</div>
            <div class="menu_item delete">Izbriši</div>
        </div>

        <script>
            window.currentUserRole = <?= json_encode($_SESSION["user_role"] ?? "", JSON_UNESCAPED_UNICODE) ?>;
        </script>

        <script src="../../frontend/tasks/tasks.js"></script>
        <script src="../../frontend/tasks/pin_fill.js"></script>
        <script src="../../frontend/tasks/points_fill.js"></script>
        <script src="../../frontend/tasks/form_validation.js"></script>
        <script src="../../frontend/tasks/right_click.js"></script>
        <script src="../../frontend/common_code/common_js.js"></script>
        <script src="../../frontend/tasks/task_history_fill.js"></script>

    </main>

</body>
</html>


