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

    //кнопка печати страницы
    $('body').on('click', '.btn-print', function () {
        $('.wrapper_task').printThis();
    });


    ///////////// Работа со списком со списками(папками) /////////////


    //выпадающий список "мои списки"
    $('body').on('click', '#my_list', function () {
        $('#my_lists').slideToggle(300);
        $(this).parent('.title_lists').toggleClass('active_title_list');
        if ($(this).attr('class') === 'fa fa-arrow-circle-down') {
            $(this).removeAttr('class');
            $(this).attr('class', 'fa fa-arrow-circle-up');
            $.ajax({
                url: "/include/controller.php",
                type: "POST",
                data: {
                    my_list: 'open'
                }
            });
        } else if ($(this).attr('class') === 'fa fa-arrow-circle-up') {
            $(this).removeAttr('class');
            $(this).attr('class', 'fa fa-arrow-circle-down');
            $.ajax({
                url: "/include/controller.php",
                type: "POST",
                data: {
                    my_list: 'close'
                }
            });
        }
    });

    //выпадающий список "участвую в списках"
    $('body').on('click', '#invite_list', function () {
        $('#invite_lists').slideToggle(300);
        $(this).parent('.title_lists').toggleClass('active_title_list');
        if ($(this).attr('class') === 'fa fa-arrow-circle-down') {
            $(this).removeAttr('class');
            $(this).attr('class', 'fa fa-arrow-circle-up');
            $.ajax({
                url: "/include/controller.php",
                type: "POST",
                data: {
                    invite_list: 'open'
                }
            });
        } else if ($(this).attr('class') === 'fa fa-arrow-circle-up') {
            $(this).removeAttr('class');
            $(this).attr('class', 'fa fa-arrow-circle-down');
            $.ajax({
                url: "/include/controller.php",
                type: "POST",
                data: {
                    invite_list: 'close'
                }
            });
        }
    });

    //появление настроек при наведении мыши
    $('body').on('mouseover', '.list_item', function () {
        $(this).children('.settings').css('display', 'block');
    });

    //скрытие настроек при наведении мыши
    $('body').on('mouseout', '.list_item', function () {
        $(this).children('.settings').css('display', 'none');
    });

    //закрывает модальное окно при клике на кнопку закрыть
    $('body').on('click', '.close_modal', function () {
        location.reload();
    });

    //отправляет изменёные настройки списка(папки) контроллеру
    $('body').on('click', '.success_modal', function () {

        var name_list = $(this).parents('.my_modal').children('input').val();
        var arrCheckedUsers = [];

        $("input:checked").each(function () {
            arrCheckedUsers.push($(this).val());
        });
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                change_list: 1,
                name_list: name_list,
                arrCheckedUsers: arrCheckedUsers,
            },
            success: function (html) {
                $('.my_modal').html(html);
            }
        });

    });

    //добавление класса active элементу списка при клике
    $('body').on('click', '.list_item', function (e) {
        $('.list_item').removeClass('active_list');
        $(this).addClass('active_list');
        var id = $(this).attr('data-list-id');
        //удаляем предыдущие таски
        $('.wrapper_task').empty();
        //и загружаем новые
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                list_id: id,
                active_list: 1
            },
            //обновляем данные на странице
            success: function () {
                location.reload();
            }
        });
        //удаление списка(папки)
    }).on('click', '.delete_list', function (e) {
        e.stopPropagation();
        var list_id = $(this).parents('.list_item').attr('data-list-id');
        //запрос на сервер с id папки
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                delete_list: 1,
                list_id: list_id
            },
            beforeSend: function () {
                $('#my_lists').css('opacity', .5);
            },
            success: function (html) {
                $('#my_lists').css('opacity', 1);
                $('.my_lists').html(html);
            }
        });
    });

    //добавление новой папки в список
    $('body').on('click', '.add_new_list', function () {
        var val = $('.name_new_list').val();
        //отправляем данные на сервер с именем новой папки
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                name_list: val,
                add_new_list: 1
            },
            //обновляем список со списками
            success: function (html) {
                $('.my_lists').html(html);
            }
        });
    });


    ///////////// Работа с тасками /////////////


    //добавляет возможность перетаскивать элементы
    $('.wrapper_task').sortable({
        handle: "span",
        axis: 'y',
        update: function () {
            //записываем расположение тасков в массив по порядку
            var order = $('.wrapper_task').sortable('toArray');
            $.ajax({
                url: "/include/controller.php",
                type: "POST",
                data: {
                    order: order,
                    update_sort: 1
                },
                success: function (html) {
                    $('.wrapper_task').html(html);
                }
            });
        }
    });

    //удаление таска
    $('body').on('click', '.delete_task', function () {
        var task_id = $(this).parents('.task').attr('id');
        //запрос на сервер с id таска
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                delete_task: 1,
                task_id: task_id
            },
            beforeSend: function () {
                $('.wrapper_task').css('opacity', .5);
            },
            success: function (html) {
                $('.wrapper_task').css('opacity', 1);
                $('.wrapper_task').html(html);
            }
        });
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                update_lists: 1,
            },
            success: function (html) {
                $('.my_lists').html(html);
            }
        });
        //рассылка уведомлений по email
        $.ajax({
            url: "/include/send_mail_notice.php",
            type: "POST",
            data: {
                delete_task: 1,
                task_id: task_id
            }
        });
        //открытие модального окна с настройками списка(папки)
    }).on('click', '.setting_list', function (e) {
        e.stopPropagation();
        var list_id = $(this).parents('.list_item').attr('data-list-id');
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                setting_list: 1,
                list_id: list_id
            },
            success: function (html) {
                $('.wrapper_modal').html(html);
                $('.wrapper_modal').css('display', 'block');
            }
        });
    });

    //Добавление нового таска
    $('body').on('click', '.add_new_task', function () {
        var val = $('#text_new_task').val();
        $('#text_new_task').val('');
        //отправляем на сервер текст нового таска
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                text_task: val,
                add_new_task: 1
            }, beforeSend: function () {
                $('.wrapper_task').css('opacity', .5);
            },
            success: function (html) {
                $('.wrapper_task').css('opacity', 1);
                $('.wrapper_task').html(html);
            }
        });
        //обновляем список со списками
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                update_lists: 1,
            },
            success: function (html) {
                $('.my_lists').html(html);
            }
        });

    });

    //при нажатии enter добавляется запись
    $('body').on('keydown', '#text_new_task', function (e) {
        if (e.keyCode == 13) {
            var val = $('#text_new_task').val();
            $('#text_new_task').val(' ');
            //отправляем на сервер текст нового таска
            $.ajax({
                url: "/include/controller.php",
                type: "POST",
                data: {
                    text_task: val,
                    add_new_task: 1
                },
                success: function (html) {
                    $('.wrapper_task').html(html);
                }
            });
            //обновляем список со списками
            $.ajax({
                url: "/include/controller.php",
                type: "POST",
                data: {
                    update_lists: 1,
                },
                success: function (html) {
                    $('.my_lists').html(html);
                }
            });
        }
    });

    //задаём цвета иконкам с цветом тасков
    $('.color_item').each(function (index) {
        var color = $(this).attr('data-color');
        $(this).css('background-color', color);
    });

    //редактирование таска
    $('body').on('click', '.setting_task_btn', function () {
        $(this).parents('.wrapper_task_item').hide();
        $(this).parents('.wrapper_task_item').siblings('.setting_task').css('display', 'flex');
    });

    //закрыть редактирование без сохранения изменений
    $('body').on('click', '.cancel_setting_task', function () {
        $(this).parents('.setting_task').css('display', 'none');
        $(this).parents('.task').children('.wrapper_task_item').show();
    });

    //показать коментарии к таску
    $('body').on('click', '.task', function (e) {
        $('.task').attr('class', 'task');
        $(this).attr('class', 'task active_task');
        var taskId = $(this).attr('id');
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                comments_to_task_id: taskId
            },
            success: function (html) {
                $('.comments_wrapper').empty();
                $('.comments_wrapper').html(html);
            }
        });
        //сохрнение изменений таска
    }).on('click', '.success_setting_task', function (e) {
        e.stopPropagation();
        $(this).parents('.setting_task').css('display', 'none');
        $(this).parents('.task').children('.wrapper_task_item').show();
        var modifiedTextTask = $(this).parents('.setting_task').children('textarea').val();
        var taskId = $(this).parents('.task').attr('id');
        //только так смог добраться до селекта
        var moveToFolder = $(this).parents('.task').children('.setting_task').children('p').children('.move_to_folder_task').val();
        //отправляем изменения таска
        $.ajax({
            url: "include/controller.php",
            type: "POST",
            data: {
                change_task_id: taskId,
                move_to_folder: moveToFolder,
                modified_text: modifiedTextTask
            },
            beforeSend: function (e) {
                $('.wrapper_task').css('opacity', .5);
            },
            success: function (html) {
                $('.wrapper_task').html(html);
                $('.wrapper_task').css('opacity', 1);
            }
        });
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                update_lists: 1,
            },
            success: function (html) {
                $('.my_lists').html(html);
            }
        });
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                update_comments: 1,
            },
            success: function (html) {
                $('.comments_wrapper').html(html);
            }
        });
    });

    //добавляем цвет таску при клике на иконку с цветом
    $('body').on('click', '.color_item', function () {
        var color = $(this).attr('data-color-id');
        //отправляем изменения таска
        $.ajax({
            url: "include/controller.php",
            type: "POST",
            data: {
                change_color_task: color,
            },
            success: function (html) {
                $('.wrapper_task').html(html);
            }
        });
    });


    ///////////// Работа с коментариями /////////////


    //добавить коментарий
    $('body').on('click', '.btn_add_comment', function () {
        var textComment = $('.text_comment').val();
        $('.text_comment').val('');
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                add_comment: 1,
                comment_text: textComment
            },
            beforeSend: function () {
                $('.comments_wrapper').css('opacity', .5);
            },
            success: function (html) {
                $('.comments_wrapper').css('opacity', 1);
                $('.comments_wrapper').html(html);
            }
        });
    });

    $('body').on('click', '.user_info_link', function () {
        var userId = $(this).attr('data-user-id');
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                show_user_info: 1,
                user_id_info: userId
            },
            success: function (html) {
                $('.wrapper_modal').html(html);
                $('.wrapper_modal').css('display', 'block');
            }
        });
    });

    //закрывает модальное окно при клике на кнопку закрыть
    $('body').on('click', '.close_user_info', function () {
        $('.wrapper_modal').css('display', 'none');
    });


    ///////////// Настройки пользователя /////////////


    //маска отображения номера телефона
    $('.settings_phone').mask("+7 (999) 999-99-99");

    //отображение имени фотографии которую хотим загрузить
    $('body').on('change', '.avatar', function () {
        $('.file_name_avatar').html($('.avatar')[0].files[0].name);
    });
    //при выборе страны изменяется список городов
    $('body').on('change', '#country', function () {
        var country_id = $(this).find("option:selected").val();
        $.ajax({
            url: "/include/controller.php",
            type: "POST",
            data: {
                selected_country: country_id
            },
            success: function (html) {
                $('#city').html(html);
            }
        });
    });

    //при загрузке страницы загружается список городов согласно уже выбранной стране
    var country_id = $('#country').find("option:selected").val();
    $.ajax({
        url: "/include/controller.php",
        type: "POST",
        data: {
            selected_country: country_id
        },
        success: function (html) {
            $('.city').html(html);
        }
    });

});

