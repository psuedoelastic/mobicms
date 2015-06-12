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
$id = App::request()->getQuery('id', 0);

/*
-----------------------------------------------------------------
Удаление файл
-----------------------------------------------------------------
*/
$req_down = App::db()->query("SELECT * FROM `download__files` WHERE `id` = '" . $id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();
if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name'])) {
    echo __('not_found_file') . ' <a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}
if (App::user()->rights == 4 || App::user()->rights >= 6) {
    if (isset($_GET['yes'])) {
        if (is_dir($screens_path . '/' . $id)) {
            $dir_clean = opendir($screens_path . '/' . $id);
            while ($file = readdir($dir_clean)) {
                if ($file != '.' && $file != '..') {
                    @unlink($screens_path . '/' . $id . '/' . $file);
                }
            }
            closedir($dir_clean);
            rmdir($screens_path . '/' . $id);
        }
        @unlink(ROOT_PATH . 'files/download/java_icons/' . $id . '.png');
        $req_file_more = App::db()->query("SELECT * FROM `download__more` WHERE `refid` = " . $id);
        if ($req_file_more->rowCount()) {
            while ($res_file_more = $req_file_more->fetch()) {
                if (is_file($res_down['dir'] . '/' . $res_file_more['name']))
                    @unlink($res_down['dir'] . '/' . $res_file_more['name']);
                @unlink(ROOT_PATH . 'files/download/java_icons/' . $res_file_more['id'] . '_' . $id . '.png');
            }
            App::db()->exec("DELETE FROM `download__more` WHERE `refid` = " . $id);
        }
        App::db()->exec("DELETE FROM `download__bookmark` WHERE `file_id` = " . $id);
        App::db()->exec("DELETE FROM `download__comments` WHERE `sub_id` = " . $id);
        @unlink($res_down['dir'] . '/' . $res_down['name']);
        $dirid = $res_down['refid'];
        $sql = '';
        $i = 0;
        while ($dirid != '0' && $dirid != "") {
            $res = App::db()->query("SELECT `refid` FROM `download__category` WHERE `id` = '$dirid' LIMIT 1")->fetch();
            if ($i) $sql .= ' OR ';
            $sql .= '`id` = \'' . $dirid . '\'';
            $dirid = $res['refid'];
            ++$i;
        }
        App::db()->exec("UPDATE `download__category` SET `total` = (`total`-1) WHERE $sql");
        App::db()->exec("DELETE FROM `download__files` WHERE `id` = " . $id);
        App::db()->query("OPTIMIZE TABLE `download__files`");
        header('Location: ' . $url . '?id=' . $res_down['refid']);
    } else {
        echo '<div class="phdr"><b>' . __('delete_file') . '</b></div>' .
            '<div class="rmenu"><p><a href="' . $url . '?act=delete_file&amp;id=' . $id . '&amp;yes"><b>' . __('delete') . '</b></a></p></div>' .
            '<div class="phdr"><a href="' . $url . '?act=view&amp;id=' . $id . '">' . __('back') . '</a></div>';
    }
} else {
    header('Location: ' . App::cfg()->sys->homeurl . '404');
}