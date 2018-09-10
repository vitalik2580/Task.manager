$(document).ready(function () {

    //кнопка выхода
    $('body').on('click', '.btn_exit', function () {
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {exit: 1},
            success: function () {
                location.reload();
            }
        });
    });

    //ajax подгрузка доступных пользователю списков(папок)
    $('body').on('click', '.setting_list_item', function () {
        $(this).parents('.admin_panel_list_item').find('.col_wrapper').css('display', 'none');
        $(this).parents('.admin_panel_list_item').find('.col_settings').css('display', 'flex');
        $(this).parents('.admin_panel_list_item').find('.setting_list_item').css('display', 'none');
        $(this).parents('.admin_panel_list_item').find('.admin_list_settings').css('display', 'flex');
        //получаем id пользователя
        var list_item = $(this).parents('.admin_panel_list_item').find('.changeUserName').on('change', function () {
            var user_id = $(this).find("option:selected").val();
            $.ajax({
                url: "/include/controller.php",
                type: "POST",
                data: {
                    admin_panel_selected_user: user_id
                },
                success: function (html) {
                    list_item.parents('.admin_panel_list_item').find('.select_city').html(html);
                }
            });
        });
    });

    //отменить изменения записи
    $('body').on('click', '.cancel_admin_setting', function () {
        location.reload();
    });

    //сохранить изменения записи
    $('body').on('click', '.success_admin_setting', function () {
        var task_id = $(this).parents('.admin_panel_list_item').attr('data-task-id');
        var list_id = $(this).parents('.admin_panel_list_item').find('.changeUserLists option:selected').val();
        var date = $(this).parents('.admin_panel_list_item').find('.task_date input').val();
        var text_task = $(this).parents('.admin_panel_list_item').find('.text_task textarea').val();
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                admin_change_task: 1,
                task_id: task_id,
                list_id: list_id,
                date: date,
                text_task: text_task
            },
            success: function () {
                location.reload();
            }
        });
    });

    //удалить запись
    $('body').on('click', '.admin_delete_task', function () {
        var task_id = $(this).parents('.admin_panel_list_item').attr('data-task-id');
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                admin_delete_task: 1,
                task_id: task_id
            },
            success: function () {
                location.reload();
            }
        });
    });
});