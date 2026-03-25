<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION["user_id"], $_SESSION["family_id"])) {
    header("Location: ../entry/login.php");
    exit;
}
if (($_SESSION["user_role"] ?? "") !== "Starš - admin") {
    header("Location: ../dashboard/dashboard.php");
    exit;
}

$roles = [];
$roles_sql = "SELECT id, user_role_name FROM user_role ORDER BY id";
$roles_res = $conn->query($roles_sql);
if ($roles_res) {
    while ($role = $roles_res->fetch_assoc()) {
        $roles[] = $role;
    }
    $roles_res->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje z družino - admin</title>
    <link rel="stylesheet" href="../../frontend/admin_page/admin_page.css">
    <link rel="stylesheet" href="../../frontend/sidebar/sidebar.css">
    <link rel="stylesheet" href="../../frontend/common_code/common_css.css">
    <link rel="stylesheet" href="../../frontend/common_code/open_space_settings.css">
    <link rel="stylesheet" href="../../frontend/common_code/form_window.css">
</head>
<body>
    <?php include "../sidebar/sidebar.php"; ?>
    <main>
        <div class="top_row">
            <div class="title"><h2>UPRAVLJANJE Z DRUŽINO</h2></div>
        </div>
        <div class="small_title left">Družina</div>
        <div class="content smaller">
            <div class="pin_row smaller">
                <table>
                    <colgroup>
                        <col style="width: 35%;">
                        <col style="width: 35%">
                        <col style="width: 15%">
                        <col style="width: 15%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="non-important low">IME</th>
                            <th class="non-important low">VSTOPNA KODA</th>
                        </tr>
                    </thead>
                    <tbody id="family_info"></tbody>
                </table>
            </div>
            <template id="family_info_template">
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </template>
        </div>

        <div class="small_title left">Uporabniki</div>
        <div class="content small">
            <div class="pin_row">
                <table>
                    <colgroup>
                        <col style="width: 15%;">
                        <col style="width: 15%">
                        <col style="width: 10%">
                        <col style="width: 25%">
                        <col style="width: 15%">
                        <col style="width: 10%">
                        <col style="width: 10%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="non-important low">IME</th>
                            <th class="non-important low">PRIIMEK</th>
                            <th class="non-important low">DATUM ROJSTVA</th>
                            <th class="non-important low">E-MAIL</th>
                            <th class="non-important low">VLOGA</th>
                        </tr>
                    </thead>
                    <tbody id="users_info"></tbody>
                </table>
            </div>
            <template id="user_info_template">
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </template>
        </div>

        <div class="small_title left">Točke</div>
        <div class="content smallest">
            <div class="pin_row">
                <table>
                    <colgroup>
                        <col style="width: 40%;">
                        <col style="width: 40%">
                        <col style="width: 20%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="non-important low">IME</th>
                            <th class="non-important low">TOCKE</th>
                        </tr>
                    </thead>
                    <tbody id="points_info"></tbody>
                </table>
            </div>
            <template id="points_info_template">
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </template>
        </div>

        <div class="add_something_view" id="add_something_view">
            <div class="update_family window" id="update_family_window">
                <div class="title">Uredi družino:</div>
                <form id="update_family_form" class="form" method="post" action="update_family.php">
                    <input type="hidden" name="family_id" id="update_family_id">
                    <div class="error" id="update_family_error" aria-live="polite" hidden></div>
                    <div class="field">
                        <label>Ime:</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="field">
                        <label>Vstopna koda:</label>
                        <input type="text" name="code" required>
                    </div>
                    <div class="btns">
                        <button type="reset" id="cancel_update_family_btn">Prekliči</button>
                        <button type="submit" id="update_family_btn">Posodobi</button>
                    </div>
                </form>
            </div>
            <div class="update_user window" id="update_user_window">
                <div class="title">Uredi osebne podatke:</div>
                <form id="update_user_form" class="form" method="post" action="update_user.php">
                    <input type="hidden" name="user_id" id="update_user_id">
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
                            <label>Vloga:</label>
                            <select name="role" id="update_user_role" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= (int)$role['id'] ?>"><?= htmlspecialchars($role['user_role_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Nastavi novo geslo:</label>
                            <input type="password" name="password_1">
                        </div>
                        <div class="field"></div>
                        <div class="field">
                            <label>Ponovi geslo:</label>
                            <input type="password" name="password_2">
                        </div>
                    </div>
                    <div class="btns">
                        <button type="reset" id="cancel_update_user_btn">Preklici</button>
                        <button type="submit" id="update_user_btn">Posodobi</button>
                    </div>
                </form>
            </div>
            <div class="update_points window" id="update_points_window">
                <div class="title">Uredi tocke:</div>
                <div class="points_user_name" id="update_points_user_name"></div>
                <form id="update_points_form" class="form" method="post" action="update_points.php">
                    <input type="hidden" name="user_id" id="update_points_user_id">
                    <div class="error" id="update_points_error" aria-live="polite" hidden></div>
                    <div class="field">
                        <label>Tocke:</label>
                        <input type="number" name="points" min="0" step="1" required>
                    </div>
                    <div class="btns">
                        <button type="reset" id="cancel_update_points_btn">Preklici</button>
                        <button type="submit" id="update_points_btn">Posodobi</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script src="../../frontend/common_code/common_js.js"></script>
    <script src="../../frontend/admin_page/admin_page.js"></script>
</body>
</html>
