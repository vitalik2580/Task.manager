<?php
$user = getUserInfo();
$task_colors = getColorsToTask();
?>
<div class="user">
    <div class="photo_user">
        <img src="<?= getUserAvatar($_SESSION['user_id']) ?>" width="70px" alt="ava">
    </div>
    <div class="name">
        <p><?= $user['lastname'] ?> <?= $user['name'] ?> <?= $user['surname'] ?></p>

        <?php if (!$isInviteList): ?>

            <div class="colors">
                <?php foreach ($task_colors as $key => $val) { ?>
                    <div data-color="<?= $val['rgb'] ?>" data-color-id="<?= $val['id'] ?>"
                         class="color_item <?= ($val['rgb'] == 'none') ? 'color_none' : '' ?>"
                         title="<?= $val['name'] ?>">
                    </div>
                <?php } ?>

            </div>

        <?php endif; ?>
    </div>
</div>