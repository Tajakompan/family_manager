<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="admin_page.css">
    <link rel="stylesheet" href="../sidebar/sidebar.css">
    <link rel="stylesheet" href="../common_code/common_css.css">
    <link rel="stylesheet" href="../common_code/form_window.css">
    <link rel="stylesheet" href="../common_code/open_space_settings.css">
</head>
<body>
    <?php include "../sidebar/sidebar.php"; ?>
    <main>
        <div class="top_row">
            <div class="title"> <h2>UPRAVLJANJE Z DRUZINO</h2> </div>
        </div>
        <div class="small_title left">Druzina</div>
        <div class="content">
            <div id="family_info" class="pin_row"></div>
            <template id="family_info_template">
                <table><tr>
                    <td>IME DRUZINE</td>
                    <td>KODA DRUZINE</td>
                    <td>IZBRISI DRUZINO</td>
                </tr></table>
            </template>
        </div>
        <div class="small_title left">Uporabniki</div>
        <div class="content">
            <div id="users_info" class="pin_row"></div>
            <template id="user_info_template">
                <table><tr>
                    <td>IME</td>
                    <td>priimek</td>
                    <td>datum_rojstva</td>
                    <td>mail</td>
                    <td>vloga</td>
                    <td>IZBRISI userja</td>
                </tr></table>
            </template>
        </div>
        <div class="small_title left">Tocke</div>
        <div class="content">
            <div id="points_info" class="pin_row"></div>
            <template id="points_info_template">
                <table><tr>
                    <td>IME</td>
                    <td>Tocke</td>
                </tr></table>
            </template>
        </div>
    </main>
    <script src="../common_code/common_js.js"></script>
    <script src="admin_page.js"></script>
</body>
</html>
