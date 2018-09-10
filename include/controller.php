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
    $result = authorization($email_auth, $password_auth);
    if ($result === true) {
        //проверка была ли запущенна сессия ранее
        if (!isset($_SESSION['active'])) session_start();
        setcookie(session_name(), session_id(), time() + 60 * 20, '/');
        setcookie('login', $email_auth, time() + 60 * 60 * 24 * 30, '/');
        $_SESSION['user_id'] = setUserId($email_auth);
        $_SESSION['active'] = 1;
        $_SESSION['login'] = $email_auth;
        $email_auth = '';
        $password_auth = '';
        $error_auth = '';
        unset($error_auth);
        header('Location: /');
        exit();
    } else {
        $error_auth = $result;
    }
}

//смена логина
if (isset($_POST['change_login']) && isset($_COOKIE['login'])) {
    setcookie('login', '', 3, '/');
    header("Location: /authorization.php");
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
    $result = validateRegEmail($email_reg);
    if ($result !== true) $reg_error_email = $result;

    //валидация имени
    $result = validateStr($name_reg, 'Name');
    if ($result !== true) $reg_error_name = $result;

    //валидация пароля
    $result = validatePassword($password_reg);
    if ($result !== true) $reg_error_password = $result;

    if (!isset($reg_error_password) && !isset($reg_error_name) && !isset($reg_error_email)) {
        $result = registration($name_reg, $email_reg, $password_reg);
        if ($result === true) {
            setcookie('login', $email_reg, time() + 60 * 60 * 24 * 30, '/');
            header('Location: /authorization.php');
        }
        $reg_error = $result;
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
    $user_info = getUserName(getUserId());
    if (!empty($_POST['name_list'])) {
        $newList = sqlProtected($_POST['name_list']);
        $query = "INSERT INTO lists(`name`, `created_user_id`) 
                  VALUES('$newList', '" . $_SESSION['user_id'] . "')";
        mysqli_query(connectDB(), $query);
    }
    $userId = getUserId();
    $isInviteList = isInviteList($userId, currentListId());
    $countMyList = getNumMyLists($userId);
    $countInviteList = getNumInviteList($userId);
    $myListStatus = myListStatus();
    $inviteListStatus = inviteListStatus();
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/lists.php");
}

//Добавление нового таска
if (isset($_POST['add_new_task'])) {
    if (!empty($_POST['text_task'])) {
        $date = date("Y-m-d", time());
        $user_info = getUserName(getUserId());
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
    $user_info = getUserName(getUserId());
    $taskId = sqlProtected($_POST['task_id']);
    $taskId = preg_replace("/[^0-9]/", '', $taskId);

    //текст удоляемого таска
    $query = "SELECT `tasks`.`text` AS `text`, `lists`.`name` AS `name`
              FROM `tasks`
              LEFT JOIN `lists`
              ON `tasks`.`list_id` = `lists`.`id`
              WHERE `tasks`.`id` = '$taskId'";
    $result = mysqli_query(connectDB(), $query);
    $text_info = mysqli_fetch_assoc($result);

    //удаляем таск
    $query = "DELETE FROM `tasks`
              WHERE `id` = '$taskId'";
    mysqli_query(connectDB(), $query);
    if ($_SESSION['task_id'] == $taskId) {
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
    $user_info = getUserName(getUserId());
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
    $userId = getUserId();
    $isInviteList = isInviteList($userId, currentListId());
    $countMyList = getNumMyLists($userId);
    $countInviteList = getNumInviteList($userId);
    $myListStatus = myListStatus();
    $inviteListStatus = inviteListStatus();
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/lists.php");
}

//обновление списка с тасками
if (isset($_POST['update_tasks'])) {
    require($_SERVER['DOCUMENT_ROOT'] . "/blocks/tasks.php");
}

//обновление списка со списками
if (isset($_POST['update_lists'])) {
    $userId = getUserId();
    $isInviteList = isInviteList($userId, currentListId());
    $countMyList = getNumMyLists($userId);
    $countInviteList = getNumInviteList($userId);
    $myListStatus = myListStatus();
    $inviteListStatus = inviteListStatus();
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/lists.php");
}

//изменение таска
if (isset($_POST['change_task_id'])) {
    $user_info = getUserName(getUserId());
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
    $user_name = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'];

    if ($row['list_id'] != $move_to_folder_id) {
        $_SESSION['task_id'] = false;

        $row['text'] == $modified_text_task ?
            $message .= $user_name . ' переместил(а) запись "' . $modified_text_task . '" в папку "' . $row['name'] . '".' :
            $message .= $user_name . ' изменил(а) запись "' . $row['text'] . '" на "' . $modified_text_task .
                '" и переместил(а) в папку "' . $row['name'] . '".';

        sendEmailNotice($_SESSION['list_id'], $message);
    } else {
        $message .= $user_name . ' изменил(а) запись"' . $row['text'] . '" на "' . $modified_text_task . '" в папке "' . $row['name'] . '".';
        sendEmailNotice($_SESSION['list_id'], $message);
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
    $user_id = getUserId();
    $task_id = currentTaskId();
    $list_id = currentListId();
    $user_info = getUserName($user_id);

    //добавляем коментарий
    addComment($_POST['comment_text'], $task_id, $user_id);

    $query = "SELECT `tasks`.`text` AS `task_text`, `tasks`.`id` AS `task_id`, `lists`.`name` AS `list_name`  
              FROM `tasks`
              LEFT JOIN `lists` 
              ON `tasks`.`list_id` = `lists`.`id`
              WHERE `tasks`.`id` = '$task_id'";
    $result = mysqli_query(connectDB(), $query);
    $row = mysqli_fetch_assoc($result);

    $commentText = sqlProtected($_POST['comment_text']);
    $nameList = $row['list_name'];
    $textTask = $row['task_text'];
    $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
        ' оставил(а) коментарий "' . $commentText . '" к записи "' . $textTask . '" находящейся в папке "' . $nameList . '".';

    sendEmailNoticeComment($list_id, $message);
    getMyComments($task_id);
}

//выводит коментарии к таску
if (isset($_POST['comments_to_task_id'])) {
    $_SESSION['task_id'] = sqlProtected($_POST['comments_to_task_id']);
    getMyComments($_SESSION['task_id']);
}

//выводит модальное окно с настройками списка(папки)
if (isset($_POST['setting_list'])) {
    $_SESSION['list_id'] = sqlProtected($_POST['list_id']);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/modal.php");
}

//настройки списка(папки)
if (isset($_POST['change_list'])) {
    $notice = [];
    $user_info = getUserName(getUserId());
    //количество пользователей которым можно просматривать список
    is_array($_POST['arrCheckedUsers']) ? $countShared = count($_POST['arrCheckedUsers']) : $countShared = 0;
    //массив с id пользователей которым разрешено просматривать список
    $arrUsersSharedId = $_POST['arrCheckedUsers'];
    //имя списка
    $newNameList = sqlProtected($_POST['name_list']);
    //максимальное количество пользователей просматривания списка 5
    if ($countShared > 5) {
        //в случае если их больше 5 пользователей то объявляем переменную с ошибкой
        $notice['text'] = "Вы выбрали <b>$countShared</b> пользователей, лимит 5";
        $notice['color'] = "#F34236";
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
                      AND `to_user_id` 
                      NOT IN (";
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
        $notice['text'] = "Изменения сохранены";
        $notice['color'] = "#4BAE4F";
    }
    getModal($notice);
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
    $user_info = getUserName($_SESSION['user_id']);
    $color_id = sqlProtected($_POST['change_color_task']);

    //обновляем цвет таска
    $query = "UPDATE `tasks` 
              SET `color_id` = '$color_id' 
              WHERE `id` = '" . $_SESSION['task_id'] . "'";
    mysqli_query(connectDB(), $query);

    //имя списка в котором находится таск
    $query = "SELECT `lists`.`name` AS `list_name`, `tasks`.`text`, `colors`.`name` AS `color_name`
              FROM `lists` 
              LEFT JOIN `tasks`
              ON `lists`.`id` = `tasks`.`list_id`
              LEFT JOIN `colors`
              ON `tasks`.`color_id` = `colors`.`id`
              WHERE `lists`.`id` = '" . $_SESSION['list_id'] . "'
              AND `tasks`.`id` = '" . $_SESSION['task_id'] . "'
              AND `colors`.`id` = '$color_id'";
    $result = mysqli_query(connectDB(), $query);
    $row = mysqli_fetch_assoc($result);

    $nameList = $row['list_name'];
    $textTask = $row['text'];
    $nameColor = $row['color_name'];
    //сообщение
    $message = $user_info['lastname'] . " " . $user_info['name'] . " " . $user_info['surname'] .
        " изменил(а) цвет записи '$textTask' на '$nameColor' в папке '$nameList'.";
    sendEmailNotice($_SESSION['list_id'], $message);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/blocks/tasks.php");
}

//модальное окно с информацией о пользователе который оставил этот коментарий
if (isset($_POST['show_user_info'])) {
    $user_id = sqlProtected($_POST['user_id_info']);
    $user_info = getUserName($user_id);
    $user_groups = getUserGroups($user_id);
    $pathAva = getUserAvatar($user_id);
    require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/blocks/user_info.php");
}

//Загрузка аватарки
if (isset($_POST['upload_ava'])) {
    $user_id = getUserId();
    $result = validateAvatar($_FILES['avatar']);
    if ($result !== true) {
        $notice_upload = $result;
    } else {
        uploadAvatar($user_id);
    }
}

//удаление аватарки
if (isset($_POST['delete_ava'])) {
    $user_id = getUserId();
    $result = deleteAvatar($user_id);
    if ($result === true) header('Location: /settings.php');
    $notice_delete = $result;
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
    require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/settings/select_cities.php");
}

//проверка и сохранение изменений настроек пользователя
if (isset($_POST['save_change_settings'])) {
    $error_form = [];
    //валидация поля имя
    if (isset($_POST['name'])) {
        $result = validateStr($_POST['name'], 'Имя');
        if ($result !== true) {
            array_push($error_form, 'error_name');
            $notice_name = $result;
        }
        $name = sqlProtected($_POST['name']);
    }
    //валидация поля фамилия
    if (isset($_POST['lastname'])) {
        $result = validateStr($_POST['lastname'], 'Фамилия');
        if ($result !== true) {
            array_push($error_form, 'error_lastname');
            $notice_lastname = $result;
        }
        $lastname = sqlProtected($_POST['lastname']);
    }
    //валидация поля отчества
    if (isset($_POST['surname'])) {
        $result = validateStr($_POST['surname'], 'Отчество');
        if ($result !== true) {
            array_push($error_form, 'error_surname');
            $notice_surname = $result;
        }
        $surname = sqlProtected($_POST['surname']);
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
        $result = validatePassword($_POST['new_password']);
        if ($result !== true) {
            $notice_password = $result;
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
    require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/admin_panel/select_lists.php");
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
