<?php
error_reporting(-1);
ini_set("display_errors", 1);
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/session_start.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/function.php');
if (!isAdmin()) header('Location: /');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/controller.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/header.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/navigation.php");
getAdminPanel();
require_once($_SERVER['DOCUMENT_ROOT'] . "/app/template/footer.php");
