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
Редактирование описания файла
-----------------------------------------------------------------
*/
$req_down = App::db()->query("SELECT * FROM `" . TP . "download__files` WHERE `id` = '" . App::vars()->id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();
if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name']) || (App::user()->rights < 6 && App::user()->rights != 4)) {
    echo '<a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}
if (isset($_POST['submit'])) {
    $text = isset($_POST['opis']) ? trim($_POST['opis']) : '';

    $stmt = App::db()->prepare("
        UPDATE `" . TP . "download__files` SET
        `about`    = ?
        WHERE `id` = ?
    ");

    $stmt->execute([
        $text,
        App::vars()->id
    ]);
    $stmt = null;

    header('Location: ' . $url . '?act=view&id=' . App::vars()->id);
} else {
    echo '<div class="phdr"><b>' . __('dir_desc') . ':</b> ' . htmlspecialchars($res_down['rus_name']) . '</div>' .
        '<div class="list1"><form action="' . $url . '?act=edit_about&amp;id=' . App::vars()->id . '" method="post">' .
        '<small>' . __('desc_file_faq') . '</small><br />' .
        '<textarea name="opis">' . htmlentities($res_down['about'], ENT_QUOTES, 'UTF-8') . '</textarea><br />' .
        '<input type="submit" name="submit" value="' . __('sent') . '"/></form></div>' .
        '<div class="phdr"><a href="' . $url . '?act=view&amp;id=' . App::vars()->id . '">' . __('back') . '</a></div>';
}