<?php foreach ($list as $key => $val) { ?>
    <div data-list-id="<?= $val['id'] ?>"
         class="list_item <?= (isset($_SESSION['list_id']) && $val['id'] == $_SESSION['list_id'])? 'active_list':'' ?>">
        <!--проверка на возможность просматривать список другими пользователями-->
        <?php if (inviteList($val['id'])) : ?>
            <span><i class="fa fa-users" aria-hidden="true"></i></span>
        <?php endif; ?>

        <p><?= $val['name'] ?> (<?= getNumThisTasks($val['id']) ?>)</p>
        <div class="settings">
            <a href="#"><i class="setting_list fa fa-cog" aria-hidden="true"></i></a>
            <a href="#"><i class="delete_list fa fa-times" aria-hidden="true"></i></a>
        </div>
    </div>
<?php } ?>
