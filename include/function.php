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
function getNumMyLists()
{
    $connect = connectDB();
    $query = "SELECT COUNT(*) 
              FROM `lists` 
              WHERE `created_user_id` = " . $_SESSION['user_id'];
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
function getMyLists()
{
    $connect = connectDB();
    $query = "SELECT `name`, `id` 
              FROM `lists` 
              WHERE `created_user_id` = " . $_SESSION['user_id'];
    $result = mysqli_query($connect, $query);
    $list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($list, $row);
    }
    require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/template_my_lists.php');
}

/**
 * @return array
 * функция возвращает все списки(папки) пользователя
 */
function myLists($user_id = null)
{
    $connect = connectDB();
    if (isset($user_id)) {
        $query = "SELECT `name`, `id` 
                  FROM `lists` 
                  WHERE `created_user_id` = '$user_id'";
    } else {
        $query = "SELECT `name`, `id` 
                  FROM `lists` 
                  WHERE `created_user_id` = " . $_SESSION['user_id'];
    }
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
function getMyTasks()
{
    if (isset($_SESSION['list_id'])) {
        $connect = connectDB();
        $query = "SELECT `tasks`.`id` AS `id`, `tasks`.`text` AS `text`, 
                  `tasks`.`list_id` AS `list_id`, `colors`.`rgb` AS `rgb`  
                  FROM `tasks`
                  LEFT JOIN `lists`
                  ON `lists`.`id` = `tasks`.`list_id`
                  LEFT JOIN `colors`
                  ON `tasks`.`color_id` = `colors`.`id`
                  WHERE `list_id` = '" . $_SESSION['list_id'] . "'
                  ORDER BY `sort` ASC";
        $result = mysqli_query($connect, $query);
        $tasks = [];
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($tasks, $row);
        }
        $arrMylists = myLists();
        $isInviteList = isInviteList();
        require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/template_my_tasks.php');
    }
}

/**
 * Выводит все коментарии выбранного таска
 */
function getMyComments()
{
    if (isset($_SESSION['task_id'])) {
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
                  WHERE `comments`.`task_id` = '" . $_SESSION['task_id'] . "'
                  ORDER BY `comments`.`date` ASC";
        $result = mysqli_query($connect, $query);
        $comments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($comments, $row);
        }
        require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/template_my_comments.php');
    }
}

/**
 * @param $str
 * функция добавляет коментарий в БД
 */
function addComment($str)
{
    $str = sqlProtected($str);
    $date = date('Y-m-d H:i:s');
    if (!empty($str)) {
        $query = "INSERT INTO comments(`text`, `date`, `task_id`, `create_user_id`) 
                  VALUES('$str', '$date', '" . $_SESSION['task_id'] . "', '" . $_SESSION['user_id'] . "')";
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
function getUserInfo($user_id = null)
{
    $connect = connectDB();
    if (!empty($user_id)) {
        $query = "SELECT `users`.`name`, `users`.`lastname`, `users`.`surname`, `users`.`email`, `users`.`phone`,
                  `country_id`, `city_id`, `users`.`email_notice`,
                  DATE_FORMAT(`users`.`date_registration`, '%e.%c.%y') AS `date`
                  FROM `users`
                  WHERE `users`.`id` = '$user_id'";
    } else {
        $query = "SELECT `users`.`name`, `users`.`lastname`, `users`.`surname`, `users`.`email`, `users`.`phone`, 
                  `country_id`, `city_id`, `users`.`email_notice`,
                  DATE_FORMAT(`users`.`date_registration`, '%e.%c.%y') AS `date`
                  FROM `users`
                  WHERE `users`.`id` = '" . $_SESSION['user_id'] . "'";
    }

    $result = mysqli_query($connect, $query);
    $user = mysqli_fetch_assoc($result);
    return $user;
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
function getArrSharedList()
{
    $connect = connectDB();
    $query = "SELECT `to_user_id` 
              FROM `shared_lists` 
              WHERE `list_id` = '" . $_SESSION['list_id'] . "'";
    $result = mysqli_query($connect, $query);
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($users, $row);
    }
    return $users;
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
}

/**
 * выводит модальное окно
 */
function getModal($notice = null)
{
    $arrMyLists = myLists();
    $arrUsers = getUsers();
    $arrSharedList = getArrSharedList();

    foreach ($arrMyLists as $key => $val) {
        if ($val['id'] == $_SESSION['list_id']) {
            $nameList = $val['name'];
        }
    }
    require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/template_modal.php');
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
function getInviteLists()
{
    $connect = connectDB();
    $query = "SELECT `lists`.`name` AS `name`, `lists`.`id` AS `id`
              FROM `lists`
              LEFT JOIN `shared_lists`
              ON `shared_lists`.`list_id` = `lists`.`id`
              WHERE `shared_lists`.`to_user_id` = " . $_SESSION['user_id'];
    $result = mysqli_query($connect, $query);
    $inviteList = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($inviteList, $row);
    }
    require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/template_invite_lists.php');
}

/**
 * @return mixed
 * возвращает количество списков в которые пригласили пользователя
 */
function getNumInviteList()
{
    $connect = connectDB();
    $query = "SELECT COUNT(*) 
              FROM `shared_lists` 
              WHERE `to_user_id` = " . $_SESSION['user_id'];
    $result = mysqli_query($connect, $query);
    $numRow = mysqli_fetch_assoc($result);
    return $numRow['COUNT(*)'];
}

/**
 * @return bool
 * Возвращает true или false в зависимости от того состоит этот список в приглашённых
 */
function isInviteList()
{
    if (isset($_SESSION['list_id'])) {
        $connect = connectDB();
        $query = "SELECT COUNT(*) FROM `shared_lists` 
                  WHERE `list_id` = '" . $_SESSION['list_id'] . "'
                  AND `shared_user_id` != '" . $_SESSION['user_id'] . "'";
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
function getCountrys()
{
    $connect = connectDB();
    $query = "SELECT * 
              FROM `countrys`";
    $result = mysqli_query($connect, $query);
    $countrys = [];
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($countrys, $row);
    }
    return $countrys;
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

    $result = mysqli_query($connect, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($users, $row);
    }
    $headers = "Content-type: text/plain; charset=utf-8 \r\n";
    $headers .= "From: Task.manager <svarog.2580@ya.ru>\r\n";
    $headers .= "Bcc: Task.manager svarog.2580@ya.ru\r\n";
    foreach ($users as $key => $val) {
        $users .= $val['email'] . ", ";
    }
    if (!empty($users)) {
        $users = substr($users, 0, -3);
        mail($users, 'Task.manager', $message, $headers);
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
    if (isInviteList()) {
        $query = "SELECT `shared_lists`.`to_user_id`, `users`.`email`, `shared_lists`.`shared_user_id`
                  FROM `shared_lists`
                  LEFT JOIN `lists`
                  ON `lists`.`id` = `shared_lists`.`list_id`
                  LEFT JOIN `users`
                  ON `users`.`id` = `shared_lists`.`to_user_id`
                  WHERE `lists`.`id` = '$list_id'
                  AND `users`.`email_notice` = '1'
                  AND `users`.`id` NOT IN ('" . $_SESSION['user_id'] . "')";
        $users = [];
        $result = mysqli_query($connect, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($users, $row);
        }
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
        foreach ($users as $key => $val) {
            $mails .= $val['email'] . ', ';
        }
        substr($mails, 0, -2);
        mail($mails, 'Task.manager', $message);
        mysqli_free_result($result);

    } else {
        $query = "SELECT `shared_lists`.`to_user_id`, `users`.`email`, `shared_lists`.`shared_user_id`
                  FROM `shared_lists`
                  LEFT JOIN `lists`
                  ON `lists`.`id` = `shared_lists`.`list_id`
                  LEFT JOIN `users`
                  ON `users`.`id` = `shared_lists`.`to_user_id`
                  WHERE `lists`.`id` = '$list_id'
                  AND `users`.`email_notice` = '1'";
        $users = [];
        $result = mysqli_query($connect, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($users, $row);
        }
        foreach ($users as $key => $val) {
            $mails .= $val['email'] . ', ';
        }
        substr($mails, 0, -2);
        mail($mails, 'Task.manager', $message);
        mysqli_free_result($result);
    }
}

/**
 * @return bool
 * Проверяет состоит ли пользователь в группе администратор
 */
function isAdmin()
{
    $connect = connectDB();
    $query = "SELECT COUNT(*)
              FROM `users`
              LEFT JOIN `users_groups`
              ON `users_groups`.`user_id` = `users`.`id`
              WHERE `users_groups`.`group_id` = '1'
              AND `users_groups`.`user_id` = '" . $_SESSION['user_id'] . "'";
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
    require_once($_SERVER['DOCUMENT_ROOT'] . '/app/template/template_admin_panel.php');
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