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
Новые файлы
-----------------------------------------------------------------
*/
$textl = __('new_files');
$sql_down = '';
if (App::vars()->id) {
    $cat = App::db()->query("SELECT * FROM `download__category` WHERE `id` = '" . App::vars()->id . "' LIMIT 1");
    $res_down_cat = $cat->fetch();
    if (!$cat->rowCount() || !is_dir($res_down_cat['dir'])) {
        echo __('not_found_dir') . '<a href="' . $url . '">' . __('download_title') . '</a>';
        exit;
    }
    $title_pages = htmlspecialchars(mb_substr($res_down_cat['rus_name'], 0, 30));
    $textl = __('new_files') . ': ' . (mb_strlen($res_down_cat['rus_name']) > 30 ? $title_pages . '...' : $title_pages);
    $sql_down = ' AND `dir` LIKE \'' . ($res_down_cat['dir']) . '%\' ';
}
echo '<div class="phdr"><b>' . $textl . '</b></div>';
$total = App::db()->query("SELECT COUNT(*) FROM `download__files` WHERE `type` = '2'  AND `time` > $sql_down")->fetchColumn();
/*
-----------------------------------------------------------------
Навигация
-----------------------------------------------------------------
*/
if ($total > App::user()->settings['page_size'])
    echo '<div class="topmenu">' . Functions::displayPagination($url . '?id=' . App::vars()->id . '&amp;act=new_files&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
/*
-----------------------------------------------------------------
Выводим список
-----------------------------------------------------------------
*/
if ($total) {
    $i = 0;
    $req_down = App::db()->query("SELECT * FROM `download__files` WHERE `type` = '2'  AND `time` > $old $sql_down ORDER BY `time` DESC " . App::db()->pagination());
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
    echo '<div class="topmenu">' . Functions::displayPagination($url . '?id=' . App::vars()->id . '&amp;act=new_files&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
        '<p><form action="' . $url . '" method="get">' .
        '<input type="hidden" name="id" value="' . App::vars()->id . '"/>' .
        '<input type="hidden" value="new_files" name="act" />' .
        '<input type="text" name="page" size="2"/><input type="submit" value="' . __('to_page') . ' &gt;&gt;"/></form></p>';
}
echo '<p><a href="' . $url . '?id=' . App::vars()->id . '">' . __('download_title') . '</a></p>';