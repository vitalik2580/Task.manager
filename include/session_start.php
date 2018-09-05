<?php
session_start();
if (!isset($_SESSION['active'])) {
    setcookie(session_name(), session_id(), 2);
    session_destroy();
    header('Location: /authorization.php');
} elseif (!isset($_COOKIE['login']) || $_COOKIE['login'] != $_SESSION['login']) {
    setcookie(session_name(), session_id(), 2);
    session_destroy();
    header('Location: /authorization.php');
} else {
    setcookie(session_name(), session_id(), time() + 60 * 20, '/');
    setcookie('login', $_SESSION['login'], time() + 60 * 60 * 24 * 30, '/');
}
