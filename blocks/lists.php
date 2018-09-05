<?php
(isset($_SESSION['my_list']) && $_SESSION['my_list'] == 'open') ? $openMyList = true : $openMyList = false;
(isset($_SESSION['invite_list']) && $_SESSION['invite_list'] == 'open') ? $openInviteList = true : $openInviteList = false;
?>
<div class="title_lists my_title_lists <?= $openMyList ? 'active_title_list' : '' ?>">
    <p>Мои папки(<?= getNumMyLists() ?>)</p>
    <i id="my_list" class="fa fa-arrow-circle-<?= $openMyList ? 'up' : 'down' ?>" aria-hidden="true"></i>
</div>

<div id="my_lists" <?= $openMyList ? 'style="display:block"' : '' ?> class="lists">
    <?php getMyLists(); ?>
    <div class="list_add">
        <input type="text" class="form-control name_new_list" placeholder="Название папки">
        <button type="button" class="btn btn-outline-success btn-sm add_new_list">Добавить папку</button>
    </div>
</div>

<div class="title_lists invite_title_lists <?= $openInviteList ? 'active_title_list' : '' ?>">
    <p>Участвую в списках(<?= getNumInviteList() ?>)</p>
    <i id="invite_list" class="fa fa-arrow-circle-<?= $openInviteList ? 'up' : 'down' ?>" aria-hidden="true"></i>
</div>
<div <?= $openInviteList ? 'style="display:block"' : '' ?> id="invite_lists" class="lists">
    <?php getInviteLists(); ?>
</div>
