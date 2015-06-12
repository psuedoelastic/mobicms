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

/*
-----------------------------------------------------------------
Загрузка выбранного файла и обработка счетчика скачиваний
-----------------------------------------------------------------
*/
//TODO: переделать запросы
$error = [];
$req = mysql_query("SELECT * FROM `album__files` WHERE `id` = '$img'");
if (mysql_num_rows($req)) {
    $res = mysql_fetch_assoc($req);
    // Проверка прав доступа
    if (App::user()->rights < 6 && App::user()->id != $res['user_id']) {
        $req_a = mysql_query("SELECT * FROM `album__cat` WHERE `id` = '" . $res['album_id'] . "'");
        if (mysql_num_rows($req_a)) {
            $res_a = mysql_fetch_assoc($req_a);
            if ($res_a['access'] == 1 || $res_a['access'] == 2 && (!isset($_SESSION['ap']) || $_SESSION['ap'] != $res_a['password']))
                $error[] = __('access_forbidden');
        } else {
            $error[] = __('error_wrong_data');
        }
    }
    // Проверка наличия файла
    if (!$error && !file_exists(ALBUMPATH . $res['user_id'] . DIRECTORY_SEPARATOR . $res['img_name']))
        $error[] = __('error_file_not_exist');
} else {
    $error[] = __('error_wrong_data');
}
if (!$error) {
    // Счетчик скачиваний
    if (!mysql_result(mysql_query("SELECT COUNT(*) FROM `album__downlosystem__advt` WHERE `user_id` = '" . App::user()->id . "' AND `file_id` = '$img'"), 0)) {
        mysql_query("INSERT INTO `album__downlosystem__advt` SET `user_id` = '" . App::user()->id . "', `file_id` = '$img', `time` = '" . time() . "'");
        $downloads = mysql_result(mysql_query("SELECT COUNT(*) FROM `album__downlosystem__advt` WHERE `file_id` = '$img'"), 0);
        mysql_query("UPDATE `album__files` SET `downlosystem__advt` = '$downloads' WHERE `id` = '$img'");
    }
    // Отдаем файл
    header('location: ' . App::cfg()->homeurl . 'files/users/album/' . $res['user_id'] . '/' . $res['img_name']);
} else {
    echo $error . ' <a href="' . App::router()->getUri(2) . '">' . __('back') . '</a>';
}