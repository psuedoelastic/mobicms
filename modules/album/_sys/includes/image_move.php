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

$url = App::router()->getUri(1);

/*
-----------------------------------------------------------------
Перемещение картинки в другой альбом
-----------------------------------------------------------------
*/
//TODO: переделать запросы
if ($img && $user['id'] == App::user()->id || App::user()->rights >= 6) {
    $req = mysql_query("SELECT * FROM `" . TP . "album__files` WHERE `id` = '$img' AND `user_id` = '" . $user['id'] . "'");
    if (mysql_num_rows($req)) {
        $image = mysql_fetch_assoc($req);
        echo '<div class="phdr"><a href="' . $url . '?act=show&amp;al=' . $image['album_id'] . '&amp;user=' . $user['id'] . '"><b>' . __('photo_album') . '</b></a> | ' . __('image_move') . '</div>';
        if (isset($_POST['submit'])) {
            $req_a = mysql_query("SELECT * FROM `" . TP . "album__cat` WHERE `id` = '$al' AND `user_id` = '" . $user['id'] . "'");
            if (mysql_num_rows($req_a)) {
                $res_a = mysql_fetch_assoc($req_a);
                mysql_query("UPDATE `" . TP . "album__files` SET
                    `album_id` = '$al',
                    `access` = '" . $res_a['access'] . "'
                    WHERE `id` = '$img'
                ");
                echo '<div class="gmenu"><p>' . __('image_moved') . '<br />' .
                    '<a href="' . $url . '?act=show&amp;al=' . $al . '&amp;user=' . $user['id'] . '">' . __('continue') . '</a></p></div>';
            } else {
                echo __('error_wrong_data');
            }
        } else {
            $req = mysql_query("SELECT * FROM `" . TP . "album__cat` WHERE `user_id` = '" . $user['id'] . "' AND `id` != '" . $image['album_id'] . "' ORDER BY `sort` ASC");
            if (mysql_num_rows($req)) {
                echo '<form action="' . $url . '?act=image_move&amp;img=' . $img . '&amp;user=' . $user['id'] . '" method="post">' .
                    '<div class="menu"><p><h3>' . __('album_select') . '</h3>' .
                    '<select name="al">';
                while ($res = mysql_fetch_assoc($req)) {
                    echo '<option value="' . $res['id'] . '">' . htmlspecialchars($res['name']) . '</option>';
                }
                echo '</select></p>' .
                    '<p><input type="submit" name="submit" value="' . __('move') . '"/></p>' .
                    '</div></form>' .
                    '<div class="phdr"><a href="' . $url . '?act=show&amp;al=' . $image['album_id'] . '&amp;user=' . $user['id'] . '">' . __('cancel') . '</a></div>';
            } else {
                echo __('image_move_error') . ' <a href="' . $url . '?act=list&amp;user=' . $user['id'] . '">' . __('continue') . '</a>';
            }
        }
    } else {
        echo __('error_wrong_data');
    }
}