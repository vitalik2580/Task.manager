<div class="user_info">
    <img src="<?= $pathAva ?>" height="100px" alt="">
    <div class="description">
        <p><?= $user_info['lastname'] ?> <?= $user_info['name'] ?> <?= $user_info['surname'] ?></p>
        <p>Зарегистрирован: <?= $user_info['date'] ?></p>
        <p>Состоит в группах:
            <?php foreach ($user_groups as $key => $val) { ?>
                <span title="<?= $val['description'] ?>"><?= $val['name'] ?>;</span>
            <?php } ?>
        </p>
        <p>Номер телефона: <a href="tel:<?= $phone ?>"><?= $phone ?></a></p>
        <p>Проживает: <?= $lives ?></p>
    </div>
    <i class="close_user_info fa fa-times" aria-hidden="true"></i>
</div>