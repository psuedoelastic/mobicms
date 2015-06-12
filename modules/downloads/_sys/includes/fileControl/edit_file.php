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
Редактирование файла
-----------------------------------------------------------------
*/
$req_down = App::db()->query("SELECT * FROM `download__files` WHERE `id` = '" . App::vars()->id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();
if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name']) || (App::user()->rights < 6 && App::user()->rights != 4)) {
    echo '<a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}
if (isset($_POST['submit'])) {
    $name = isset($_POST['text']) ? trim($_POST['text']) : null;
    $name_link = isset($_POST['name_link']) ? htmlspecialchars(mb_substr($_POST['name_link'], 0, 200)) : null;
    if ($name_link && $name) {
        $stmt = App::db()->prepare("
            UPDATE `download__files` SET
            `rus_name` = ?,
            `text`     = ?
            WHERE `id` = ?
        ");

        $stmt->execute([
            $name,
            $name_link,
            App::vars()->id
        ]);
        $stmt = null;

        header('Location: ' . $url . '?act=view&id=' . App::vars()->id);
    } else
        echo __('error_empty_fields') . ' <a href="' . $url . '?act=edit_file&amp;id=' . App::vars()->id . '">' . __('repeat') . '</a>';
} else {
    $file_name = htmlspecialchars($res_down['rus_name']);
    echo '<div class="phdr"><b>' . $file_name . '</b></div>' .
        '<div class="list1"><form action="' . $url . '?act=edit_file&amp;id=' . App::vars()->id . '" method="post">' .
        __('name_file') . '(мах. 200):<br /><input type="text" name="text" value="' . $file_name . '"/><br />' .
        __('link_file') . ' (мах. 200):<br /><input type="text" name="name_link" value="' . $res_down['text'] . '"/><br />' .
        '<input type="submit" name="submit" value="' . __('sent') . '"/></form></div>' .
        '<div class="phdr"><a href="' . $url . '?act=view&amp;id=' . App::vars()->id . '">' . __('back') . '</a></div>';
}