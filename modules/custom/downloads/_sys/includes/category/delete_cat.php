<?php

/**
 * @package     mobiCMS
 * @link        http://mobicms.net
 * @copyright   Copyright (C) 2008-2011 mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://mobicms.net/about
 */

//TODO: Добавить проверку, пустой ли каталог, перед удалением
defined('MOBICMS') or die('Error: restricted access');

$url = App::router()->getUri(1);
$id = abs(intval(App::request()->getQuery('id', 0)));

/*
-----------------------------------------------------------------
Удаление каталога
-----------------------------------------------------------------
*/
if (App::user()->rights == 4 || App::user()->rights >= 6) {
    $del_cat = App::db()->query("SELECT COUNT(*) FROM `" . TP . "download__category` WHERE `refid` = " . $id)->fetchColumn();
    $req = App::db()->query("SELECT * FROM `" . TP . "download__category` WHERE `id` = " . $id);
    if (!$req->rowCount() || $del_cat) {
        echo ($del_cat ? __('sub_catalogs') : __('not_found_dir')) . ' <a href="' . $url . '">' . __('download_title') . '</a>';
        exit;
    }
    $res = $req->fetch();
    if (isset($_GET['yes'])) {
        $req_down = App::db()->query("SELECT * FROM `" . TP . "download__files` WHERE `refid` = " . $id);
        while ($res_down = $req_down->fetch()) {
            if (is_dir($screens_path . '/' . $res_down['id'])) {
                $dir_clean = opendir($screens_path . '/' . $res_down['id']);
                while ($file = readdir($dir_clean)) {
                    if ($file != '.' && $file != '..') {
                        @unlink($screens_path . '/' . $res_down['id'] . '/' . $file);
                    }
                }
                closedir($dir_clean);
                rmdir($screens_path . '/' . $res_down['id']);
            }
            @unlink(ROOTPATH . 'files/download/java_icons/' . $res_down['id'] . '.png');
            $req_file_more = App::db()->query("SELECT * FROM `" . TP . "download__more` WHERE `refid` = " . $res_down['id']);
            while ($res_file_more = $req_file_more->fetch()) {
                @unlink($res_down['dir'] . '/' . $res_file_more['name']);
                @unlink(ROOTPATH . 'files/download/java_icons/' . $res_file_more['id'] . '.png');
            }
            @unlink($res_down['dir'] . '/' . $res_down['name']);
            App::db()->exec("DELETE FROM `" . TP . "download__more` WHERE `refid` = " . $res_down['id']);
            App::db()->exec("DELETE FROM `" . TP . "download__comments` WHERE `sub_id` = " . $res_down['id']);
            App::db()->exec("DELETE FROM `" . TP . "download__bookmark` WHERE `file_id` = " . $res_down['id']);
        }
        App::db()->exec("DELETE FROM `" . TP . "download__files` WHERE `refid` = " . $id);
        App::db()->exec("DELETE FROM `" . TP . "download__category` WHERE `id` = " . $id);

        App::db()->query("OPTIMIZE TABLE `" . TP . "download__bookmark`, `" . TP . "download__files`, `" . TP . "download__comments`, `" . TP . "download__more`, `" . TP . "download__category`");

        rmdir($res['dir']);
        header('location: ' . $url . '?id=' . $res['refid']);
    } else {
        echo '<div class="phdr"><b>' . __('download_del_cat') . '</b></div>' .
            '<div class="rmenu"><p><a href="' . $url . '?act=delete_cat&amp;id=' . $id . '&amp;yes"><b>' . __('delete') . '</b></a></p></div>' .
            '<div class="phdr"><a href="' . $url . '?id=' . $id . '">' . __('back') . '</a></div>';
    }
} else {
    header('Location: ' . App::cfg()->sys->homeurl . '404');
}