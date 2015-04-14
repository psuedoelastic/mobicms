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
Файлы на модерации
-----------------------------------------------------------------
*/
$textl = __('mod_files');
if (App::user()->rights == 4 || App::user()->rights >= 6) {
    echo '<div class="phdr"><a href="' . App::router()->getUri(1) . '"><b>' . __('downloads') . '</b></a> | ' . $textl . '</div>';
    if (App::vars()->id) {
        App::db()->exec("UPDATE `" . TP . "download__files` SET `type` = 2 WHERE `id` = '" . App::vars()->id . "' LIMIT 1");
        echo '<div class="gmenu">' . __('file_accepted_ok') . '</div>';
    } else if (isset($_POST['all_mod'])) {
        App::db()->exec("UPDATE `" . TP . "download__files` SET `type` = 2 WHERE `type` = '3'");
        echo '<div class="gmenu">' . __('file_accepted_all_ok') . '</div>';
    }

    $total = App::db()->query("SELECT COUNT(*) FROM `" . TP . "download__files` WHERE `type` = '3'")->fetchColumn();

    /*
    -----------------------------------------------------------------
    Навигация
    -----------------------------------------------------------------
    */
    if ($total > App::user()->settings['page_size'])
        echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=mod_files&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
    $i = 0;
    if ($total) {
        $req_down = App::db()->query("SELECT * FROM `" . TP . "download__files` WHERE `type` = '3' ORDER BY `time` DESC " . App::db()->pagination());
        while ($res_down = $req_down->fetch()) {
            echo (($i++ % 2) ? '<div class="list2">' : '<div class="list1">') . Download::displayFile($res_down) .
                '<div class="sub"><a href="' . $url . '?act=mod_files&amp;id=' . $res_down['id'] . '">' . __('file_accepted') . '</a> | ' .
                '<span class="red"><a href="' . $url . '?act=delete_file&amp;id=' . $res_down['id'] . '">' . __('delete') . '</a></span></div></div>';
        }
        echo '<div class="rmenu"><form name="" action="' . $url . '?act=mod_files" method="post"><input type="submit" name="all_mod" value="' . __('file_accepted_all') . '"/></form></div>';
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
        echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=mod_files&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
            '<p><form action="' . $url . '" method="get">' .
            '<input type="hidden" value="top_users" name="act" />' .
            '<input type="text" name="page" size="2"/><input type="submit" value="' . __('to_page') . ' &gt;&gt;"/></form></p>';
    }
    echo '<p><a href="' . $url . '">' . __('download_title') . '</a></p>';
} else {
    header('Location: ' . App::cfg()->sys->homeurl . '404');
}