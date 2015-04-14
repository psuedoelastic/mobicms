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
Топ файлов
-----------------------------------------------------------------
*/
if (App::vars()->id == 2) {
    $textl = __('top_files_comments');
} elseif (App::vars()->id == 1) {
    $textl = __('top_files_download');
} else {
    $textl = __('top_files_popular');
}

//TODO: Переделать на получение настроек из таблицы модулей
$linkTopComments = App::cfg()->sys->acl_downloads_comm || App::user()->rights >= 7 ? '<br /><a href="' . $url . '?act=top_files&amp;id=2">' . __('top_files_comments') . '</a>' : '';
echo '<div class="phdr"><a href="' . App::router()->getUri(1) . '"><b>' . __('downloads') . '</b></a> | ' . $textl . ' (' . $set_down['top'] . ')</div>';
//TODO: Переделать на получение настроек из таблицы модулей
if (App::vars()->id == 2 && (App::cfg()->sys->acl_downloads_comm || App::user()->rights >= 7)) {
    echo '<div class="gmenu"><a href="' . $url . '?act=top_files&amp;id=0">' . __('top_files_popular') . '</a><br />' .
        '<a href="' . $url . '?act=top_files&amp;id=1">' . __('top_files_download') . '</a></div>';
    $sql = '`total`';
} elseif (App::vars()->id == 1) {
    echo '<div class="gmenu"><a href="' . $url . '?act=top_files&amp;id=0">' . __('top_files_popular') . '</a>' . $linkTopComments . '</div>';
    $sql = '`field`';
} else {
    echo '<div class="gmenu"><a href="' . $url . '?act=top_files&amp;id=1">' . __('top_files_download') . '</a>' . $linkTopComments . '</div>';
    $sql = '`rate`';
}
/*
-----------------------------------------------------------------
Выводим список
-----------------------------------------------------------------
*/
$req_down = App::db()->query("SELECT * FROM `" . TP . "download__files` WHERE `type` = 2 ORDER BY $sql DESC LIMIT " . $set_down['top']);
$i = 0;
while ($res_down = $req_down->fetch()) {
    echo (($i++ % 2) ? '<div class="list2">' : '<div class="list1">') . Download::displayFile($res_down, 1) . '</div>';
}
echo '<div class="phdr"><a href="' . $url . '">' . __('download_title') . '</a></div>';