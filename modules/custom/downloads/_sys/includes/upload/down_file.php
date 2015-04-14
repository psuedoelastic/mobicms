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

$req = App::db()->query("SELECT * FROM `" . TP . "download__category` WHERE `id` = '" . App::vars()->id . "' LIMIT 1");
$res = $req->fetch();
if ($req->rowCount() && is_dir($res['dir'])) {
    if (($res['field'] && App::user()->id) || (App::user()->rights == 4 || App::user()->rights >= 6)) {
        $al_ext = $res['field'] ? explode(', ', $res['text']) : $defaultExt;
        if (isset($_POST['submit'])) {
            $load_cat = $res['dir'];
            $do_file = false;
            if ($_FILES['fail']['size'] > 0) {
                $do_file = true;
                $fname = strtolower($_FILES['fail']['name']);
                $fsize = $_FILES['fail']['size'];
            }
            if ($do_file) {
                $error = [];
                $new_file = isset($_POST['new_file']) ? trim($_POST['new_file']) : null;
                $name = isset($_POST['text']) ? trim($_POST['text']) : null;
                $name_link = isset($_POST['name_link']) ? htmlspecialchars(mb_substr($_POST['name_link'], 0, 200)) : null;
                $text = isset($_POST['opis']) ? trim($_POST['opis']) : null;
                $ext = explode(".", $fname);
                if (!empty($new_file)) {
                    $fname = strtolower($new_file . '.' . $ext[1]);
                    $ext = explode(".", $fname);
                }
                if (empty($name))
                    $name = $fname;
                if (empty($name_link))
                    $error[] = __('error_empty_fields');
                if ($fsize > 1024 * App::cfg()->sys->filesize)
                    $error[] = __('error_file_size') . ' ' . App::cfg()->sys->filesize . 'kb.';
                if (!in_array($ext[(count($ext) - 1)], $al_ext))
                    $error[] = __('error_file_ext') . ': ' . implode(', ', $al_ext);
                if (strlen($fname) > 100)
                    $error[] = __('error_file_name_size ');
                if (preg_match("/[^\da-z_\-.]+/", $fname))
                    $error[] = __('error_file_symbols');
                if ($error) {
                    $error[] = '<a href="' . $url . '?act=down_file&amp;id=' . App::vars()->id . '">' . __('repeat') . '</a>';
                    echo $error;
                } else {
                    if (file_exists("$load_cat/$fname"))
                        $fname = time() . $fname;
                    if ((move_uploaded_file($_FILES["fail"]["tmp_name"], "$load_cat/$fname")) == true) {
                        echo '<div class="phdr"><b>' . __('download_upload_file') . ': ' . htmlspecialchars($res['rus_name']) . '</b></div>';
                        @chmod("$fname", 0777);
                        @chmod("$load_cat/$fname", 0777);
                        echo '<div class="gmenu">' . __('upload_file_ok');
                        if ($set_down['mod'] && (App::user()->rights < 6 && App::user()->rights != 4)) {
                            echo __('upload_file_mod');
                            $type = 3;
                        } else $type = 2;
                        echo '</div>';

                        $stmt = App::db()->prepare("
                            INSERT INTO `" . TP . "download__files`
                            (`refid`, `dir`, `time`, `name`, `text`, `rus_name`, `type`, `user_id`, `about`, `desc`)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '')
                        ");

                        $stmt->execute([
                            App::vars()->id,
                            $load_cat,
                            time(),
                            $fname,
                            $name_link,
                            mb_substr($name, 0, 200),
                            $type,
                            App::user()->id,
                            $text
                        ]);
                        $file_id = App::db()->lastInsertId();
                        $stmt = null;

                        $handle = new upload($_FILES['screen']);
                        if ($handle->uploaded) {
                            if (mkdir($screens_path . '/' . $file_id, 0777) == true)
                                @chmod($screens_path . '/' . $file_id, 0777);
                            $handle->file_new_name_body = $file_id;
                            $handle->allowed = [
                                'image/jpeg',
                                'image/gif',
                                'image/png'
                            ];
                            $handle->file_max_size = 1024 * App::cfg()->sys->filesize;
                            $handle->file_overwrite = true;
                            if ($set_down['screen_resize']) {
                                $handle->image_resize = true;
                                $handle->image_x = 240;
                                $handle->image_ratio_y = true;
                            }
                            $handle->process($screens_path . '/' . $file_id . '/');
                            if ($handle->processed) {
                                echo '<div class="gmenu">' . __('upload_screen_ok') . '</div>';
                            } else
                                echo '<div class="rmenu">' . __('upload_screen_no') . ': ' . $handle->error . '</div>';
                        } else
                            echo '<div class="rmenu">' . __('upload_screen_no') . '</div>';
                        if (!$set_down['mod'] || App::user()->rights > 6 || App::user()->rights == 4) {
                            echo '<div class="menu"><a href="' . $url . '?act=view&amp;id=' . $file_id . '">' . __('continue') . '</a></div>';
                            $dirid = App::vars()->id;
                            $sql = '';
                            $i = 0;
                            while ($dirid != '0' && $dirid != "") {
                                $res_down = App::db()->query("SELECT `refid` FROM `" . TP . "download__category` WHERE `id` = '$dirid' LIMIT 1")->fetch();
                                if ($i)
                                    $sql .= ' OR ';
                                $sql .= '`id` = \'' . $dirid . '\'';
                                $dirid = $res_down['refid'];
                                ++$i;
                            }
                            App::db()->exec("UPDATE `" . TP . "download__category` SET `total` = (`total`+1) WHERE $sql");
                        }
                        echo '<div class="phdr"><a href="' . $url . '?act=down_file&amp;id=' . App::vars()->id . '">' . __('upload_file_more') . '</a> | <a href="' . $url . '?id=' . App::vars()->id . '">' . __('back') . '</a></div>';
                    } else
                        echo '<div class="rmenu">' . __('upload_file_no') . '<br /><a href="' . $url . '?act=down_file&amp;id=' . App::vars()->id . '">' . __('repeat') . '</a></div>';
                }
            } else
                echo '<div class="rmenu">' . __('upload_file_no') . '<br /><a href="' . $url . '?act=down_file&amp;id=' . App::vars()->id . '">' . __('repeat') . '</a></div>';
        } else {
            echo '<div class="phdr"><b>' . __('download_upload_file') . ': ' . htmlspecialchars($res['rus_name']) . '</b></div>' .
                '<div class="list1"><form action="' . $url . '?act=down_file&amp;id=' . App::vars()->id . '" method="post" enctype="multipart/form-data">' .
                __('select_file') . '<span class="red">*</span>:<br /><input type="file" name="fail"/><br />' .
                __('save_name_file') . ':<br /><input type="text" name="new_file"/><br />' .
                __('screen_file') . ':<br /><input type="file" name="screen"/><br />' .
                __('name_file') . ' (мах. 200):<br /><input type="text" name="text"/><br />' .
                __('link_file') . ' (мах. 200)<span class="red">*</span>:<br /><input type="text" name="name_link" value="' . __('download_file') . '"/><br />' .
                __('dir_desc') . ' (max. 500)<br /><textarea name="opis"></textarea><br />' .
                '<input type="submit" name="submit" value="' . __('upload') . '"/></form></div>' .
                '<div class="phdr"><small>' . __('file_size_faq') . ' ' . App::cfg()->sys->filesize . 'kb<br />' .
                __('extensions') . ': ' . implode(', ', $al_ext) . ($set_down['screen_resize'] ? '<br />' . __('add_screen_faq') : '') . '</small></div>' .
                '<p><a href="' . $url . '?id=' . App::vars()->id . '">' . __('back') . '</a></p>';
        }
    } else
        echo __('access_forbidden ') . ' <a href="' . $url . '?id=' . App::vars()->id . '">' . __('back') . '</a>';
} else {
    echo __('not_found_dir') . '<a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}