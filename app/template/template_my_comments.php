<?php
foreach ($comments as $key => $val) {
    ?>
    <div id="<?= $val['id'] ?>" class="comment">
        <div class="title_comment">
            <img src="<?= getUserAvatar($val['create_user_id']) ?>" height="50px" alt="ava">
            <a class="user_info_link" data-user-id="<?= $val['create_user_id'] ?>" href="#">
                <?= $val['lastname'] ?> <?= $val['name'] ?> <?= $val['surname'] ?>
            </a>
            <p><?= $val['date'] ?> , <?= $val['time'] ?></p>
        </div>
        <p><?= $val['text'] ?></p>
    </div>
    <?php
}
?>
