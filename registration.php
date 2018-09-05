<?php
error_reporting(-1);
ini_set("display_errors", E_ALL);
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/controller.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/header.php');
?>
<div class="form_registration_wrapper">
    <h1>Task.manager</h1>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <?php if (isset($reg_error_name)): ?>
            <p class="reg_notice"><?= $reg_error_name ?></p>
        <?php endif; ?>
        <input type="text" name="name_reg" placeholder="name" value="<?= $name_reg ?>">
        <?php if (isset($reg_error_email)): ?>
            <p class="reg_notice"><?= $reg_error_email ?></p>
        <?php endif; ?>
        <input type="text" name="email_reg" placeholder=" E-mail" value="<?= $email_reg ?>">
        <?php if (isset($reg_error_password)): ?>
            <p class="reg_notice"><?= $reg_error_password ?></p>
        <?php endif; ?>
        <input type="text" name="password_reg" placeholder=" password" value="<?= $password_reg ?>">
        <div class="btn_wrapper">
            <input type="submit" name="registration" class="btn btn-success btn-sm" value="Зарегистрироваться">
            <a href="/" class="btn btn-warning btn-sm">Назад</a>
        </div>
    </form>
</div>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/footer.php');
?>