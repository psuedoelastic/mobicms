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
Удалить картинку
-----------------------------------------------------------------
*/
if ($img && $user['id'] == App::user()->id || App::user()->rights >= 6) {
    //TODO: переделать запрос
    $req = mysql_query("SELECT * FROM `album__files` WHERE `id` = '$img' AND `user_id` = '" . $user['id'] . "' LIMIT 1");
    if (mysql_num_rows($req)) {
        $res = mysql_fetch_assoc($req);
        $album = $res['album_id'];
        echo '<div class="phdr"><a href="' . $url . '?act=show&amp;al=' . $album . '&amp;user=' . $user['id'] . '"><b>' . __('photo_album') . '</b></a> | ' . __('image_delete') . '</div>';
        //TODO: Сделать проверку, чтоб администрация не могла удалять фотки старших по должности
        if (isset($_POST['submit'])) {
            // Удаляем файлы картинок
            @unlink(ALBUMPATH . $user['id'] . DIRECTORY_SEPARATOR . $res['img_name']);
            @unlink(ALBUMPATH . $user['id'] . DIRECTORY_SEPARATOR . $res['tmb_name']);
            // Удаляем записи из таблиц
            mysql_query("DELETE FROM `album__files` WHERE `id` = '$img'");
            mysql_query("DELETE FROM `album__votes` WHERE `file_id` = '$img'");
            mysql_query("DELETE FROM `album__comments` WHERE `sub_id` = '$img'");
            mysql_query("OPTIMIZE TABLE `album__comments`, `album__votes`");
            header('Location: ' . $url . '?act=show&al=' . $album . '&user=' . $user['id']);
        } else {
            echo '<div class="rmenu"><form action="' . $url . '?act=image_delete&amp;img=' . $img . '&amp;user=' . $user['id'] . '" method="post">' .
                '<p>' . __('image_delete_warning') . '</p>' .
                '<p><input type="submit" name="submit" value="' . __('delete') . '"/></p>' .
                '</form></div>' .
                '<div class="phdr"><a href="' . $url . '?act=show&amp;al=' . $album . 'user=' . $user['id'] . '">' . __('cancel') . '</a></div>';
        }
    } else {
        echo __('error_wrong_data');
    }
}