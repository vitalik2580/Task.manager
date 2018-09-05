<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/function.php");

//авторизация
$email_auth = '';
$password_auth = '';
//проверка была ли нажата кнопка входа
if (isset($_POST['entrance'])) {
    $error_auth = '';
    //защита от sql инекции
    $email_auth = sqlProtected($_POST['email_auth']);
    $password_auth = sqlProtected($_POST['password_auth']);
    //проверка на пустоту вводимых данных
    if (empty($email_auth) && empty($password_auth)) {
        $error_auth = 'Вы ничего не ввели';
    } else {
        $query = "SELECT * FROM users WHERE email = '$email_auth'";
        $result = mysqli_query(connectDB(), $query);
        if (mysqli_num_rows($result) > 1) {
            $error_auth = 'Ошибка email';
            exit();
        } else {
            $row = mysqli_fetch_assoc($result);
            //если есть такой email то сравниваем хеш вводимого пароля с хешем из БД
            if ($row['password'] == hash('sha256', $password_auth)) {
                //проверка была ли запущенна сессия ранее
                if (!isset($_SESSION['active'])) session_start();
                setcookie(session_name(), session_id(), time() + 60 * 20, '/');
                setcookie('login', $email_auth, time() + 60 * 60 * 24 * 30, '/');
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['active'] = 1;
                $_SESSION['login'] = $email_auth;
                $email_auth = '';
                $password_auth = '';
                $error_auth = '';
                unset($error_auth);
                header('Location: /');
                exit();
            }
        }
        $error_auth = 'Не верно введён логин или пароль';
    }
}
//смена логина
if (isset($_POST['change_login'])) {
    if (isset($_COOKIE['login'])) {
        setcookie('login', '', 3, '/');
        header("Location: /authorization.php");
    }
}

//регистрация
$name_reg = '';
$email_reg = '';
$password_reg = '';
if (isset($_POST['registration'])) {
    $name_reg = sqlProtected($_POST['name_reg']);
    $email_reg = sqlProtected($_POST['email_reg']);
    $password_reg = $_POST['password_reg'];
    //Валидация email
    if (!empty($email_reg)) {
        $query = "SELECT `email`
              FROM `users`
              WHERE `email` = '$email_reg'";
        $result = mysqli_query(connectDB(), $query);
        if (mysqli_num_rows($result) > 0) {
            $reg_error_email = 'Пользователь с таким E-mail уже зарегистрирован.';
        }
        if (!filter_var($email_reg, FILTER_VALIDATE_EMAIL)) {
            $reg_error_email = 'Не допустимые значения в поле E-mail.';
        }
    } else {
        $reg_error_email = 'Поле E-mail не заполнено.';
    }

    //валидация имени
    if (!empty($name_reg)) {
        if (preg_match("/[^a-zа-я]/iu", $name_reg)) {
            $reg_error_name = 'Не допустимые значения в поле Name.';
        }
    } else {
        $reg_error_name = 'Поле Name не заплнено.';
    }


    //валидация пароля
    if (!empty($password_reg)) {
        if (preg_match("/[^a-z0-9]/iu", $password_reg)) {
            $reg_error_password = 'Не допустимые значения в поле Password.';
        } elseif (!preg_match("/^[a-z0-9\w]{4,10}$/iu", $password_reg)) {
            $reg_error_password = 'В поле Password должно быть от 4 до 10 символов';
        }
    } else {
        $reg_error_password = 'Не заполнено поле Password.';
    }

    if (!isset($reg_error_password) && !isset($reg_error_name) && !isset($reg_error_email)) {
        $date = date("Y-m-d H:h:s", time());
        $password_reg = hash('sha256', $password_reg);
        $query = "INSERT INTO `users`(`name`, `email`, `password`, `date_registration`) 
                  VALUES('$name_reg', '$email_reg', '$password_reg', '$date')";
        if ($result = mysqli_query(connectDB(), $query)) {
            $query = "INSERT INTO `users_groups`(`user_id`, `group_id`)
                  VALUES((SELECT `id` FROM `users` WHERE `email` = '$email_reg'), '2')";
            if ($result = mysqli_query(connectDB(), $query)) {
                $password_reg = $_POST['password_reg'];
                setcookie('login', $email_reg, time() + 60 * 60 * 24 * 30, '/');
                header('Location: /authorization.php');
            } else {
                $reg_error = 'Произошла ошибка при регистрации';
            }
        } else {
            $reg_error = 'Произошла ошибка при регистрации';
        }
    }

}

if (!isset($_SESSION['active'])) session_start();


//кнопка выхода
if (isset($_POST['exit'])) {
    setcookie(session_name(), '', 3);
    session_destroy();
    header("Location: /authorization.php");
}

//записываем в сессию id активного списка
if (isset($_POST['active_list'])) {
    $_SESSION['list_id'] = sqlProtected($_POST['list_id']);
    $_SESSION['task_id'] = false;
}

//добавление в БД нового списка(папки)
if (isset($_POST['add_new_list'])) {
    $user_info = getUserInfo($_SESSION['user_id']);
    if (!empty($_POST['name_list'])) {
        $newList = sqlProtected($_POST['name_list']);
        $query = "INSERT INTO lists(`name`, `created_user_id`) 
                  VALUES('$newList', '" . $_SESSION['user_id'] . "')";
        mysqli_query(connectDB(), $query);
    }
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/lists.php");
}

//Добавление нового таска
if (isset($_POST['add_new_task'])) {
    if (!empty($_POST['text_task'])) {
        $date = date("Y-m-d", time());
        $user_info = getUserInfo($_SESSION['user_id']);
        $textTask = sqlProtected($_POST['text_task']);
        $query = "INSERT INTO `tasks`(`text`, `list_id`, `date`)
                  VALUES('$textTask', '" . $_SESSION['list_id'] . "', '$date')";
        mysqli_query(connectDB(), $query);

        $query = "SELECT `name`
                  FROM `lists`
                  WHERE `id` = '" . $_SESSION['list_id'] . "'";
        $result = mysqli_query(connectDB(), $query);
        $name_list = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        //текст сообщения
        $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
            " добавил(а) новую запись '$textTask' в папке '" . $name_list['name'] . "'.";
        sendEmailNotice($_SESSION['list_id'], $message);
    }
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/tasks.php");
}

//удаление таска
if (isset($_POST['delete_task'])) {
    $user_info = getUserInfo($_SESSION['user_id']);
    $task_id = sqlProtected($_POST['task_id']);
    $task_id = preg_replace("/[^0-9]/", '', $task_id);

    //текст удоляемого таска
    $query = "SELECT `tasks`.`text` AS `text`, `lists`.`name` AS `name`
              FROM `tasks`
              LEFT JOIN `lists`
              ON `tasks`.`list_id` = `lists`.`id`
              WHERE `tasks`.`id` = '$task_id'";
    $result = mysqli_query(connectDB(), $query);
    $text_info = mysqli_fetch_assoc($result);

    //удаляем таск
    $query = "DELETE FROM `tasks`
              WHERE `id` = '$task_id'";
    mysqli_query(connectDB(), $query);
    if ($_SESSION['task_id'] == $task_id) {
        $_SESSION['task_id'] = false;
    }
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/tasks.php");
    mysqli_free_result($result);
    //текст сообщения
    $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
        " удалил(а) запись '" . $text_info['text'] . "' в папке '" . $text_info['name'] . "'.";
    sendEmailNotice($_SESSION['list_id'], $message);
}

//обновление сортировки тасков
if (isset($_POST['update_sort'])) {
    $order = $_POST['order'];
    $i = 1;
    foreach ($order as $key => $val) {
        $query = "UPDATE `tasks` 
                  SET `sort` ='$i' 
                  WHERE id='" . sqlProtected($val) . "'";
        mysqli_query(connectDB(), $query);
        $i++;
    }
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/tasks.php");
}

//удаление списка(папки)
if (isset($_POST['delete_list'])) {
    $user_info = getUserInfo($_SESSION['user_id']);
    $list_id = sqlProtected($_POST['list_id']);
    //имя изменяемого списка(папки)
    $query = "SELECT `lists`.`name` AS `name`
              FROM `lists`
              WHERE `lists`.`id` = '$list_id'";
    $result = mysqli_query(connectDB(), $query);
    $name_list = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    //текст сообщения
    $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
        " удалил(а) список '" . $name_list['name'] . "'.";
    sendEmailNotice($_SESSION['list_id'], $message);
    $query = "DELETE FROM `lists` 
              WHERE `id` = '$list_id'";
    mysqli_query(connectDB(), $query);
    if ($_SESSION['list_id'] == $list_id) {
        $_SESSION['list_id'] = false;
    }
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/lists.php");
}

//обновление коментариев
if (isset($_POST['update_comments'])) {
    require($_SERVER['DOCUMENT_ROOT'] . "/blocks/comments.php");
}

//обновление списка с тасками
if (isset($_POST['update_tasks'])) {
    require($_SERVER['DOCUMENT_ROOT'] . "/blocks/tasks.php");
}

//обновление списка со списками
if (isset($_POST['update_lists'])) {
    require($_SERVER['DOCUMENT_ROOT'] . "/blocks/lists.php");
}

//изменение таска
if (isset($_POST['change_task_id'])) {
    $user_info = getUserInfo($_SESSION['user_id']);
    $task_id = sqlProtected($_POST['change_task_id']);
    $move_to_folder_id = sqlProtected($_POST['move_to_folder']);
    $modified_text_task = sqlProtected($_POST['modified_text']);

    $query = "SELECT `tasks`.`text`, `tasks`.`list_id`, `lists`.`name`
              FROM `tasks`
              LEFT JOIN `lists`
              ON `lists`.`id` = `tasks`.`list_id`
              WHERE `tasks`.`id` = '$task_id'";
    $result = mysqli_query(connectDB(), $query);
    $row = mysqli_fetch_assoc($result);

    if ($row['list_id'] != $move_to_folder_id) {
        $_SESSION['task_id'] = false;
        if ($row['text'] == $modified_text_task) {

            $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
                " переместил(а) запись '$modified_text_task' в папку '" . $row['name'] . "'.";
            sendEmailNotice($_SESSION['list_id'], $message);
        } else {
            $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
                " изменил(а) запись '" . $row['text'] . "' на '$modified_text_task' и переместил(а) в папку '" . $row['name'] . "'.";
            sendEmailNotice($_SESSION['list_id'], $message);
        }
    } else {

        if ($row['list_id'] != $move_to_folder_id) {
            $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
                " изменил(а) запись'" . $row['text'] . "' на '$modified_text_task' и переместила  в папку '" . $row['name'] . "'.";
            sendEmailNotice($_SESSION['list_id'], $message);
        } else {
            $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
                " изменил(а) запись '" . $row['text'] . "' на '$modified_text_task' в папке '" . $row['name'] . "'.";
            sendEmailNotice($_SESSION['list_id'], $message);
        }
    }
    mysqli_free_result($result);

    $query = "UPDATE `tasks` 
              SET `list_id` = '$move_to_folder_id', `text` = '$modified_text_task' 
              WHERE `id` = '$task_id'";
    $result = mysqli_query(connectDB(), $query);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/tasks.php");
}

//добавить коментарий
if (isset($_POST['add_comment'])) {
    $query = "SELECT `tasks`.`id` 
              FROM `tasks` 
              WHERE `tasks`.`id` = '" . $_SESSION['task_id'] . "'";
    $result = mysqli_query(connectDB(), $query);
    if (mysqli_fetch_assoc($result)) {
        //добавляем коментарий
        addComment($_POST['comment_text']);
        $commentText = sqlProtected($_POST['comment_text']);
        //информация о пользователе
        $user_info = getUserInfo($_SESSION['user_id']);
        //имя списка в котором находится таск
        $query = "SELECT `lists`.`name` 
                  FROM `lists` 
                  WHERE `lists`.`id` = '" . $_SESSION['list_id'] . "'";
        $result = mysqli_query(connectDB(), $query);
        $row = mysqli_fetch_assoc($result);
        $nameList = $row['name'];
        //текст таска
        $query = "SELECT `tasks`.`text` 
                  FROM `tasks` 
                  WHERE `tasks`.`id` = '" . $_SESSION['task_id'] . "'";
        $result = mysqli_query(connectDB(), $query);
        $row = mysqli_fetch_assoc($result);
        $textTask = $row['text'];
        $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
            " оставил(а) коментарий '$commentText' к записи '$textTask' находящейся в папке '$nameList'.";
        sendEmailNoticeComment($_SESSION['list_id'], $message);
        require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/comments.php");
    }
}

//выводит коментарии к таску
if (isset($_POST['comments_to_task_id'])) {
    $_SESSION['task_id'] = sqlProtected($_POST['comments_to_task_id']);
    //getMyComments();
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/comments.php");
}

//выводит модальное окно с настройками списка(папки)
if (isset($_POST['setting_list'])) {
    $_SESSION['list_id'] = sqlProtected($_POST['list_id']);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/modal.php");
}

//настройки списка(папки)
if (isset($_POST['change_list'])) {
    $notice = [];
    $user_info = getUserInfo($_SESSION['user_id']);
    //количество пользователей которым можно просматривать список
    is_array($_POST['arrCheckedUsers']) ? $countShared = count($_POST['arrCheckedUsers']) : $countShared = 0;
    //массив с id пользователей которым разрешено просматривать список
    $arrUsersSharedId = $_POST['arrCheckedUsers'];
    //имя списка
    $newNameList = sqlProtected($_POST['name_list']);
    //максимальное количество пользователей просматривания списка 5
    if ($countShared > 5) {
        //в случае если их больше 5 пользователей то объявляем переменную с ошибкой
        $notice['error'] = "Вы выбрали <b>$countShared</b> пользователей, лимит 5";
    } else {
        //id активного списка
        $list_id = $_SESSION['list_id'];
        //если меньше 5 пользователей то через цикл заносим всех пользователй которым разрешён просмотр списка в БД
        $query = "INSERT IGNORE INTO `shared_lists` 
                  VALUES ";
        //формируем один запрос
        for ($i = 0; $i < $countShared; $i++) {
            $query .= "('$list_id', '" . $arrUsersSharedId[$i] . "', '" . $_SESSION['user_id'] . "'),";
        }
        $query = substr($query, 0, -1);
        mysqli_query(connectDB(), $query);

        //старое имя папки
        $query = "SELECT `lists`.`name` 
                  FROM `lists` 
                  WHERE `lists`.`id` = '$list_id'";
        $result = mysqli_query(connectDB(), $query);
        $row = mysqli_fetch_assoc($result);
        $oldNameList = $row['name'];
        if ($oldNameList != $newNameList) {
            $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
                " изменил(а) название списка с '$oldNameList' на '$newNameList'.";
            sendEmailNotice($_SESSION['list_id'], $message);
        }

        //обновляем имя списка(папки)
        $query = "UPDATE `lists` 
                  SET `name` = '$newNameList' 
                  WHERE `id` = '$list_id'";
        mysqli_query(connectDB(), $query);
        //удалеем всех не выделенных пользователей из таблицы
        if ($countShared > 0) {
            $query = "DELETE FROM `shared_lists`
                      WHERE `list_id` = '$list_id'
                      AND `to_user_id` NOT IN (";
            for ($i = 0; $i < $countShared; $i++) {
                $query .= "'$arrUsersSharedId[$i]',";
            }
            $query = substr($query, 0, -1);
            $query .= ")";
        } else {
            $query = "DELETE FROM `shared_lists`
                      WHERE `list_id` = '$list_id'";
        }
        mysqli_query(connectDB(), $query);
        $notice['success'] = "Изменения сохранены";
    }
    getModal($notice);
    //require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/template_modal.php");
}

//записываем состояние меню в сессию
if (isset($_POST['invite_list'])) {
    $_SESSION['invite_list'] = sqlProtected($_POST['invite_list']);
}

//записываем состояние меню в сессию
if (isset($_POST['my_list'])) {
    $_SESSION['my_list'] = sqlProtected($_POST['my_list']);
}

//изменяем и сохраняем цвет таска
if (isset($_POST['change_color_task'])) {
    //информация о пользователе
    $user_info = getUserInfo($_SESSION['user_id']);
    $color = sqlProtected($_POST['change_color_task']);
    $query = "UPDATE `tasks` 
              SET `color_id` = '$color' 
              WHERE `id` = '" . $_SESSION['task_id'] . "' ";
    mysqli_query(connectDB(), $query);
    //имя списка в котором находится таск
    $query = "SELECT `lists`.`name` 
              FROM `lists` 
              WHERE `lists`.`id` = '" . $_SESSION['list_id'] . "'";
    $result = mysqli_query(connectDB(), $query);
    $row = mysqli_fetch_assoc($result);
    $nameList = $row['name'];
    //текст таска
    $query = "SELECT `tasks`.`text` 
              FROM `tasks` 
              WHERE `tasks`.`id` = '" . $_SESSION['task_id'] . "'";
    $result = mysqli_query(connectDB(), $query);
    $row = mysqli_fetch_assoc($result);
    $textTask = $row['text'];
    //название цвета
    $query = "SELECT `name` 
              FROM `colors` 
              WHERE `id` = '$color'";
    $row = mysqli_query(connectDB(), $query);
    $result = mysqli_query(connectDB(), $query);
    $row = mysqli_fetch_assoc($result);
    $nameColor = $row['name'];
    //сообщение
    $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
        " изменил(а) цвет записи '$textTask' на '$nameColor' в папке '$nameList'.";
    sendEmailNotice($_SESSION['list_id'], $message);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/tasks.php");
}

//модальное окно с информацией о пользователе который оставил этот коментарий
if (isset($_POST['show_user_info'])) {
    $user_id = sqlProtected($_POST['user_id_info']);
    $user_info = getUserInfo($user_id);
    $user_groups = getUserGroups($user_id);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/template_user_info.php");
}

//Загрузка аватарки
if (isset($_POST['upload_ava']) && !empty($_FILES['avatar'])) {
    //максимальный размер файла 1Mb
    $maxSizeFile = 1048576;
    //количество файлов
    $maxUploadFiles = 1;
    //разрешённые форматы
    $allowedValues = ["image/jpeg", "image/pjpeg", "image/png"];
    if (count($_FILES['avatar']['name']) <= $maxUploadFiles) {
        //путь где хранятся аватарки
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . "/avatars/";
        if (empty($_FILES['avatar']['error'])) {
            if ($_FILES['avatar']['size'] <= $maxSizeFile) {
                //проверка на соответствие типу файла
                if (in_array($_FILES['avatar']['type'], $allowedValues)) {

                    //вырезаем формат файла в строку
                    $str = strpos($_FILES['avatar']['type'], "/");
                    $format = "." . substr($_FILES['avatar']['type'], $str + 1);
                    //имя аватарки каждый раз уникальное
                    $file_name = time() . $_SESSION['user_id'] . $format;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'],
                        $uploadPath . $file_name)) {
                        $query = "UPDATE `users` 
                                  SET `file_name_avatar` = '$file_name' 
                                  WHERE `id` = '" . $_SESSION['user_id'] . "'";
                        mysqli_query(connectDB(), $query);
                    }
                } else {
                    $notice_upload = 'Не допустимый формат';
                }
            } else {
                $notice_upload = 'Превышен размер файла';
            }
        } else {
            $notice_upload = 'Произошла ошибка при загрузке';
        }
    } else {
        $notice_upload = 'Превышено максимальное количество файлов для загрузки';
    }
}

//удаление аватарки
if (isset($_POST['delete_ava'])) {
    $query = "SELECT `file_name_avatar` 
              FROM `users`
              WHERE `id` = " . $_SESSION['user_id'];
    $result = mysqli_query(connectDB(), $query);
    $row = mysqli_fetch_assoc($result);
    if ($row['file_name_avatar'] != null) {
        unlink($_SERVER['DOCUMENT_ROOT'] . '/avatars/' . $row['file_name_avatar']);
        $query = "UPDATE `users` 
                  SET `file_name_avatar` = NULL
                  WHERE `id` = " . $_SESSION['user_id'];
        mysqli_query(connectDB(), $query);
        header('Location: /settings.php');
    } else {
        $notice_delete = 'Ошибка при удалении';
    }
}

//обновляет список городов в зависимости от выбранной страны
if (isset($_POST['selected_country'])) {
    $country_id = sqlProtected($_POST['selected_country']);
    $cities = getCities($country_id);
    $query = "SELECT `city_id` 
              FROM `users` 
              WHERE `id` = " . $_SESSION['user_id'];
    $result = mysqli_query(connectDB(), $query);
    $row = mysqli_fetch_assoc($result);
    $myCity = $row['city_id'];
    require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/template_select_cities.php");
}

//проверка и сохранение изменений настроек пользователя
if (isset($_POST['save_change_settings'])) {

    $error_form = [];
    //валидация поля имя
    if (isset($_POST['name']) && !empty($_POST['name'])) {
        if (preg_match("/[^a-zа-я]/iu", $_POST['name'])) {
            $notice_name = "В имени должны быть только буквы  без пробелов";
            array_push($error_form, 'error_name');
        }
        $name = sqlProtected($_POST['name']);
    } else {
        $notice_name = "Не заполнено поле имя";
        $name = '';
        array_push($error_form, 'nothing_name');
    }
    //валидация поля фамилии
    if (isset($_POST['lastname']) && !empty($_POST['lastname'])) {
        if (preg_match("/[^a-zа-я]/iu", $_POST['lastname'])) {
            $notice_lastname = "В фамилии должны быть только буквы  без пробелов";
            array_push($error_form, 'error_lastname');
        }
        $lastname = sqlProtected($_POST['lastname']);
    } else {
        $notice_lastname = "Не заполнено поле фамилия";
        $lastname = '';
        array_push($error_form, 'nothing_lastname');
    }
    //валидация поля отчества
    if (isset($_POST['surname']) && !empty($_POST['surname'])) {
        if (preg_match("/[^a-zа-я]/iu", $_POST['surname'])) {
            $notice_surname = "В отчестве должны быть только буквы без пробелов";
            array_push($error_form, 'error_surname');
        }
        $surname = sqlProtected($_POST['surname']);
    } else {
        $notice_surname = "Не заполнено поле отчество";
        $surname = '';
        array_push($error_form, 'nothing_surname');
    }
    //валидация поля email
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $notice_email = "Не корректный email";
            array_push($error_form, 'error_email');
        }
        $email = sqlProtected($_POST['email']);
    } else {
        $notice_email = "Не заполнено поле email";
        $email = '';
        array_push($error_form, 'nothing_email');
    }
    //валидация поля телефон
    if (isset($_POST['phone']) && !empty($_POST['phone'])) {
        $phone = preg_replace("/[^0-9]{1,11}/", '', $_POST['phone']);
        if (strlen($phone) != 11) {
            $notice_phone = "В номере должны быть 11 цифр";
            array_push($error_form, 'error_phone');
        }

    } else {
        $notice_phone = "Не заполнено поле телефон";
        $phone = '';
        array_push($error_form, 'nothing_phone');
    }

    //валидация поля новый пароль
    if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
        if (preg_match("/[^a-z0-9]/iu", $_POST['new_password'])) {
            $notice_password = "В поле пароль должны быть только латинские буквы и цифры";
            array_push($error_form, 'error_password');
        } elseif (!preg_match("/^[a-z0-9\w]{4,10}$/iu", $_POST['new_password'])) {
            $notice_password = "В поле пароль должно быть от 4 до 10 символов";
            array_push($error_form, 'error_password');
        }
    }

    //формируем запрос для БД
    if (empty($error_form)) {
        $query = "UPDATE `users` SET `name` = '$name', `lastname` = '$lastname', `surname` = '$surname',
                  `phone` = '$phone', `email` = '$email'
                  ";
        if (isset($_POST['country']) && is_int($country_id = ($_POST['country']) * 1)) {
            $query .= ", `country_id` = '$country_id'";
        }
        if (isset($_POST['city']) && is_int($city_id = ($_POST['city']) * 1)) {
            $query .= ", `city_id` = '$city_id'";
        }
        if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
            $new_password = hash('SHA256', $_POST['new_password']);
            $query .= ", `password` = '$new_password'";
            $success_password = "Пароль успешно изменён";
        }
        if (isset($_POST['notice_email'])) {
            $query .= ", `email_notice` = '1'";
        } else {
            $query .= ", `email_notice` = '0'";
        }
        $query .= " WHERE `id` = '" . $_SESSION['user_id'] . "'";
        $result = mysqli_query(connectDB(), $query);
        //после удачного запроса удаляем переменные, что-бы отображались значения в инпутах из БД
        if ($result) {
            unset($name);
            unset($surname);
            unset($lastname);
            unset($email);
            unset($phone);
        }
    }

}
//загрузка доступных списков пользователя для админ панели
if (isset($_POST['admin_panel_selected_user'])) {
    $user_id = $_POST['admin_panel_selected_user'];
    $myLists = myLists($user_id);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/template_admin_panel_select_lists.php");
}

//сохранение изменения записи в админ панели
if (isset($_POST['admin_change_task'])) {
    $task_id = $_POST['task_id'];
    $list_id = $_POST['list_id'];
    $date = $_POST['date'];
    $text_task = sqlProtected($_POST['text_task']);
    $query = "UPDATE `tasks`
              SET `text` = '$text_task', `date` = '$date',
              `list_id` = '$list_id', `sort` = '0'
              WHERE `id` = '$task_id'";
    mysqli_query(connectDB(), $query);
}

//удаление записи из панели администратора
if (isset($_POST['admin_delete_task'])) {
    $task_id = $_POST['task_id'];
    $query = "DELETE FROM `tasks` WHERE `id` = '$task_id'";
    mysqli_query(connectDB(), $query);
}
