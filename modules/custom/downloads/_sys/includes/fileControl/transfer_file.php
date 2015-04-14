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
Перенос файла
-----------------------------------------------------------------
*/
$req_down = App::db()->query("SELECT * FROM `" . TP . "download__files` WHERE `id` = '" . App::vars()->id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();
if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name'])) {
    echo __('not_found_file') . ' <a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if (App::user()->rights > 6) {
    $catId = isset($_GET['catId']) ? abs(intval($_GET['catId'])) : 0;
    if ($catId) {
        $queryDir = App::db()->query("SELECT * FROM `" . TP . "download__category` WHERE `id` = '$catId' LIMIT 1");
        if (!$queryDir->rowCount()) $catId = 0;
    }
    echo '<div class="phdr"><a href="' . $url . '?act=view&amp;id=' . App::vars()->id . '">' . __('back') . '</a> | <b>' . __('transfer_file') . '</b></div>';
    switch ($do) {
        case 'transfer':
            if ($catId) {
                if ($catId == $res_down['refid']) {
                    echo '<a href="' . $url . '?act=transfer_file&amp;id=' . App::vars()->id . '&amp;catId=' . $catId . '">' . __('back') . '</a>';
                    exit;
                }
                if (isset($_GET['yes'])) {
                    $resDir = $queryDir->fetch();
                    $req_file_more = App::db()->query("SELECT * FROM `" . TP . "download__more` WHERE `refid` = '" . App::vars()->id . "'");
                    if ($req_file_more->rowCount()) {
                        while ($res_file_more = $req_file_more->fetch()) {
                            copy($res_down['dir'] . '/' . $res_file_more['name'], $resDir['dir'] . '/' . $res_file_more['name']);
                            unlink($res_down['dir'] . '/' . $res_file_more['name']);
                        }
                    }

                    $name = $res_down['name'];
                    $newFile = $resDir['dir'] . '/' . $res_down['name'];
                    if (is_file($newFile)) {
                        $name = time() . '_' . $res_down['name'];
                        $newFile = $resDir['dir'] . '/' . $name;

                    }
                    copy($res_down['dir'] . '/' . $res_down['name'], $newFile);
                    unlink($res_down['dir'] . '/' . $res_down['name']);

                    $stmt = App::db()->prepare("
                        UPDATE `" . TP . "download__files` SET
                        `name`     = ?,
                        `dir`      = ?,
                        `refid`    = ?
                        WHERE `id` = ?
                    ");

                    $stmt->execute([
                        $name,
                        $resDir['dir'],
                        $catId,
                        App::vars()->id
                    ]);
                    $stmt = null;

                    echo '<div class="menu"><p>' . __('transfer_file_ok') . '</p></div>' .
                        '<div class="phdr"><a href="' . $url . '?act=recount">' . __('download_recount') . '</a></div>';
                } else {
                    echo '<div class="menu"><p><a href="' . $url . '?act=transfer_file&amp;id=' . App::vars()->id . '&amp;catId=' . $catId . '&amp;do=transfer&amp;yes"><b>' . __('transfer_file') . '</b></a></p></div>' .
                        '<div class="phdr"><br /></div>';
                }
            }
            break;
        default:
            $queryCat = App::db()->query("SELECT * FROM `" . TP . "download__category` WHERE `refid` = '$catId'");
            $totalCat = $queryCat->rowCount();
            $i = 0;
            if ($totalCat > 0) {
                while ($resCat = $queryCat->fetch()) {
                    echo ($i++ % 2) ? '<div class="list2">' : '<div class="list1">';
                    echo Functions::loadModuleImage('folder.png') . '&#160;' .
                        '<a href="' . $url . '?act=transfer_file&amp;id=' . App::vars()->id . '&amp;catId=' . $resCat['id'] . '">' . htmlspecialchars($resCat['rus_name']) . '</a>';
                    if ($resCat['id'] != $res_down['refid'])
                        echo '<br /><small><a href="' . $url . '?act=transfer_file&amp;id=' . App::vars()->id . '&amp;catId=' . $resCat['id'] . '&amp;do=transfer">' . __('move_this_folder') . '</a></small>';
                    echo '</div>';
                }
            } else
                echo '<div class="rmenu"><p>' . __('list_empty') . '</p></div>';
            echo '<div class="phdr">' . __('total') . ': ' . $totalCat . '</div>';
            if ($catId && $catId != $res_down['refid'])
                echo '<p><div class="func"><a href="' . $url . '?act=transfer_file&amp;id=' . App::vars()->id . '&amp;catId=' . $catId . '&amp;do=transfer">' . __('move_this_folder') . '</a></div></p>';
    }
    echo '<p><a href="' . $url . '?act=view&amp;id=' . App::vars()->id . '">' . __('back') . '</a></p>';
} else {
    header('Location: ' . App::cfg()->sys->homeurl . '404');
}