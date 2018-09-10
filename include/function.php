<?php
/**
 * @return bool|mysqli
 * Подключение к БД
 */
function connectDB()
{
    $nameDB = 'task_manager';
    $userName = 'root';
    $password = '';
    $host = 'test';
    $connect = mysqli_connect($host, $userName, $password, $nameDB);
    if (mysqli_connect_errno()) return false;
    return $connect;
}

/**
 * @param $var
 * @return string
 * Функция для экранирования специальных символов
 */
function sqlProtected($var)
{
    $connect = connectDB();
    $var = htmlspecialchars($var);
    $var = mysqli_real_escape_string($connect, $var);
    return $var;
}

/**
 * @return mixed
 * Возвращает количество списков(папок) пользователя
 */
function getNumMyLists($user_id)
{
    $connect = connectDB();
    $query = "SELECT COUNT(*) 
              FROM `lists` 
              WHERE `created_user_id` = '$user_id'";
    $result = mysqli_query($connect, $query);
    $numRow = mysqli_fetch_assoc($result);
    return $numRow['COUNT(*)'];
}

/**
 * @param $listId
 * @return mixed
 * Принимает id списка и возвращает число тасков в нём
 */
function getNumThisTasks($listId)
{
    $connect = connectDB();
    $query = "SELECT COUNT(*) 
              FROM `tasks` 
              WHERE `list_id` = '$listId'";
    $result = mysqli_query($connect, $query);
    $numTasks = mysqli_fetch_assoc($result);
    return $numTasks['COUNT(*)'];
}

/**
 * Выводит все списки пользователя
 */
function getMyLists($user_id)
{
    $connect = connectDB();
    $query = "SELECT `name`, `id` 
              FROM `lists` 
              WHERE `created_user_id` = '$user_id'";
    $result = mysqli_query($connect, $query);
    $list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($list, $row);
    }
    foreach ($list as $key => $val) {
        inviteList($val['id']) ? $inviteList = true : $inviteList = false;
        $numThisTask = getNumThisTasks($val['id']);
        isset($_SESSION['list_id']) && $val['id'] == $_SESSION['list_id'] ? $classList = 'active_list' : $classList = '';
        require($_SERVER['DOCUMENT_ROOT'] . '/app/template/blocks/my_lists.php');
    }
}

/**
 * @return array
 * функция возвращает все списки(папки) пользователя
 */
function myLists($user_id)
{
    $connect = connectDB();
    $query = "SELECT `name`, `id` 
              FROM `lists` 
              WHERE `created_user_id` = '$user_id'";
    $result = mysqli_query($connect, $query);
    $list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($list, $row);
    }
    return $list;
}

/**
 * @param $listId
 * Выводит все таски списка беря его id из сессии
 */
function getMyTasks($list_id)
{
    if ($list_id != false) {
        $connect = connectDB();
        $query = "SELECT `tasks`.`id` AS `id`, `tasks`.`text` AS `text`, 
                  `tasks`.`list_id` AS `list_id`, `colors`.`rgb` AS `rgb`  
                  FROM `tasks`
                  LEFT JOIN `lists`
                  ON `lists`.`id` = `tasks`.`list_id`
                  LEFT JOIN `colors`
                  ON `tasks`.`color_id` = `colors`.`id`
                  WHERE `list_id` = '$list_id'
                  ORDER BY `sort` ASC";
        $result = mysqli_query($connect, $query);
        $tasks = [];
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($tasks, $row);
        }
        $user_id = getUserId();
        $arrMylists = myLists($user_id);
        $isInviteList = isInviteList($user_id, $list_id);
        require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/blocks/my_tasks.php');
    }
}

/**
 * Выводит все коментарии выбранного таска
 */
function getMyComments($task_id)
{
    if ($task_id != false) {
        $connect = connectDB();
        $query = "SELECT `comments`.`id` AS `id`, `comments`.`text` AS `text`, DATE_FORMAT(`comments`.`date`, '%e.%c.%y') AS `date`, 
                  `users`.`name` AS `name`, `users`.`lastname` AS `lastname`, `users`.`surname` AS `surname`, 
                  DATE_FORMAT(`comments`.`date`, '%H:%i') AS `time`, `comments`.`create_user_id` AS `create_user_id`
                  FROM `comments`
                  LEFT JOIN `tasks`
                  ON `comments`.`task_id` = `tasks`.`id`
                  LEFT JOIN `lists`
                  ON `tasks`.`list_id` = `lists`.`id`
                  LEFT JOIN `users`
                  ON `comments`.`create_user_id` = `users`.`id`
                  WHERE `comments`.`task_id` = '$task_id'
                  ORDER BY `comments`.`date` ASC";
        $result = mysqli_query($connect, $query);
        $comments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($comments, $row);
        }
        foreach ($comments as $key => $val) {
            $pathAvatar = getUserAvatar($val['create_user_id']);
            require($_SERVER['DOCUMENT_ROOT'] . '/app/template/blocks/my_comments.php');
        }
    }
}

/**
 * @param $str
 * функция добавляет коментарий в БД
 */
function addComment($str, $task_id, $user_id)
{
    $str = sqlProtected($str);
    $date = date('Y-m-d H:i:s');
    if (!empty($str) && $task_id != false) {
        $query = "INSERT INTO comments(`text`, `date`, `task_id`, `create_user_id`) 
                  VALUES('$str', '$date', '$task_id', '$user_id')";
        mysqli_query(connectDB(), $query);
    }
}

/**
 * @return array
 * Функция возвращает массив со всеми пользователями
 */
function getUsers()
{
    $connect = connectDB();
    $query = "SELECT `name`, `lastname`, `surname`, `id`  
              FROM `users`";
    $result = mysqli_query($connect, $query);
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($users, $row);
    }
    return $users;
}

/**
 * @return array
 * Функция возвращает массив с именем, фамилией, отчеством пользователя
 */
function getUserName($user_id)
{
    $connect = connectDB();
    if ($user_id != false) {
        $query = "SELECT `users`.`name`, `users`.`lastname`, `users`.`surname`, `users`.`email`, `users`.`phone`,
                  `country_id`, `city_id`, `users`.`email_notice`,
                  DATE_FORMAT(`users`.`date_registration`, '%e.%c.%y') AS `date`
                  FROM `users`
                  WHERE `users`.`id` = '$user_id'";
        $result = mysqli_query($connect, $query);
        $user = mysqli_fetch_assoc($result);
        return $user;
    }
}

/**
 * @param $user_id
 * @return array|null
 * Функция возвращает массив с именем и описанием групп в которых состит пльзватеь
 */
function getUserGroups($user_id)
{
    $connect = connectDB();
    $query = "SELECT `groups`.`name`, `groups`.`description` 
              FROM `groups`
              LEFT JOIN `users_groups`
              ON `groups`.`id` = `users_groups`.`group_id`
              LEFT JOIN `users`
              ON `users_groups`.`user_id` = `users`.`id`
              WHERE `users`.`id` = '$user_id'";
    $result = mysqli_query($connect, $query);
    $user_group = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($user_group, $row);
    }
    return $user_group;
}

/**
 * @param $list_id
 * @return array
 * Принимает id выбранного списка(папки) и возвращает массив с пользователями
 * которым дуступен этот список
 */
function getArrSharedList($list_id)
{
    $connect = connectDB();
    if ($list_id != false) {
        $query = "SELECT `to_user_id` 
                  FROM `shared_lists` 
                  WHERE `list_id` = '$list_id'";
        $result = mysqli_query($connect, $query);
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($users, $row);
        }
        return $users;
    }
}

/**
 * @param $arr
 * @param $user_id
 * @return bool
 *указывает какие пользователи приглашены в список пользователя
 */
function checkInviteUsers($arr, $user_id)
{
    foreach ($arr as $key => $val) {
        if ($val['to_user_id'] == $user_id) return true;
    }
    return false;
}

/**
 * выводит модальное окно
 */
function getModal($notice = null)
{
    $arrMyLists = myLists(getUserId());
    $arrUsers = getUsers();
    $arrSharedList = getArrSharedList(currentListId());
    //проверяем приглашён ли пользователь
    foreach ($arrUsers as $key => $val) {
        checkInviteUsers($arrSharedList, $val['id']) ? $arrUsers[$key]['checked'] = 'checked' : $arrUsers[$key]['checked'] = '';
    }
    foreach ($arrMyLists as $key => $val) {
        if ($val['id'] == $_SESSION['list_id']) {
            $nameList = $val['name'];
        }
    }
    require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/modal.php');
}

/**
 * @param $listId id списка
 * @return bool
 * Возвращает true или false в зависимости доступен этот список для просмотра другим пользователям
 */
function inviteList($listId)
{
    $connect = connectDB();
    $query = "SELECT COUNT(*) 
              FROM `shared_lists` 
              WHERE `list_id` = '$listId'";
    $result = mysqli_query($connect, $query);
    $count = mysqli_fetch_assoc($result);
    $count['COUNT(*)'] > 0 ? $res = true : $res = false;
    return $res;
}

/**
 * возвращает список со списками в которые пригласили пользователя
 */
function getInviteLists($user_id)
{
    $connect = connectDB();
    $query = "SELECT `lists`.`name` AS `name`, `lists`.`id` AS `id`
              FROM `lists`
              LEFT JOIN `shared_lists`
              ON `shared_lists`.`list_id` = `lists`.`id`
              WHERE `shared_lists`.`to_user_id` = '$user_id'";
    $result = mysqli_query($connect, $query);
    $inviteList = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($inviteList, $row);
    }
    foreach ($inviteList as $key => $val) {
        $numThisTask = getNumThisTasks($val['id']);
        isset($_SESSION['list_id']) && $val['id'] == $_SESSION['list_id'] ? $classList = 'active_list' : $classList = '';
        require($_SERVER['DOCUMENT_ROOT'] . '/app/template/blocks/invite_lists.php');
    }
}

/**
 * @return mixed
 * возвращает количество списков в которые пригласили пользователя
 */
function getNumInviteList($user_id)
{
    $connect = connectDB();
    $query = "SELECT COUNT(*) 
              FROM `shared_lists` 
              WHERE `to_user_id` = '$user_id'";
    $result = mysqli_query($connect, $query);
    $numRow = mysqli_fetch_assoc($result);
    return $numRow['COUNT(*)'];
}

/**
 * @return bool
 * Возвращает true или false в зависимости от того состоит этот список в приглашённых
 */
function isInviteList($user_id, $list_id)
{
    $connect = connectDB();
    if ($list_id != false) {
        $query = "SELECT COUNT(*) FROM `shared_lists` 
                  WHERE `list_id` = '$list_id'
                  AND `shared_user_id` != '$user_id'";
        $result = mysqli_query($connect, $query);
        $count = mysqli_fetch_assoc($result);
        $count['COUNT(*)'] > 0 ? $res = true : $res = false;
        return $res;
    }
}

/**
 * @return mixed
 * функция возвращает строку с путём до аватарки пользователя
 */
function getUserAvatar($userId)
{
    $connect = connectDB();
    $query = "SELECT `users`.`file_name_avatar` AS `file_name`
              FROM `users`
              WHERE `id` = '$userId'";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    if ($row['file_name'] == null) {
        return "/avatars/default_photo.png";
    }
    return "/avatars/" . $row['file_name'];
}

/**
 * @return array
 * функция  возвращает вассив со всеми доступными цветами окрашивания тасков
 */
function getColorsToTask()
{
    $connect = connectDB();
    $query = "SELECT *
              FROM `colors`";
    $colors = [];
    $result = mysqli_query($connect, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($colors, $row);
    }
    return $colors;
}

/**
 * @return array
 * функция возвращает массив со странами
 */
function getCountries()
{
    $connect = connectDB();
    $query = "SELECT * 
              FROM `countries`";
    $result = mysqli_query($connect, $query);
    $countries = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($countries, $row);
    }
    return $countries;
}

/**
 * @param $country_id
 * @return array
 * Принимает id страны и возвращает массив с городами этой страны
 */
function getCities($country_id)
{
    $connect = connectDB();
    $query = "SELECT * 
              FROM `cities` 
              WHERE `parent_country_id` = '$country_id'";
    $result = mysqli_query($connect, $query);
    $cities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($cities, $row);
    }
    return $cities;
}

/**
 * @param $list_id
 * @param $message
 * функция осуществляет рассылку уведомлений на почту приглашённых пользователей у которых включены уведомления
 *
 */
function sendEmailNotice($list_id, $message)
{
    $connect = connectDB();
    $query = "SELECT `users`.`email`
              FROM `shared_lists`
              LEFT JOIN `lists`
              ON `lists`.`id` = `shared_lists`.`list_id`
              LEFT JOIN `users`
              ON `users`.`id` = `shared_lists`.`to_user_id`
              WHERE `lists`.`id` = '$list_id'
              AND `users`.`email_notice` = '1'";
    $users = [];
    $mail_to = '';

    $result = mysqli_query($connect, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($users, $row);
    }
    $headers = "Content-type: text/plain; charset=utf-8 \r\n";
    $headers .= "From: Task.manager <svarog.2580@ya.ru>\r\n";
    $headers .= "Bcc: Task.manager svarog.2580@ya.ru\r\n";
    foreach ($users as $key => $val) {
        $mail_to .= $val['email'] . ", ";
    }
    if (!empty($mail_to)) {
        $mail_to = substr($mail_to, 0, -2);
        mail($mail_to, 'Task.manager', $message, $headers);
    }
    mysqli_free_result($result);
}

/**
 * @param $list_id
 * @param $message
 * функция предназначена для отправки уведомлений на почту.Осуществляется проверка где оставляется коментарий, если
 * в своём списке, то уведомления рассылаются всем приглашённым у кого стоит согласие на рассылку, а если
 * в приглашённом списке, то рассылка идёт автору если есть согласие и остальным пользователям которых пригласил
 * автор, кроме пользователя который оставил коментарий.
 */
function sendEmailNoticeComment($list_id, $message)
{
    $connect = connectDB();
    $mails = '';
    $user_id = getUserId();
    $users = [];
    $is_invite_list = isInviteList($user_id, $list_id);

    //выборка всех пользователей приглашёных для просмотра списка и одобривших email рассылку
    $query = "SELECT `shared_lists`.`to_user_id`, `users`.`email`, `shared_lists`.`shared_user_id`
              FROM `shared_lists`
              LEFT JOIN `lists`
              ON `lists`.`id` = `shared_lists`.`list_id`
              LEFT JOIN `users`
              ON `users`.`id` = `shared_lists`.`to_user_id`
              WHERE `lists`.`id` = '$list_id'
              AND `users`.`email_notice` = '1'";
    //все пользователи кроме отправителя
    if ($is_invite_list) $query .= " AND `users`.`id` NOT IN ('$user_id')";

    $result = mysqli_query($connect, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($users, $row);
    }

    //если приглашённый список, то отправляем уведомление автору списка, если стоит согласие на уведомление
    if ($is_invite_list) {
        $query = "SELECT `users`.`email`
                  FROM `users`
                  LEFT JOIN `lists`
                  ON `users`.`id` = `lists`.`created_user_id`
                  LEFT JOIN `shared_lists`
                  ON `shared_lists`.`shared_user_id` = `users`.`id`
                  WHERE `lists`.`id` = '$list_id'
                  AND `users`.`email_notice` = '1'";
        $result = mysqli_query($connect, $query);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            array_push($users, $row);
        }
    }
    foreach ($users as $key => $val) {
        $mails .= $val['email'] . ', ';
    }
    substr($mails, 0, -2);
    mail($mails, 'Task.manager', $message);
    mysqli_free_result($result);
}

/**
 * @return bool
 * Проверяет состоит ли пользователь в группе администратор
 */
function isAdmin($user_id)
{
    $connect = connectDB();
    $query = "SELECT COUNT(*)
              FROM `users`
              LEFT JOIN `users_groups`
              ON `users_groups`.`user_id` = `users`.`id`
              WHERE `users_groups`.`group_id` = '1'
              AND `users_groups`.`user_id` = '$user_id'";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    if ($row['COUNT(*)'] > 0) return true;
    return false;
}

/**
 * выводит все списки всех пользователей со всеми тасками
 */
function getAdminPanel()
{
    $connect = connectDB();
    $query = "SELECT `lists`.`name` AS `list_name`, `lists`.`id` AS `list_id`, `users`.`name`, `users`.`lastname`, 
              `users`.`surname`, `tasks`.`text` AS `task_text`, DATE_FORMAT(`tasks`.`date`, '%Y-%m-%d') AS `task_date`, `tasks`.`id` AS `task_id`,
              `users`.`id` AS `user_id` 
              FROM `lists`
              LEFT JOIN `tasks`
              ON `lists`.`id` = `tasks`.`list_id`
              LEFT JOIN `users`
              ON `users`.`id` = `lists`.`created_user_id`
              ORDER BY `tasks`.`id` ASC";
    $result = mysqli_query($connect, $query);
    $admin_lists = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($admin_lists, $row);
    }
    $allUsers = getAllUsers();
    $allLists = getAllLists();
    require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/admin_panel/admin_panel.php');
}

/**
 * @return array
 * Возвращает массив со всеми списками(папками)
 */
function getAllLists()
{
    $connect = connectDB();
    $query = "SELECT `name`, `id`
              FROM `lists`";
    $result = mysqli_query($connect, $query);
    $allLists = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($allLists, $row);
    }
    return $allLists;
}

/**
 * @return array
 * возвращает массив со всеми пользователями
 */
function getAllUsers()
{
    $connect = connectDB();
    $query = "SELECT `id`, `name`, `lastname`, `surname`
              FROM `users`";
    $result = mysqli_query($connect, $query);
    $allUsers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($allUsers, $row);
    }
    return $allUsers;
}

/**
 * Функция выводит панель навигации
 */
function getNavBar()
{
    $user_id = getUserId();
    $user = getUserName($user_id);
    $isAdmin = isAdmin($user_id);
    $_SERVER['REQUEST_URI'] == '/admin_panel.php' ? $nameLink = 'Назад' : $nameLink = 'Панель администратора';
    $_SERVER['REQUEST_URI'] == '/admin_panel.php' ? $pathLink = '/' : $pathLink = '/admin_panel.php';

    $_SERVER['REQUEST_URI'] == '/settings.php' ? $nameSettingLink = 'fa-chevron-left' : $nameSettingLink = 'fa-cog';
    $_SERVER['REQUEST_URI'] == '/settings.php' ? $pathSettingLink = '/' : $pathSettingLink = '/settings.php';

    require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/navigation.php');
}

/**
 * выводит информацию о пользователе и доступные цвета для раскрашивания тасков
 */
function getUserInfo()
{
    $user_id = getUserId();
    $user = getUserName($user_id);
    $task_colors = getColorsToTask();
    $pathAvatar = getUserAvatar($user_id);
    $isInviteList = isInviteList($user_id, currentListId());
    require_once($_SERVER['DOCUMENT_ROOT'] . '/blocks/user_info.php');
}

/**
 * @return mixed
 * Возвращает id nользователя
 */
function getUserId()
{
    return $_SESSION['user_id'];
}

/**
 * @return bool
 * возвращает id активуного списка либо false
 */
function currentListId()
{
    if (isset($_SESSION['list_id'])) return $_SESSION['list_id'];
    return false;
}

/**
 * @return bool
 * возвращает id активуного таска либо false
 */
function currentTaskId()
{
    if (isset($_SESSION['task_id'])) return $_SESSION['task_id'];
    return false;
}

/**
 * @param $file
 * @return bool|string
 * функция проверят массив по размеру, формату, количеству в нём фаилов
 */
function validateAvatar($file)
{
    //максимальный размер файла 1Mb
    $maxSizeFile = 1048576;
    //количество файлов
    $maxUploadFiles = 1;
    //разрешённые форматы
    $allowedValues = ["image/jpeg", "image/pjpeg", "image/png"];
    $count = 0;
    foreach ($file as $key) {
        $count++;
    }
    if ($file['error'] == 4) {
        return $error = 'Фотография не выбрана';
    } elseif ($count == $maxUploadFiles) {
        return $error = 'Превышено количество файлов';
    } elseif ($file['size'] > $maxSizeFile) {
        return $error = 'Превышен допустимый размер файла';
    } elseif (!in_array($file['type'], $allowedValues)) {
        return $error = 'Не допустимый формат файла';
    } elseif (empty($file)) {
        return $error = 'Фотография не выбрана';
    } elseif (!empty($file['error'])) {
        return $error = 'Произошла ошибка при загрузке';
    }
    return true;
}

/**
 * @param $user_id
 * перемещает аватарку из временного хранилища в папку avatars
 */
function uploadAvatar($user_id)
{
    $uploadPath = $_SERVER['DOCUMENT_ROOT'] . "/avatars/";
    //вырезаем формат файла в строку
    $str = strpos($_FILES['avatar']['type'], "/");
    $format = "." . substr($_FILES['avatar']['type'], $str + 1);
    //имя аватарки каждый раз уникальное
    $file_name = time() . $user_id . $format;
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath . $file_name)) {
        $query = "UPDATE `users` 
                  SET `file_name_avatar` = '$file_name' 
                  WHERE `id` = '$user_id'";
        mysqli_query(connectDB(), $query);
    }
}

/**
 * @param $user_id
 * @return bool|string
 * удаляет и обновляет информацию в БД об аватарке пользователя
 */
function deleteAvatar($user_id)
{
    $query = "SELECT `file_name_avatar` 
              FROM `users`
              WHERE `id` = '$user_id'";
    $result = mysqli_query(connectDB(), $query);
    $row = mysqli_fetch_assoc($result);
    if ($row['file_name_avatar'] != null) {

        if (unlink($_SERVER['DOCUMENT_ROOT'] . '/avatars/' . $row['file_name_avatar'])) {
            $query = "UPDATE `users` 
                      SET `file_name_avatar` = NULL
                      WHERE `id` = '$user_id'";
            mysqli_query(connectDB(), $query);
            return true;
        }
        return $error = 'Ошибка при удалении.';
    }
    return $error = 'У Вас нет фото, что бы удалить его.';
}

/**
 * @param $password
 * @return bool|string
 * валидация пароля
 */
function validatePassword($password)
{
    if (empty($password)) return $error = 'Не заполнено поле Password.';
    if (preg_match("/[^a-z0-9]/iu", $password)) {
        return $error = 'Не допустимые значения в поле Password.';
    } elseif (!preg_match("/^[a-z0-9\w]{4,10}$/iu", $password)) {
        return $error = 'В поле Password должно быть от 4 до 10 символов';
    }
    return true;
}

/**
 * @param $email
 * @return bool|string
 * валидация поля email при регистрации
 */
function validateRegEmail($email)
{
    if (empty($email)) return $error = 'Поле E-mail не заполнено.';
    $query = "SELECT `email`
              FROM `users`
              WHERE `email` = '$email'";
    $result = mysqli_query(connectDB(), $query);
    if (mysqli_num_rows($result) > 0) {
        return $error = 'Пользователь с таким E-mail уже зарегистрирован.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $error = 'Не допустимые значения в поле E-mail.';
    }
    return true;
}

/**
 * @param $str
 * @param $name
 * @return bool|string
 * валидация строки для полей имени формы
 */
function validateStr($str, $name)
{
    if (empty($str)) return $error = "Поле $name не заплнено.";
    if (preg_match("/[^a-zа-я]/iu", $str)) return $error = "Не допустимые значения в поле $name.";
    return true;
}

/**
 * @param $name
 * @param $email
 * @param $password
 * @return bool|string
 * регистрирует пользователя на сайте
 */
function registration($name, $email, $password)
{
    $date = date("Y-m-d H:h:s", time());
    $password = hash('sha256', $password);
    $query = "INSERT INTO `users`(`name`, `email`, `password`, `date_registration`) 
                  VALUES('$name', '$email', '$password', '$date')";
    if (!mysqli_query(connectDB(), $query)) return $error = 'Произошла ошибка при регистрации';
    $query = "INSERT INTO `users_groups`(`user_id`, `group_id`)
              VALUES((SELECT `id` FROM `users` WHERE `email` = '$email'), '2')";
    if (!mysqli_query(connectDB(), $query)) return $error = 'Произошла ошибка при регистрации';
    return true;
}

/**
 * @param $email
 * @param $password
 * @return bool|string
 * функция производит авторизацию
 */
function authorization($email, $password)
{
    if (empty($email) && empty($password)) return $error = 'Вы ничего не ввели';
    $query = "SELECT `id`, `email`, `password` 
              FROM users 
              WHERE email = '$email'";
    $result = mysqli_query(connectDB(), $query);
    if (mysqli_num_rows($result) > 1) return $error = 'Ошибка email';
    $row = mysqli_fetch_assoc($result);
    //если есть такой email то сравниваем хеш вводимого пароля с хешем из БД
    if ($row['password'] !== hash('sha256', $password)) return $error = 'Не верно введён логин или пароль';
    return true;
}

/**
 * @param $email
 * @return mixed
 * возвращает id пользователя с указанным email
 */
function setUserId($email)
{
    $query = "SELECT `id`
              FROM `users` 
              WHERE `email` = '$email'";
    $result = mysqli_query(connectDB(), $query);
    $row = mysqli_fetch_assoc($result);
    return $row['id'];
}

/**
 * @return bool
 * показывает открыт или закрыт выпадающий список "мои папки"
 */
function myListStatus()
{
    isset($_SESSION['my_list']) && $_SESSION['my_list'] == 'open' ? $openMyList = true : $openMyList = false;
    return $openMyList;
}

/**
 * @return bool
 * показывает открыт или закрыт выпадающий список "участвую в списках"
 */
function inviteListStatus()
{
    isset($_SESSION['invite_list']) && $_SESSION['invite_list'] == 'open' ? $openInviteList = true : $openInviteList = false;
    return $openInviteList;
}

/**
 * @param $str
 * выводит шаблон уведомления об ошибке
 */
function getSettingsNotice($str)
{
    require($_SERVER['DOCUMENT_ROOT'] . "/app/template/settings/notice.php");
}