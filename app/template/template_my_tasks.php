<?php foreach ($tasks as $key => $val) { ?>
    <div id="<?= $val['id'] ?>"
         class="task <?= (isset($_SESSION['task_id']) && $_SESSION['task_id'] == $val['id'])? 'active_task':'' ?>">
        <div class="wrapper_task_item">
            <?php if (!$isInviteList): ?>
                <span><i class="fa fa-bars" aria-hidden="true"></i></span>
            <?php endif; ?>
            <div class="wrapper_task_text">
                <p style="background-color:<?= $val['rgb'] ?>"><?= $val['text'] ?></p>
            </div>
            <?php if (!$isInviteList): ?>
                <div class="settings">
                    <a href="#" data-title="Изменить запись"><i class="setting_task_btn fa fa-cog" aria-hidden="true"></i></a>
                    <a href="#" data-title="Удалить запись"><i class="delete_task fa fa-times" aria-hidden="true"></i></a>
                </div>
            <?php endif; ?>
        </div>
        <div class="setting_task">
            <textarea type="text"><?= $val['text'] ?></textarea>
            <div class="settings">
                <a href="#"><i class="success_setting_task fa fa-check" aria-hidden="true"></i></a>
                <a href="#"><i class="cancel_setting_task fa fa-times" aria-hidden="true"></i></a>
            </div>

            <p>Переместить в папку:
                <select class="move_to_folder_task" name="">
                    <?php foreach ($arrMylists as $k => $v) { ?>
                        <option value="<?= $v['id'] ?>" <?= ($v['id'] == $_SESSION['list_id']) ? 'selected' : '' ?> >
                            <?= $v['name'] ?>
                        </option>
                    <?php } ?>
                </select>
            </p>
        </div>
    </div>
<?php } ?>