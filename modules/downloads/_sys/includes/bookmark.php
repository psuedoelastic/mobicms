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
Закладки
-----------------------------------------------------------------
*/
$textl = __('download_bookmark');
if (!App::user()->id) {
    echo __('access_guest_forbidden');
    exit;
}
echo '<div class="phdr"><b>' . $textl . '</b></div>';
$total = App::db()->query("SELECT COUNT(*) FROM `download__bookmark` WHERE `user_id` = " . App::user()->id)->fetchColumn();
/*
-----------------------------------------------------------------
Навигация
-----------------------------------------------------------------
*/
if ($total > App::user()->settings['page_size'])
    echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=bookmark&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
/*
-----------------------------------------------------------------
Список закладок
-----------------------------------------------------------------
*/
$i = 0;
if ($total) {
    $req_down = App::db()->query("SELECT `download__files`.*, `download__bookmark`.`id` AS `bid`
    FROM `download__files` LEFT JOIN `download__bookmark` ON `download__files`.`id` = `download__bookmark`.`file_id`
    WHERE `download__bookmark`.`user_id`=" . App::user()->id . " ORDER BY `download__files`.`time` DESC " . App::db()->pagination());
    while ($res_down = $req_down->fetch()) {
        echo (($i++ % 2) ? '<div class="list2">' : '<div class="list1">') . Download::displayFile($res_down) . '</div>';
    }
} else {
    echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
}
echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
/*
-----------------------------------------------------------------
Навигация
-----------------------------------------------------------------
*/
if ($total > App::user()->settings['page_size']) {
    echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=bookmark&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
        '<p><form action="' . $url . '" method="get">' .
        '<input type="hidden" value="bookmark" name="act" />' .
        '<input type="text" name="page" size="2"/><input type="submit" value="' . __('to_page') . ' &gt;&gt;"/></form></p>';
}
echo '<p><a href="' . $url . '">' . __('download_title') . '</a></p>';