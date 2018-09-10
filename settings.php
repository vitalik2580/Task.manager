<?php
error_reporting(-1);
ini_set("display_errors", 1);
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/session_start.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/controller.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/function.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/header.php");
getNavBar();
$user_info = getUserName($_SESSION['user_id']);
$countries = getCountries();
?>
    <div class="settings_container">
        <div class="row">

            <div class="col-6 settings_left">
                <div class="avatar_wrapper">
                    <div class="user_avatar">
                        <img src="<?= getUserAvatar($_SESSION['user_id']) ?>" alt="">
                        <div class="btn_settings_avatar">
                            <form class="upload_avatar" action="<?= $_SERVER['PHP_SELF'] ?>"
                                  enctype="multipart/form-data" method="POST">

                                <input type="submit" name="delete_ava" class="btn btn-outline-danger btn-sm"
                                       value="Удалить"/>
                                <input type="submit" name="upload_ava"
                                       class="upload_avatar btn btn-outline-warning btn-sm" value="Загрузить другое"/>
                                <label>
                                    <span class="btn btn-outline-secondary btn-sm">...</span>
                                    <input class="avatar" name="avatar" type="file"/>
                                </label>
                            </form>
                        </div>
                        <span class="file_name_avatar"></span>
                    </div>
                    <?php if (isset($notice_upload)) : ?>
                        <p style="color: #FEC007"><?= $notice_upload ?></p>
                    <?php endif; ?>
                    <?php if (isset($notice_delete)) : ?>
                        <p style="color: #F34236"><?= $notice_delete ?></p>
                    <?php endif; ?>
                </div>
            </div>


            <div class="col-6 settings_right">
                <h4>Личная информация</h4>
                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
                    <?= isset($notice_lastname) ? getSettingsNotice($notice_lastname) : '' ?>
                    <label>Фамилия: <input type="text" name="lastname"
                                           value="<?= isset($lastname) ? $lastname : $user_info['lastname'] ?>">
                    </label>

                    <?= isset($notice_name) ? getSettingsNotice($notice_name) : '' ?>
                    <label>Имя: <input type="text" name="name"
                                       value="<?= isset($name) ? $name : $user_info['name'] ?>">
                    </label>

                    <?= isset($notice_surname) ? getSettingsNotice($notice_surname) : '' ?>
                    <label>Отчество: <input type="text" name="surname"
                                            value="<?= isset($surname) ? $surname : $user_info['surname'] ?>">
                    </label>

                    <?= isset($notice_email) ? getSettingsNotice($notice_email) : '' ?>
                    <label>E-mail: <input type="email" name="email"
                                          value="<?= isset($email) ? $email : $user_info['email'] ?>">
                    </label>

                    <?= isset($notice_password) ? getSettingsNotice($notice_password) : '' ?>

                    <?php if (isset($success_password)): ?>
                        <p class="form_success"><?= $success_password ?></p>
                    <?php endif; ?>
                    <label>Новый пароль: <input type="text" name="new_password"></label>

                    <br>

                    <label>Страна: <select id="country" name="country">
                            <?php foreach ($countries as $key => $val) { ?>
                                <option value="<?= $val['id'] ?>"
                                    <?= $user_info['country_id'] == $val['id'] ? 'selected' : '' ?>><?= $val['name'] ?>
                                </option>
                            <?php } ?>
                        </select></label>
                    <label>Город:
                        <span class="city">

                        </span>
                    </label>

                    <?= isset($notice_phone) ? getSettingsNotice($notice_phone) : '' ?>
                    <label>Телефон(моб.): <input class="settings_phone" type="text" name="phone"
                                                 value="<?= isset($phone) ? $phone : $user_info['phone'] ?>">
                    </label>

                    <p>
                        <input class="notice_email" name="notice_email" type="checkbox"
                            <?= $user_info['email_notice'] != 0 ? 'checked' : '' ?>>
                        Присылать уведомления на e-mail
                    </p>
                    <div class="save_settings_user">
                        <button type="submit" name="save_change_settings" class="btn btn-outline-success btn-sm">
                            Сохранить
                        </button>
                        <button type="submit" name="no_save_change_settings" class="btn btn-outline-danger btn-sm">
                            Отменить
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/footer.php"); ?>