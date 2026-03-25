<?php
$currentScript = basename($_SERVER["SCRIPT_NAME"] ?? "");
?>
<aside class="sidebar">
        <ul class="sidebar-nav">
            <li class="sidebar-item sidebar-logo">
                <a href="../dashboard/dashboard.php" aria-label="Nadzorna plosca"><img src="../img/logo.png"></a>
            </li>
            <li class="sidebar-item interactive <?= $currentScript === "dashboard.php" ? "sidebar-item-active" : "" ?>">
                <a href="../dashboard/dashboard.php" <?= $currentScript === "dashboard.php" ? 'aria-current="page"' : "" ?>><span class="sidebar-link-text">Domov</span></a>
            </li>
            <li class="sidebar-item interactive <?= $currentScript === "calendar.php" ? "sidebar-item-active" : "" ?>">
                <a href="../calendar/calendar.php" <?= $currentScript === "calendar.php" ? 'aria-current="page"' : "" ?>><span class="sidebar-link-text">Koledar</span></a>
            </li>
            <li class="sidebar-item interactive <?= $currentScript === "shopping_list.php" ? "sidebar-item-active" : "" ?>">
                <a href="../shopping_list/shopping_list.php" <?= $currentScript === "shopping_list.php" ? 'aria-current="page"' : "" ?>><span class="sidebar-link-text">Nakupovalni seznami</span></a>
            </li>
            <li class="sidebar-item interactive <?= $currentScript === "tasks.php" ? "sidebar-item-active" : "" ?>">
                <a href="../tasks/tasks.php" <?= $currentScript === "tasks.php" ? 'aria-current="page"' : "" ?>><span class="sidebar-link-text">Opravila</span></a>
            </li>
            <li class="sidebar-item interactive <?= $currentScript === "food_storage.php" ? "sidebar-item-active" : "" ?>">
                <a href="../food_storage/food_storage.php" <?= $currentScript === "food_storage.php" ? 'aria-current="page"' : "" ?>><span class="sidebar-link-text">Zaloga hrane</span></a>
            </li>
            <li class="sidebar-item interactive <?= $currentScript === "meals.php" ? "sidebar-item-active" : "" ?>">
                <a href="../meals/meals.php" <?= $currentScript === "meals.php" ? 'aria-current="page"' : "" ?>><span class="sidebar-link-text">Obroki</span></a>
            </li>
            <?php if (($_SESSION["user_role"] ?? "") === "Starš - admin"): ?>
            <li class="sidebar-item interactive <?= $currentScript === "admin_page.php" ? "sidebar-item-active" : "" ?>" id="admin_page">
                <a href="../admin_page/admin_page.php" <?= $currentScript === "admin_page.php" ? 'aria-current="page"' : "" ?>><span class="sidebar-link-text">Upravljanje z družino</span></a>
            </li>
            <?php endif; ?>

        </ul>
        <a class="bottom" href="../entry/logout.php">
            <div class="logout_font">Odjava</div>
            <div class="info_font" id="sidebar_user_info">-</div>
        </a>
</aside>
<script src="../../frontend/sidebar/sidebar_fill.js"></script>
