<div data-list-id="<?= $val['id'] ?>" class="list_item <?= $classList ?>">
    <!--проверка на возможность просматривать список другими пользователями-->
    <?php if ($inviteList) : ?>
        <span><i class="fa fa-users" aria-hidden="true"></i></span>
    <?php endif; ?>
    <p><?= $val['name'] ?> (<?= $numThisTask ?>)</p>
    <div class="settings">
        <a href="#"><i class="setting_list fa fa-cog" aria-hidden="true"></i></a>
        <a href="#"><i class="delete_list fa fa-times" aria-hidden="true"></i></a>
    </div>
</div>