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
Дополнительные файлы
-----------------------------------------------------------------
*/
$req_down = App::db()->query("SELECT * FROM `" . TP . "download__files` WHERE `id` = '" . App::vars()->id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();
if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name']) || (App::user()->rights < 6 && App::user()->rights != 4)) {
    echo '<a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}
$del = isset($_GET['del']) ? abs(intval($_GET['del'])) : false;
$edit = isset($_GET['edit']) ? abs(intval($_GET['edit'])) : false;
if ($edit) {
    /*
    -----------------------------------------------------------------
    Изменяем файл
    -----------------------------------------------------------------
    */
    $name_link = isset($_POST['name_link']) ? htmlspecialchars(mb_substr($_POST['name_link'], 0, 200)) : null;
    $req_file_more = App::db()->query("SELECT `rus_name` FROM `" . TP . "download__more` WHERE `id` = '$edit' LIMIT 1");
    if ($name_link && $req_file_more->rowCount() && isset($_POST['submit'])) {
        $stmt = App::db()->prepare("
            UPDATE `" . TP . "download__more` SET
            `rus_name` = ?
            WHERE `id` = ?
        ");

        $stmt->execute([
            $name_link,
            $edit
        ]);
        $stmt = null;

        header('Location: ' . $url . '?act=files_more&id=' . App::vars()->id);
    } else {
        $res_file_more = $req_file_more->fetch();
        echo '<div class="phdr"><b>' . htmlspecialchars($res_down['rus_name']) . '</b></div>' .
            '<div class="gmenu"><b>' . __('edit_file') . '</b></div>' .
            '<div class="list1"><form action="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '&amp;edit=' . $edit . '"  method="post">' .
            __('link_file') . ' (мах. 200)<span class="red">*</span>:<br />' .
            '<input type="text" name="name_link" value="' . $res_file_more['rus_name'] . '"/><br />' .
            '<input type="submit" name="submit" value="' . __('sent') . '"/></form>' .
            '</div><div class="phdr"><a href="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '">' . __('back') . '</a></div>';
    }
} else if ($del) {
    /*
    -----------------------------------------------------------------
    Удаление файла
    -----------------------------------------------------------------
    */
    $req_file_more = App::db()->query("SELECT `name` FROM `" . TP . "download__more` WHERE `id` = '$del'");
    if ($req_file_more->rowCount() && isset($_GET['yes'])) {
        $res_file_more = $req_file_more->fetch();
        if (is_file($res_down['dir'] . '/' . $res_file_more['name']))
            unlink($res_down['dir'] . '/' . $res_file_more['name']);
        App::db()->exec("DELETE FROM `" . TP . "download__more` WHERE `id` = '$del' LIMIT 1");

        header('Location: ' . $url . '?act=files_more&id=' . App::vars()->id);
    } else {
        echo '<div class="rmenu">' . __('delete_confirmation') . '<br /> <a href="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '&amp;del=' . $del . '&amp;yes">' . __('delete') . '</a> | <a href="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '">' . __('cancel') . '</a></div>';
    }
} else if (isset($_POST['submit'])) {
    /*
    -----------------------------------------------------------------
    Выгружаем файл
    -----------------------------------------------------------------
    */
    $error = [];
    $link_file = isset($_POST['link_file']) ? str_replace('./', '_', trim($_POST['link_file'])) : null;
    $do_file = false;
    if ($link_file) {
        if (mb_substr($link_file, 0, 7) !== 'http://')
            $error[] = __('error_link_import');
        else {
            $link_file = str_replace('http://', '', $link_file);
            if ($link_file) {
                $do_file = true;
                $fname = basename($link_file);
                $fsize = 0;
            } else {
                $error[] = __('error_link_import');
            }
        }
        if ($error) {
            $error[] = '<a href="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '">' . __('repeat') . '</a>';
            echo $error;
            exit;
        }
    } elseif ($_FILES['fail']['size'] > 0) {
        $do_file = true;
        $fname = strtolower($_FILES['fail']['name']);
        $fsize = $_FILES['fail']['size'];
    }
    if ($do_file) {
        $new_file = isset($_POST['new_file']) ? trim($_POST['new_file']) : null;
        $name_link = isset($_POST['name_link']) ? htmlspecialchars(mb_substr($_POST['name_link'], 0, 200)) : null;
        $ext = explode(".", $fname);
        if (!empty($new_file)) {
            $fname = strtolower($new_file . '.' . $ext[1]);
            $ext = explode(".", $fname);
        }
        if (empty($name_link))
            $error[] = __('error_empty_fields');
        if ($fsize > 1024 * App::cfg()->sys->filesize && !$link_file)
            $error[] = __('error_file_size') . ' ' . App::cfg()->sys->filesize . 'kb.';
        if (!in_array($ext[(count($ext) - 1)], $defaultExt))
            $error[] = __('error_file_ext') . ': ' . implode(', ', $defaultExt);
        if (strlen($fname) > 100)
            $error[] = __('error_file_name_size ');
        if (preg_match("/[^\da-zA-Z_\-.]+/", $fname))
            $error[] = __('error_file_symbols');
        if ($error) {
            $error[] = '<a href="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '">' . __('repeat') . '</a>';
            echo $error;
        } else {
            $newFile = 'file' . App::vars()->id . '_' . $fname;
            if (file_exists($res_down['dir'] . '/' . $newFile)) $fname = 'file' . App::vars()->id . '_' . time() . $fname;
            else $fname = $newFile;
            if ($link_file) {
                $up_file = copy('http://' . $link_file, "$res_down[dir]/$fname");
                $fsize = filesize("$res_down[dir]/$fname");
            } else {
                $up_file = move_uploaded_file($_FILES["fail"]["tmp_name"], "$res_down[dir]/$fname");
            }
            if ($up_file == true) {
                @chmod("$fname", 0777);
                @chmod("$res_down[dir]/$fname", 0777);
                echo '<div class="gmenu">' . __('upload_file_ok') . '<br />' .
                    '<a href="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '">' . __('upload_file_more') . '</a> | <a href="' . $url . '?id=' . App::vars()->id . '&amp;act=view">' . __('back') . '</a></div>';

                $stmt = App::db()->prepare("
                    INSERT INTO `" . TP . "download__more`
                    (`refid`, `time`, `name`, `rus_name`, `size`)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    App::vars()->id,
                    time(),
                    $fname,
                    $name_link,
                    intval($fsize)
                ]);
                $stmt = null;
            } else
                echo '<div class="rmenu">' . __('upload_file_no') . '<br /><a href="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '">' . __('repeat') . '</a></div>';
        }
    } else
        echo '<div class="rmenu">' . __('upload_file_no') . '<br /><a href="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '">' . __('repeat') . '</a></div>';
} else {
    /*
    -----------------------------------------------------------------
    Выводим форму
    -----------------------------------------------------------------
    */
    echo '<div class="phdr"><b>' . __('files_more') . ':</b> ' . htmlspecialchars($res_down['rus_name']) . '</div>' .
        '<div class="menu"><form action="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '"  method="post" enctype="multipart/form-data">' .
        __('select_file') . '<span class="red">*</span>::<br /><input type="file" name="fail"/><br />' .
        __('or_link_to_it') . ':<br /><input type="post" name="link_file" value=""/><br />' .
        __('save_name_file') . ':<br /><input type="text" name="new_file"/><br />' .
        __('link_file') . ' (мах. 200)<span class="red">*</span>:<br />' .
        '<input type="text" name="name_link" value="' . __('download_file_more') . '"/><br />' .
        '<input type="submit" name="submit" value="' . __('upload') . '"/>' .
        '</form></div>' .
        '<div class="phdr"><small>' . __('file_size_faq') . ' ' . App::cfg()->sys->filesize . 'kb<br />' .
        __('extensions') . ': ' . implode(', ', $defaultExt) . ($set_down['screen_resize'] ? '<br />' . __('add_screen_faq') : '') . '</small></div>';
    /*
    -----------------------------------------------------------------
    Дополнительные файлы
    -----------------------------------------------------------------
    */
    $req_file_more = App::db()->query("SELECT * FROM `" . TP . "download__more` WHERE `refid` = " . App::vars()->id);
    $total_file = $req_file_more->rowCount();
    $i = 0;
    if ($total_file) {
        while ($res_file_more = $req_file_more->fetch()) {
            $format = explode('.', $res_file_more['name']);
            $format_file = strtolower($format[count($format) - 1]);
            echo(($i++ % 2) ? '<div class="list2">' : '<div class="list1">');
            echo '<b>' . $res_file_more['rus_name'] . '</b>' .
                '<div class="sub">' . $res_file_more['name'] . ' (' . Download::displayFileSize($res_file_more['size']) . '), ' . functions::displayDate($res_file_more['time']) . '<br />' .
                '<a href="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '&amp;edit=' . $res_file_more['id'] . '">' . __('edit') . '</a> | ' .
                '<span class="red"><a href="' . $url . '?act=files_more&amp;id=' . App::vars()->id . '&amp;del=' . $res_file_more['id'] . '">' . __('delete') . '</a></span></div></div>';
        }
        echo '<div class="phdr">' . __('total') . ': ' . $total_file . '</div>';
    }
    echo '<p><a href="' . $url . '?act=view&amp;id=' . App::vars()->id . '">' . __('back') . '</a></p>';
}