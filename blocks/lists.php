<div class="title_lists my_title_lists <?= $myListStatus ? 'active_title_list' : '' ?>">
    <p>Мои папки(<?= $countMyList ?>)</p>
    <i id="my_list" class="fa fa-arrow-circle-<?= $myListStatus ? 'up' : 'down' ?>" aria-hidden="true"></i>
</div>

<div id="my_lists" <?= $myListStatus ? 'style="display:block"' : '' ?> class="lists">
    <?php getMyLists($userId); ?>
    <div class="list_add">
        <input type="text" class="form-control name_new_list" placeholder="Название папки">
        <button type="button" class="btn btn-outline-success btn-sm add_new_list">Добавить папку</button>
    </div>
</div>

<div class="title_lists invite_title_lists <?= $inviteListStatus ? 'active_title_list' : '' ?>">
    <p>Участвую в списках(<?= $countInviteList ?>)</p>
    <i id="invite_list" class="fa fa-arrow-circle-<?= $inviteListStatus ? 'up' : 'down' ?>" aria-hidden="true"></i>
</div>
<div <?= $inviteListStatus ? 'style="display:block"' : '' ?> id="invite_lists" class="lists">
    <?php getInviteLists($userId); ?>
</div>
