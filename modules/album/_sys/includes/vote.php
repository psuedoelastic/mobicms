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

/*
-----------------------------------------------------------------
Голосуем за фотографию
-----------------------------------------------------------------
*/
if (!$img) {
    echo __('error_wrong_data');
    exit;
}
$check = mysql_query("SELECT * FROM `" . TP . "album__votes` WHERE `user_id` = " . App::user()->id . " AND `file_id` = '$img' LIMIT 1");
if (mysql_num_rows($check)) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
$req = mysql_query("SELECT * FROM `" . TP . "album__files` WHERE `id` = '$img' AND `user_id` != " . App::user()->id);
if (mysql_num_rows($req)) {
    $res = mysql_fetch_assoc($req);
    switch (App::request()->getQuery('mod', '')) {
        case 'plus':
            /*
            -----------------------------------------------------------------
            Отдаем положительный голос
            -----------------------------------------------------------------
            */
            mysql_query("INSERT INTO `" . TP . "album__votes` SET
                `user_id` = '" . App::user()->id . "',
                `file_id` = '$img',
                `vote` = '1'
            ");
            mysql_query("UPDATE `" . TP . "album__files` SET `vote_plus` = '" . ($res['vote_plus'] + 1) . "' WHERE `id` = '$img'");
            break;

        case 'minus':
            /*
            -----------------------------------------------------------------
            Отдаем отрицательный голос
            -----------------------------------------------------------------
            */
            mysql_query("INSERT INTO `" . TP . "album__votes` SET
                `user_id` = '" . App::user()->id . "',
                `file_id` = '$img',
                `vote` = '-1'
            ");
            mysql_query("UPDATE `" . TP . "album__files` SET `vote_minus` = '" . ($res['vote_minus'] + 1) . "' WHERE `id` = '$img'");
            break;
    }
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    echo __('error_wrong_data');
}