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
$id = abs(intval(App::request()->getQuery('id', 0)));

/*
-----------------------------------------------------------------
Редактирование категорий
-----------------------------------------------------------------
*/
if (App::user()->rights == 4 || App::user()->rights >= 6) {
    $req = App::db()->query("SELECT * FROM `" . TP . "download__category` WHERE `id` = " . $id);
    $res = $req->fetch();
    if (!$req->rowCount() || !is_dir($res['dir'])) {
        echo __('not_found_dir') . ' <a href="' . $url . '">' . __('download_title') . '</a>';
        exit;
    }

    /*
    -----------------------------------------------------------------
    Сдвиг категорий
    -----------------------------------------------------------------
    */
    if (isset($_GET['up']) || isset($_GET['down'])) {
        if (isset($_GET['up'])) {
            $order = 'DESC';
            $val = '<';
        } else {
            $order = 'ASC';
            $val = '>';
        }
        $req_two = App::db()->query("SELECT * FROM `" . TP . "download__category` WHERE `refid` = '" . $res['refid'] . "' AND `sort` $val '" . $res['sort'] . "' ORDER BY `sort` $order LIMIT 1");
        if ($req_two->rowCount()) {
            $res_two = $req_two->fetch();
            App::db()->exec("UPDATE `" . TP . "download__category` SET `sort` = '" . $res_two['sort'] . "' WHERE `id` = '" . $id . "' LIMIT 1");
            App::db()->exec("UPDATE `" . TP . "download__category` SET `sort` = '" . $res['sort'] . "' WHERE `id` = '" . $res_two['id'] . "' LIMIT 1");
        }
        header('location: ' . $url . '?id=' . $res['refid']);
        exit;
    }

    /*
    -----------------------------------------------------------------
    Изменяем данные
    -----------------------------------------------------------------
    */
    if (isset($_POST['submit'])) {
        $rus_name = isset($_POST['rus_name']) ? trim($_POST['rus_name']) : '';
        if (empty($rus_name))
            $error[] = __('error_empty_fields');
        $error_format = false;
        if (App::user()->rights == 9 && isset($_POST['user_down'])) {
            $format = isset($_POST['format']) ? trim($_POST['format']) : false;
            $format_array = explode(', ', $format);
            foreach ($format_array as $value) {
                if (!in_array($value, $defaultExt))
                    $error_format .= 1;
            }
            $user_down = 1;
            $format_files = htmlspecialchars($format);
        } else {
            $user_down = 0;
            $format_files = '';
        }
        if ($error_format)
            $error[] = __('extensions_ok') . ': ' . implode(', ', $defaultExt);
        if ($error) {
            echo $error . ' <a href="' . $url . '?act=edit_cat&amp;id=' . $id . '">' . __('repeat') . '</a>';
            exit;
        }

        $desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';

        $stmt = App::db()->prepare("
            UPDATE `" . TP . "download__category` SET
            `field`    = ?,
            `text`     = ?,
            `desc`     = ?,
            `rus_name` = ?
            WHERE `id` = ?
        ");

        $stmt->execute([
            $user_down,
            $format_files,
            $desc,
            $rus_name,
            $id
        ]);
        $stmt = null;

        header('location: ' . $url . '?id=' . $id);
    } else {
        $name = htmlspecialchars($res['rus_name']);
        echo '<div class="phdr"><b>' . __('download_edit_cat') . ':</b> ' . $name . '</div>' .
            '<div class="menu"><form action="' . $url . '?act=edit_cat&amp;id=' . $id . '" method="post">' .
            __('dir_name_view') . ':<br/><input type="text" name="rus_name" value="' . $name . '"/><br/>' .
            __('dir_desc') . ' (max. 500):<br/><textarea name="desc" rows="4">' . htmlspecialchars($res['desc']) . '</textarea><br/>';
        if (App::user()->rights == 9) {
            echo '<div class="sub"><input type="checkbox" name="user_down" value="1"' . ($res['field'] ? ' checked="checked"' : '') . '/> ' . __('user_download') . '<br/>' .
                __('extensions') . ':<br/><input type="text" name="format" value="' . $res['text'] . '"/></div>' .
                '<div class="sub">' . __('extensions_ok') . ':<br /> ' . implode(', ', $defaultExt) . '</div>';
        }
        echo ' <input type="submit" name="submit" value="' . __('sent') . '"/><br/></form></div>';
    }
    echo '<div class="phdr">' .
        '<a href="' . $url . '?id=' . $id . '">' . __('back') . '</a> | ' .
        '<a href="' . $url . '">' . __('download_title') . '</a></div>';
} else {
    header('Location: ' . App::cfg()->sys->homeurl . '404');
}