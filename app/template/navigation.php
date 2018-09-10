<nav class="navbar navbar-dark bg-dark">
    <img src="img/logo-Task.png" width="150" height="50" alt="Task.manager">
    <div class="d-inline-flex p-2">
        <?php if ($isAdmin) : ?>
            <a href="<?= $pathLink ?>" class="admin_link btn btn-primary btn-sm"><?= $nameLink ?></a>
        <?php endif; ?>
        <p class="user_name"><?= $user['name'] ?> <?= $user['lastname'] ?>
            (<a class="user_settings" href="<?= $pathSettingLink ?>">
                <i class="fa <?= $nameSettingLink ?>" aria-hidden="true"></i>
            </a>)
        </p>
        <button type="button" class="btn_exit btn btn-outline-danger btn-sm">Выйти</button>
    </div>
</nav>