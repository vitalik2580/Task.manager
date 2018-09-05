<p>Имя папки:</p>
<input type="text" value="<?= $nameList ?>">

<div class="user_lists">
    <?php if (isset($notice['error'])) : ?>
        <p style="color: red"><?= $notice['error'] ?></p>
    <?php endif; ?>
    <?php if (isset($notice['success'])) : ?>
        <p style="color: green;"><?= $notice['success'] ?></p>
    <?php endif; ?>
    <p>Разрешить просматривать:</p>
    <?php foreach ($arrUsers as $key => $val) {
        if ($_SESSION['user_id'] !== $val['id']) {
            ?>

            <p><?= $val['lastname'] ?> <?= $val['name'] ?> <?= $val['surname'] ?>

                <input value="<?= $val['id'] ?>" type="checkbox"
                    <?= (checkInviteUsers($arrSharedList, $val['id'])) ? 'checked' : '' ?>>

            </p>

            <?php
        }
    }
    ?>
    <button type="button" class="success_modal btn btn-success btn-sm">Сохранить</button>
    <button type="button" class="close_modal btn btn-danger btn-sm">Закрыть</button>
</div>
