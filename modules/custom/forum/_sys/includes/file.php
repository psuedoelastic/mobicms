<?php
/**
 * mobiCMS Content Management System (http://mobicms.net)
 *
 * For copyright and license information, please see the LICENSE.md
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://mobicms.net mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 * @license     LICENSE.md (see attached file)
 */

defined('MOBICMS') or die('Error: restricted access');

$url = App::router()->getUri(1);
$id = abs(intval(App::request()->getQuery('id', 0)));

$error = false;
if ($id) {
    /*
    -----------------------------------------------------------------
    Скачивание прикрепленного файла Форума
    -----------------------------------------------------------------
    */
    $req = App::db()->query("SELECT * FROM `" . TP . "forum__files` WHERE `id` = " . $id);
    if ($req->rowCount()) {
        $res = $req->fetch();
        if (file_exists(ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'forum' . DIRECTORY_SEPARATOR . $res['filename'])) {
            App::db()->exec("UPDATE `" . TP . "forum__files` SET  `dlcount` = '" . ++$res['dlcount'] . "' WHERE `id` = " . $id);
            header('location: ' . App::cfg()->sys->homeurl . 'files/forum/' . $res['filename']);
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }
    if ($error) {
        echo __('error_file_not_exist') . '<a href="' . $url . '">' . __('to_forum') . '</a>';
        exit;
    }
} else {
    header('location: ' . $url);
}
