<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obroki</title>
    <link rel="stylesheet" href="meals.css">
    <link rel="stylesheet" href="../sidebar/sidebar.css">
    <link rel="stylesheet" href="../common_code/common_css.css">
    <link rel="stylesheet" href="../common_code/form_window.css">
    <link rel="stylesheet" href="../common_code/open_space_settings.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>
    <?php include "../sidebar/sidebar.php"; ?>
    <main>
        <div class="top_row">
            <div class="title"> <h2>PLANIRANJE OBROKOV</h2> </div>
        </div>
        <div class="content">
            <table>
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
    </main>
    <script src="fill_table.js" ></script>
</body>
</html>