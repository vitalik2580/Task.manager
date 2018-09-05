<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/controller.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/session_start.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/function.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/header.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/navigation.php");
$isInviteList = isInviteList();
?>
<div class="my_container">
    <div class="row">

        <div class="col-3 my_lists">
            <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/lists.php"); ?>
        </div>

        <div class="col-5 my_tasks">

            <?php if (!$isInviteList): ?>
                <div class="new_task">
                    <textarea id="text_new_task"></textarea>
                    <button type="button" class="btn btn-outline-success btn-sm add_new_task">Добавить запись</button>
                </div>
            <?php endif; ?>
            <div class="printing">
                <button type="button" class="btn-print btn btn-outline-success btn-sm">Печать</button>
            </div>
            <div class="wrapper_task">
                <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/tasks.php"); ?>
            </div>

        </div>

        <div class="col-4 tasks_comments">

            <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/user_info.php"); ?>

            <div class="comments_wrapper">
                <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/comments.php"); ?>
            </div>
            <div class="add_comment">
                <textarea class="text_comment" name="" id="" cols="30" rows="10"></textarea>
                <button type="button" class="btn_add_comment btn btn-outline-success btn-sm">Отправить</button>
            </div>

        </div>

    </div>
</div>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/footer.php");
?>


