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

$mod = App::request()->getQuery('mod');

switch ($mod) {
    case 'up':
        /*
        -----------------------------------------------------------------
        Передвигаем альбом на позицию вверх
        -----------------------------------------------------------------
        */
        if ($al && $user['id'] == App::user()->id || App::user()->rights >= 7) {
            $req = mysql_query("SELECT `sort` FROM `album__cat` WHERE `id` = '$al' AND `user_id` = '" . $user['id'] . "'");
            if (mysql_num_rows($req)) {
                $res = mysql_fetch_assoc($req);
                $sort = $res['sort'];
                $req = mysql_query("SELECT * FROM `album__cat` WHERE `user_id` = '" . $user['id'] . "' AND `sort` < '$sort' ORDER BY `sort` DESC LIMIT 1");
                if (mysql_num_rows($req)) {
                    $res = mysql_fetch_assoc($req);
                    $id2 = $res['id'];
                    $sort2 = $res['sort'];
                    mysql_query("UPDATE `album__cat` SET `sort` = '$sort2' WHERE `id` = '$al'");
                    mysql_query("UPDATE `album__cat` SET `sort` = '$sort' WHERE `id` = '$id2'");
                }
            }
        }
        break;

    case 'down':
        /*
        -----------------------------------------------------------------
        Передвигаем альбом на позицию вниз
        -----------------------------------------------------------------
        */
        if ($al && $user['id'] == App::user()->id || App::user()->rights >= 7) {
            $req = mysql_query("SELECT `sort` FROM `album__cat` WHERE `id` = '$al' AND `user_id` = '" . $user['id'] . "'");
            if (mysql_num_rows($req)) {
                $res = mysql_fetch_assoc($req);
                $sort = $res['sort'];
                $req = mysql_query("SELECT * FROM `album__cat` WHERE `user_id` = '" . $user['id'] . "' AND `sort` > '$sort' ORDER BY `sort` ASC LIMIT 1");
                if (mysql_num_rows($req)) {
                    $res = mysql_fetch_assoc($req);
                    $id2 = $res['id'];
                    $sort2 = $res['sort'];
                    mysql_query("UPDATE `album__cat` SET `sort` = '$sort2' WHERE `id` = '$al'");
                    mysql_query("UPDATE `album__cat` SET `sort` = '$sort' WHERE `id` = '$id2'");
                }
            }
        }
        break;
}

header('Location: ' . App::router()->getUri(2) . '?act=list&user=' . $user['id']);