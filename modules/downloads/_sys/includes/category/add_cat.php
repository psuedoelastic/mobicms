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

if (App::user()->rights == 4 || App::user()->rights >= 6) {
    if (!$id) {
        $load_cat = $files_path;
    } else {
        $req_down = App::db()->query("SELECT * FROM `download__category` WHERE `id` = '" . $id . "' LIMIT 1");
        $res_down = $req_down->fetch();
        if (!$req_down->rowCount() || !is_dir($res_down['dir'])) {
            echo __('not_found_dir') . '<a href="' . $url . '">' . __('download_title') . '</a>';
            exit;
        }
        $load_cat = $res_down['dir'];
    }
    if (isset($_POST['submit'])) {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $rus_name = isset($_POST['rus_name']) ? trim($_POST['rus_name']) : '';
        $desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';
        $user_down = isset($_POST['user_down']) ? 1 : 0;
        $format = $user_down && isset($_POST['format']) ? trim($_POST['format']) : false;
        $error = [];
        if (empty($name))
            $error[] = __('error_empty_fields');
        if (preg_match("/[^0-9a-zA-Z]+/", $name))
            $error[] = $error[] = __('error_wrong_symbols');
        if (App::user()->rights == 9 && $user_down) {
            foreach (explode(',', $format) as $value) {
                if (!in_array(trim($value), $defaultExt)) {
                    $error[] = __('extensions_ok') . ': ' . implode(', ', $defaultExt);
                    break;
                }
            }
        }
        if ($error) {
            echo $error . ' <a href="' . $url . '?act=add_cat&amp;id=' . $id . '">' . __('repeat') . '</a>';
            exit;
        }
        if (empty($rus_name))
            $rus_name = $name;
        $dir = false;
        $load_cat = $load_cat . '/' . $name;
        if (!is_dir($load_cat))
            $dir = mkdir($load_cat, 0777);
        if ($dir == true) {
            chmod($load_cat, 0777);

            $stmt = App::db()->prepare("
                INSERT INTO `download__category`
                (`refid`, `dir`, `sort`, `name`, `desc`, `field`, `text`, `rus_name`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $id,
                $load_cat,
                time(),
                $name,
                $desc,
                $user_down,
                $format,
                $rus_name
            ]);
            $cat_id = App::db()->lastInsertId();
            $stmt = null;

            echo '<div class="phdr"><b>' . __('add_cat_title') . '</b></div>' .
                '<div class="list1">' . __('add_cat_ok') . '</div>' .
                '<div class="list2"><a href="' . $url . '?id=' . $cat_id . '">' . __('continue') . '</a></div>';
        } else {
            echo __('add_cat_error') . '<a href="' . $url . 'act=add_cat&amp;id=' . $id . '">' . __('repeat') . '</a>';
            exit;
        }
    } else {
        echo '<div class="phdr"><b>' . __('add_cat_title') . '</b></div><div class="menu">' .
            '<form action="' . $url . '?act=add_cat&amp;id=' . $id . '" method="post">' .
            __('dir_name') . ' [A-Za-z0-9]:<br/><input type="text" name="name"/><br/>' .
            __('dir_name_view') . ':<br/><input type="text" name="rus_name"/><br/>' .
            __('dir_desc') . ' (max. 500):<br/><textarea name="desc" cols="24" rows="4"></textarea><br/>';
        if (App::user()->rights == 9) {
            echo '<div class="sub"><input type="checkbox" name="user_down" value="1" /> ' . __('user_download') . '<br/>' .
                __('extensions') . ':<br/><input type="text" name="format"/></div>' .
                '<div class="sub">' . __('extensions_ok') . ':<br /> ' . implode(', ', $defaultExt) . '</div>';
        }
        echo ' <input type="submit" name="submit" value="' . __('add_cat') . '"/><br/></form></div>';
    }
    echo '<div class="phdr">';
    if ($id)
        echo '<a href="' . $url . '?id=' . $id . '">' . __('back') . '</a> | ';
    echo '<a href="' . $url . '">' . __('download_title') . '</a></div>';
} else {
    header('Location: ' . App::cfg()->sys->homeurl . '404');
}