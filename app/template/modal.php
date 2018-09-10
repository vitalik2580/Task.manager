<p>Имя папки:</p>
<input type="text" value="<?= $nameList ?>">
<div class="user_lists">
    <?php if (isset($notice)) : ?>
        <p style="color: <?= $notice['color'] ?>"><?= $notice['text'] ?></p>
    <?php endif; ?>
    <p>Разрешить просматривать:</p>
    <?php foreach ($arrUsers as $key => $val) {
        if ($_SESSION['user_id'] !== $val['id']) {
            ?>
            <p><?= $val['lastname'] ?> <?= $val['name'] ?> <?= $val['surname'] ?>
                <input value="<?= $val['id'] ?>" type="checkbox" <?= $val['checked'] ?>>
            </p>
            <?php
        }
    }
    ?>
    <button type="button" class="success_modal btn btn-success btn-sm">Сохранить</button>
    <button type="button" class="close_modal btn btn-danger btn-sm">Закрыть</button>
</div>