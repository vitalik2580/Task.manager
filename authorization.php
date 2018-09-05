<?php
error_reporting(-1);
ini_set("display_errors", E_ALL);
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/controller.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/header.php');
?>
<div class="form_auth_wrapper">
    <h1>Task.manager</h1>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <?php if (!isset($_COOKIE['login'])) : ?>
            <input type="email" name="email_auth" placeholder=" e-mail" value="<?= $email_auth ?>">
        <?php endif; ?>
        <?php if (isset($_COOKIE['login'])) : ?>
            <input type="hidden" name="email_auth" value="<?= $_COOKIE['login'] ?>">
        <?php endif; ?>
        <?php if (isset($_COOKIE['login'])) : ?>
            <p><?= $_COOKIE['login'] ?></p>
        <?php endif; ?>
        <input type="password" name="password_auth" placeholder=" password" value="<?= $password_auth ?>">
        <div class="btn_wrapper">
            <?php if (isset($error_auth) && !empty($error_auth)) : ?>
                <p style="font-size: 16px; color: #F19701;"><?= $error_auth ?></p>
            <?php endif; ?>
            <input type="submit" name="entrance" class="btn btn-success btn-sm" value="Войти">
            <?php if (isset($_COOKIE['login'])) : ?>
                <input type="submit" name="change_login" class="btn btn-warning btn-sm" value="Сменить логин">
            <?php endif; ?>
            <a href="/registration.php" class="btn btn-outline-primary btn-sm">Регистрация</a>
        </div>
    </form>
</div>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/footer.php');
?>
