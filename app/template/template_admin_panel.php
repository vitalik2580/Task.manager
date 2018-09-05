<div class="admin_panel_wrapper">
    <div class="row admin_panel_list_item" style="color: #5F7C8A;font-weight: bold;">
        <div class="col-1 task_id">
            <div class="col_wrapper">
                <p>ID</p>
            </div>
        </div>

        <div class="col-4">
            <div class="col_wrapper">
                <p>Текст записи</p>
            </div>
        </div>

        <div class="col-2">
            <div class="col_wrapper">
                <p>Название списка(папки)</p>
            </div>
        </div>

        <div class="col-3">
            <div class="col_wrapper">
                <p>Фамилия Имя Отчество</p>
            </div>
        </div>
        <div class="col-2">
            <div class="col_wrapper">
                <p>Дата создания</p>
            </div>
        </div>
    </div>
    <?php
    foreach ($admin_lists as $key => $val) {
        $myLists = myLists($val['user_id']);
        if (!empty($val['task_text'])) { ?>
            <div data-task-id="<?= $val['task_id'] ?>" class="row admin_panel_list_item">
                <div class="col-1 task_id">
                    <div class="admin_list_settings">
                        <a href="#" data-title="Сохранить изменения"><i class="success_admin_setting fa fa-check"
                                                                        aria-hidden="true"></i></a>
                        <a href="#" data-title="Отменить изменения"><i class="cancel_admin_setting fa fa-times"
                                                                       aria-hidden="true"></i></a>
                    </div>
                    <div class="setting_list_item">
                        <a href="#" data-title="Изменить запись"><i class="setting_list_item fa fa-cog"
                                                                    aria-hidden="true"></i></a>
                    </div>
                    <p><?= $val['task_id'] ?></p>
                </div>

                <div class="col-4 text_task">
                    <div class="col_wrapper">
                        <p><?= $val['task_text'] ?></p>
                    </div>
                    <div class="col_settings">
                        <textarea><?= $val['task_text'] ?></textarea>
                    </div>
                </div>

                <div class="col-2">

                    <div class="col_wrapper">
                        <p><?= $val['list_name'] ?></p>
                    </div>

                    <div class="col_settings">
                        <select class="changeUserLists" name="">
                            <?php foreach ($myLists as $keyList => $valList) { ?>
                                <option value="<?= $valList['id'] ?>" <?= ($val['list_id'] == $valList['id']) ? 'selected' : '' ?>>
                                    <?= $valList['name'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                </div>
                <div class="col-3">

                    <div class="col_wrapper">
                        <p><?= $val['lastname'] ?> <?= $val['name'] ?> <?= $val['surname'] ?></p>
                    </div>

                    <div class="col_settings">
                        <select class="changeUserName" name="">
                            <?php foreach ($allUsers as $keyUser => $valUser) { ?>
                                <option value="<?= $valUser['id'] ?>" <?= ($val['user_id'] == $valUser['id']) ? 'selected' : '' ?>>
                                    <?= $valUser['lastname'] ?> <?= $valUser['name'] ?> <?= $valUser['surname'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                </div>
                <div class="col-2 task_date">
                    <div class="col_wrapper">
                        <p><?= $val['task_date'] ?></p>
                    </div>
                    <div class="col_settings">
                        <input type="date" value="<?= $val['task_date'] ?>">
                    </div>
                    <a href="#" class="admin_delete_task" data-title="Удалить запись"><i class="fa fa-times"
                                                                                         aria-hidden="true"></i></a>
                </div>
            </div>
            <?php
        }
    }
    ?>
</div>