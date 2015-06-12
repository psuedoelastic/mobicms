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

//TODO: Переделать SQL запросы

$url = App::router()->getUri(1);

/*
-----------------------------------------------------------------
Удалить альбом
-----------------------------------------------------------------
*/
if ($al && $user['id'] == App::user()->id || App::user()->rights >= 6) {
    $req_a = mysql_query("SELECT * FROM `album__cat` WHERE `id` = '$al' AND `user_id` = '" . $user['id'] . "' LIMIT 1");
    if (mysql_num_rows($req_a)) {
        $res_a = mysql_fetch_assoc($req_a);
        echo '<div class="phdr"><a href="' . $url . '?act=list&amp;user=' . $user['id'] . '"><b>' . __('photo_album') . '</b></a> | ' . __('delete') . '</div>';
        if (isset($_POST['submit'])) {
            $req = mysql_query("SELECT * FROM `album__files` WHERE `album_id` = '" . $res_a['id'] . "'");
            while ($res = mysql_fetch_assoc($req)) {
                // Удаляем файлы фотографий
                @unlink(ALBUMPATH . $user['id'] . DIRECTORY_SEPARATOR . $res['img_name']);
                @unlink(ALBUMPATH . $user['id'] . DIRECTORY_SEPARATOR . $res['tmb_name']);
                // Удаляем записи из таблицы скачиваний
                mysql_query("DELETE FROM `album__downlosystem__advt` WHERE `file_id` = '" . $res['id'] . "'");
                // Удаляем записи из таблицы голосований
                mysql_query("DELETE FROM `album__votes` WHERE `file_id` = '" . $res['id'] . "'");
                // Удаляем комментарии
                mysql_query("DELETE FROM `album__comments` WHERE `sub_id` = '" . $res['id'] . "'");
            }
            // Удаляем записи из таблиц
            mysql_query("DELETE FROM `album__files` WHERE `album_id` = '" . $res_a['id'] . "'");
            mysql_query("DELETE FROM `album__cat` WHERE `id` = '" . $res_a['id'] . "'");
            mysql_query("OPTIMIZE TABLE `album__cat`, `album__downlosystem__advt`, `album__votes`, `album__files`, `album__comments`");
            echo '<div class="menu"><p>' . __('album_deleted') . '<br />' .
                '<a href="' . $url . '?act=list&amp;user=' . $user['id'] . '">' . __('continue') . '</a></p></div>';
        } else {
            echo '<div class="rmenu"><form action="' . $url . '?act=delete&amp;al=' . $al . '&amp;user=' . $user['id'] . '" method="post">' .
                '<p>' . __('album') . ': <b>' . htmlspecialchars($res_a['name']) . '</b></p>' .
                '<p>' . __('album_delete_warning') . '</p>' .
                '<p><input type="submit" name="submit" value="' . __('delete') . '"/></p>' .
                '</form></div>' .
                '<div class="phdr"><a href="' . $url . '?act=list&amp;user=' . $user['id'] . '">' . __('cancel') . '</a></div>';
        }
    } else {
        echo __('error_wrong_data');
    }
}