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
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="../sidebar/sidebar.css">
    <link rel="stylesheet" href="../common_code/common_css.css">
    <link rel="stylesheet" href="../common_code/form_window.css">
    <link rel="stylesheet" href="../common_code/open_space_settings.css">
</head>
<body>
    <?php include "../sidebar/sidebar.php"; ?>
    <div class="vertical_flex">
        <span class="title_left"><h2><?=$greeting?>,&nbsp;<span style="color: #96CA43"><?=htmlspecialchars($_SESSION["user_name"])?></span></h2></span>
        <main class="horizontal_flex">
            <div class="content">
                <div class="gray grid">
                    <div class="col">
                        <div class="pin" id="task_pin">
                            <div class="small_pin_title">moja opravila</div>
                            <div class="mini_main">
                                <ul id="task_list"></ul>
                            </div>
                        </div>
 
                        <div class="pin" id="shopping_list_pin">
                            <div class="small_pin_title">nujno kupi</div>
                            <div class="mini_main">
                                <ul id="shopping_list"></ul>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="pin" id="day_time">
                            <div class="clock_layout" id="clock_layout">
                                <div class="clock_face" aria-label="Analogna ura">
                                    <div class="clock_hand hour" id="clock_hour_hand"></div>
                                    <div class="clock_hand minute" id="clock_minute_hand"></div>
                                    <div class="clock_hand second" id="clock_second_hand"></div>
                                    <div class="clock_center"></div>
                                </div>
                                <div class="clock_meta">
                                    <div class="clock_digital" id="time">--:--</div>
                                    <div class="clock_day" id="day_name">-</div>
                                    <div class="clock_date" id="date">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="pin" id="storage_pin">
                            <div class="small_pin_title">pomembno v zalogi</div>
                            <div class="mini_main">
                                <ul id="storage_list"></ul>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="pin" id="events_pin">
                            <div class="small_pin_title">današnji dogodki</div>
                            <div class="mini_main">
                                <ul id="events_list"></ul>
                            </div>
                        </div>

                        <div class="pin" id="meals_pin">
                            <div class="small_pin_title">današnji obroki</div>
                            <div class="mini_main">
                                <ul id="meals_list"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="left profile_card">
                <div class="avatar"></div>
                <div class="profile_field">
                    <div class="name" id="profile_name">-</div>
                    <div class="role" id="profile_role">-</div>
                    <div class="email" id="profile_email">-</div>
                </div>
                <div class="profile_btn" id="option_btn">
                    <span aria-hidden="true">&#9881;</span> &nbsp; možnosti
                </div>
            </div>
        </main>
        <div class="add_something_view" id="add_something_view">
            <div class="update_user window" id="update_user_window">
                <div class="title">Uredi osebne podatke:</div>
                <form id="update_user_form" class="form" method="post" action="update_user.php">
                    <div class="error" id="update_user_password_error" aria-live="polite" hidden></div>
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
                            <label>E-mail:</label>
                            <input type="email" name="email">
                        </div>
                        <div class="field">
                            <label>Datum rojstva:</label>
                            <input type="date" name="birthdate">
                        </div>
                        <div class="field">
                            <label>Nastavi novo geslo:</label>
                            <input type="password" name="password_1">
                        </div>
                        <div class="field">
                            <label>Ponovi geslo:</label>
                            <input type="password" name="password_2">
                        </div>
                    </div>
                    <div class="btns">
                        <button type="reset" id="cancel_update_user_btn">Prekliči</button>
                        <button type="submit" id="update_user_btn">Posodobi</button>
                    </div>
                </form>
            </div>
            <div class="upload_image window" id="upload_image_window">
                <div class="title">Spremeni profilno sliko:</div>
                <form id="upload_image_form" class="form" action="upload_profile_image.php" method="POST" enctype="multipart/form-data">
                    <div class="error" id="upload_image_error" aria-live="polite" hidden></div>
                    <label for="profile_image">Izberi profilno sliko:</label>
                    <input type="file" name="profile_image" id="profile_image" accept="image/*" required>
                    <div class="btns">
                        <button type="reset" id="cancel_upload_image_btn">Prekliči</button>
                        <button type="submit" id="upload_image_btn">Posodobi</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="row_menu" class="menu">
            <div class="menu_item" id="edit_data">Uredi podatke</div>
            <div class="menu_item"id="change_image">Zamenjaj uporabniško sliko</div>
            <div class="menu_item red"id="delete_user">Izbriši uporabnika</div>
        </div>
    </div>
    <script src="pin_fill.js"></script>
    <script src="option_btn.js"></script>
    <script src="profile_card_fill.js"></script>
</body>
</html>




