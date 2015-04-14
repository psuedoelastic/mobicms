<?php

/**
 * @package     mobiCMS
 * @link        http://mobicms.net
 * @copyright   Copyright (C) 2008-2012 mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://mobicms.net/about
 */

defined('_IN_MOBICMS') or die('Error: restricted access');

//TODO: переделать SQL запросы

global $user, $al;

/*
-----------------------------------------------------------------
Создать / изменить альбом
-----------------------------------------------------------------
*/
if ($user['id'] == App::user()->id || App::user()->rights >= 7) {
    if ($al) {
        $req = mysql_query("SELECT * FROM `" . TP . "album__cat` WHERE `id` = '$al' AND `user_id` = '" . $user['id'] . "'");
        if (mysql_num_rows($req)) {
            echo '<div class="phdr"><b>' . __('album_edit') . '</b></div>';
            $res = mysql_fetch_assoc($req);
            $name = htmlspecialchars($res['name']);
            $description = htmlspecialchars($res['description']);
            $password = htmlspecialchars($res['password']);
            $access = $res['access'];
        } else {
            echo __('error_wrong_data');
            exit;
        }
    } else {
        echo '<div class="phdr"><b>' . __('album_create') . '</b></div>';
        $name = '';
        $description = '';
        $password = '';
        $access = 0;
    }
    $error = [];
    if (isset($_POST['submit'])) {
        // Принимаем данные
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $access = isset($_POST['access']) ? abs(intval($_POST['access'])) : null;
        // Проверяем на ошибки
        if (empty($name))
            $error[] = __('error_empty_title');
        elseif (mb_strlen($name) < 2 || mb_strlen($name) > 50)
            $error[] = __('title') . ': ' . __('error_wrong_lenght');
        $description = mb_substr($description, 0, 500);
        if ($access == 2 && empty($password))
            $error[] = __('error_empty_password');
        elseif ($access == 2 && mb_strlen($password) < 3 || mb_strlen($password) > 15)
            $error[] = __('password') . ': ' . __('error_wrong_lenght');
        if ($access < 1 || $access > 4)
            $error[] = __('error_wrong_data');
        // Проверяем, есть ли уже альбом с таким же именем?
        if (!$al && mysql_num_rows(mysql_query("SELECT * FROM `" . TP . "album__cat` WHERE `name` = '" . mysql_real_escape_string($name) . "' AND `user_id` = '" . $user['id'] . "' LIMIT 1")))
            $error[] = __('error_album_exists');
        if (!$error) {
            if ($al) {
                // Изменяем данные в базе
                mysql_query("UPDATE `" . TP . "album__files` SET `access` = '$access' WHERE `album_id` = '$al' AND `user_id` = '" . $user['id'] . "'");
                mysql_query("UPDATE `" . TP . "album__cat` SET
                    `name` = '" . mysql_real_escape_string($name) . "',
                    `description` = '" . mysql_real_escape_string($description) . "',
                    `password` = '" . mysql_real_escape_string($password) . "',
                    `access` = '$access'
                    WHERE `id` = '$al' AND `user_id` = '" . $user['id'] . "'
                ");
            } else {
                // Вычисляем сортировку
                $req = mysql_query("SELECT * FROM `" . TP . "album__cat` WHERE `user_id` = '" . $user['id'] . "' ORDER BY `sort` DESC LIMIT 1");
                if (mysql_num_rows($req)) {
                    $res = mysql_fetch_assoc($req);
                    $sort = $res['sort'] + 1;
                } else {
                    $sort = 1;
                }
                // Заносим данные в базу
                mysql_query("INSERT INTO `" . TP . "album__cat` SET
                    `user_id` = '" . $user['id'] . "',
                    `name` = '" . mysql_real_escape_string($name) . "',
                    `description` = '" . mysql_real_escape_string($description) . "',
                    `password` = '" . mysql_real_escape_string($password) . "',
                    `access` = '$access',
                    `sort` = '$sort'
                ");
            }
            echo '<div class="gmenu"><p>' . ($al ? __('album_changed') : __('album_created')) . '<br />' .
                '<a href="' . App::router()->getUri(2) . '?act=list&amp;user=' . $user['id'] . '">' . __('continue') . '</a></p></div>';
            exit;
        }
    }
    if ($error) {
        App::view()->error = $error;
    }
    App::view()->name = $name;
    App::view()->access = $access;
    App::view()->password = $password;
    App::view()->description = $description;
    App::view()->setTemplate('album_edit.php');
}