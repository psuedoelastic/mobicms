<?php

/**
 * @package     mobiCMS
 * @link        http://mobicms.net
 * @copyright   Copyright (C) 2008-2011 mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://mobicms.net/about
 */

defined('MOBICMS') or die('Error: restricted access');
$url = App::router()->getUri(1);

/*
-----------------------------------------------------------------
Файлы юзера
-----------------------------------------------------------------
*/
$textl = __('user_files');
//TODO: Переделать на класс Users
if (($user = Mobi::getUser()) === false || (!Mobi::$USER && !App::user()->id)) {
    echo __('user_does_not_exist');
    exit;
}
if (!Mobi::$USER) Mobi::$USER = App::user()->id;
echo '<div class="phdr"><a href="/profile?user=' . Mobi::$USER . '">' . __('profile') . '</a></div>' .
    '<div class="user"><p>' . functions::displayUser($user, ['iphide' => 0]) . '</p></div>' .
    '<div class="phdr"><b>' . __('user_files') . '</b></div>';
$total = App::db()->query("SELECT COUNT(*) FROM `" . TP . "download__files` WHERE `type` = '2'  AND `user_id` = " . Mobi::$USER)->fetchColumn();
/*
-----------------------------------------------------------------
Навигация
-----------------------------------------------------------------
*/
if ($total > App::user()->settings['page_size'])
    echo '<div class="topmenu">' . Functions::displayPagination($url . '?user=' . Mobi::$USER . '&amp;act=user_files&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
/*
-----------------------------------------------------------------
Список файлов
-----------------------------------------------------------------
*/
$i = 0;
if ($total) {
    $req_down = App::db()->query("SELECT * FROM `" . TP . "download__files` WHERE `type` = '2'  AND `user_id` = " . Mobi::$USER . " ORDER BY `time` DESC " . App::db()->pagination());
    while ($res_down = $req_down->fetch()) {
        echo (($i++ % 2) ? '<div class="list2">' : '<div class="list1">') . Download::displayFile($res_down) . '</div>';
    }
} else {
    echo '<div class="rmenu"><p>' . __('list_empty') . '</p></div>';
}
echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
/*
-----------------------------------------------------------------
Навигация
-----------------------------------------------------------------
*/
if ($total > App::user()->settings['page_size']) {
    echo '<div class="topmenu">' . Functions::displayPagination($url . '?user=' . Mobi::$USER . '&amp;act=user_files&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
        '<p><form action="' . $url . '" method="get">' .
        '<input type="hidden" name="USER" value="' . Mobi::$USER . '"/>' .
        '<input type="hidden" value="user_files" name="act" />' .
        '<input type="text" name="page" size="2"/><input type="submit" value="' . __('to_page') . ' &gt;&gt;"/></form></p>';
}
echo '<p><a href="' . $url . '">' . __('download_title') . '</a></p>';