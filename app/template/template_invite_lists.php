<?php foreach ($inviteList as $key => $val) { ?>
    <div data-list-id="<?= $val['id'] ?>"
         class="list_item <?= (isset($_SESSION['list_id']) && $val['id'] == $_SESSION['list_id']) ? 'active_list' : '' ?>">
        <p><?= $val['name'] ?>(<?= getNumThisTasks($val['id']) ?>)</p>
    </div>
<?php } ?>
