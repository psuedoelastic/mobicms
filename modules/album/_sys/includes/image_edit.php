<?php

/**
 * @package     mobiCMS
 * @link        http://mobicms.net
 * @copyright   Copyright (C) 2008-2012 mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://mobicms.net/about
 */

defined('_IN_MOBICMS') or die('Error: restricted access');

global $img, $user;

/*
-----------------------------------------------------------------
Редактировать картинку
-----------------------------------------------------------------
*/
//TODO: Переделать запросы
if ($img && $user['id'] == App::user()->id || App::user()->rights >= 6) {
    $req = mysql_query("SELECT * FROM `" . TP . "album__files` WHERE `id` = '$img' AND `user_id` = '" . $user['id'] . "'");
    if (mysql_num_rows($req)) {
        App::view()->res = mysql_fetch_assoc($req);
        App::view()->album = App::view()->res['album_id'];
        App::view()->tmb_name = App::view()->res['tmb_name'];
        App::view()->description = App::view()->res['description'];
        echo '<div class="phdr"><a href="' . App::router()->getUri(2) . '?act=show&amp;al=' . App::view()->album . '&amp;user=' . $user['id'] . '"><b>' . __('photo_album') . '</b></a> | ' . __('image_edit') . '</div>';
        if (isset($_POST['submit'])) {
            $sql = '';
            $rotate = isset($_POST['rotate']) ? intval($_POST['rotate']) : 0;
            $brightness = isset($_POST['brightness']) ? intval($_POST['brightness']) : 0;
            $contrast = isset($_POST['contrast']) ? intval($_POST['contrast']) : 0;
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            App::view()->description = mb_substr($description, 0, 500);
            if ($rotate == 1 || $rotate == 2 || ($brightness > 0 && $brightness < 5) || ($contrast > 0 && $contrast < 5)) {
                $path = ALBUMPATH . $user['id'] . DIRECTORY_SEPARATOR;
                $handle = new upload($path . App::view()->res['img_name']);
                // Обрабатываем основное изображение
                $handle->file_new_name_body = 'img_' . time();
                if ($rotate == 1 || $rotate == 2)
                    $handle->image_rotate = ($rotate == 2 ? 90 : 270);
                if ($brightness > 0 && $brightness < 5) {
                    switch ($brightness) {
                        case 1:
                            $handle->image_brightness = -40;
                            break;
                        case 2:
                            $handle->image_brightness = -20;
                            break;
                        case 3:
                            $handle->image_brightness = 20;
                            break;
                        case 4:
                            $handle->image_brightness = 40;
                            break;
                    }
                }
                if ($contrast > 0 && $contrast < 5) {
                    switch ($contrast) {
                        case 1:
                            $handle->image_contrast = -50;
                            break;
                        case 2:
                            $handle->image_contrast = -25;
                            break;
                        case 3:
                            $handle->image_contrast = 25;
                            break;
                        case 4:
                            $handle->image_contrast = 50;
                            break;
                    }
                }
                $handle->process($path);
                $img_name = $handle->file_dst_name;
                if ($handle->processed) {
                    // Обрабатываем превьюшку
                    $handle->file_new_name_body = 'tmb_' . time();
                    if ($rotate == 1 || $rotate == 2)
                        $handle->image_rotate = ($rotate == 2 ? 90 : 270);
                    if ($brightness > 0 && $brightness < 5) {
                        switch ($brightness) {
                            case 1:
                                $handle->image_brightness = -40;
                                break;
                            case 2:
                                $handle->image_brightness = -20;
                                break;
                            case 3:
                                $handle->image_brightness = 20;
                                break;
                            case 4:
                                $handle->image_brightness = 40;
                                break;
                        }
                    }
                    if ($contrast > 0 && $contrast < 5) {
                        switch ($contrast) {
                            case 1:
                                $handle->image_contrast = -50;
                                break;
                            case 2:
                                $handle->image_contrast = -25;
                                break;
                            case 3:
                                $handle->image_contrast = 25;
                                break;
                            case 4:
                                $handle->image_contrast = 50;
                                break;
                        }
                    }
                    $handle->image_resize = true;
                    $handle->image_x = 80;
                    $handle->image_y = 80;
                    $handle->image_ratio_no_zoom_in = true;
                    $handle->process($path);
                    $tmb_name = $handle->file_dst_name;
                    App::view()->tmb_name = $tmb_name;
                }
                $handle->clean();
                @unlink(ALBUMPATH . $user['id'] . DIRECTORY_SEPARATOR . App::view()->res['img_name']);
                @unlink(ALBUMPATH . $user['id'] . DIRECTORY_SEPARATOR . App::view()->res['tmb_name']);
                $sql = "`img_name` = '" . mysql_real_escape_string($img_name) . "', `tmb_name` = '" . mysql_real_escape_string($tmb_name) . "',";
            }
            mysql_query("UPDATE `" . TP . "album__files` SET $sql
                `description` = '" . mysql_real_escape_string(App::view()->description) . "'
                WHERE `id` = '$img'
            ");
            App::view()->save = 1;
        }
        App::view()->link = App::router()->getUri(2);
        App::view()->setTemplate('image_edit.php');
    } else {
        echo __('error_wrong_data');
    }
}