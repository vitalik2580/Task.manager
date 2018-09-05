<?php
$user = getUserInfo();
?>
<nav class="navbar navbar-dark bg-dark">
    <img src="img/logo-Task.png" width="150" height="50" alt="Task.manager">
    <div class="d-inline-flex p-2">
        <?php if ($_SERVER['REQUEST_URI'] == '/admin_panel.php'): ?>
            <a href="/" class="admin_link btn btn-primary btn-sm">Назад</a>
        <?php endif; ?>
        <?php if (isAdmin() && $_SERVER['REQUEST_URI'] !== '/admin_panel.php'): ?>
            <a href="/admin_panel.php" class="admin_link btn btn-primary btn-sm">Панель администратора</a>
        <?php endif; ?>

        <p class="user_name"><?= $user['name'] ?> <?= $user['lastname'] ?>
            ( <?php if ($_SERVER['REQUEST_URI'] != '/settings.php') : ?>
                <a class="user_settings" href="/settings.php"><i class="fa fa-cog" aria-hidden="true"></i></a>
            <?php elseif ($_SERVER['REQUEST_URI'] == '/settings.php'): ?>
                <a class="user_settings" href="/"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
            <?php endif; ?>
            )
        </p>
        <button type="button" class="btn_exit btn btn-outline-danger btn-sm">Выйти</button>
    </div>
</nav>