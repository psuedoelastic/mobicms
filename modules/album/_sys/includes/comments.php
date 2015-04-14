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

//TODO: Переделать SQL запросы

$url = App::router()->getUri(1);

// Проверяем наличие комментируемого объекта
$req_obj = mysql_query("SELECT * FROM `" . TP . "album__files` WHERE `id` = '$img'");
if (mysql_num_rows($req_obj)) {
    $res_obj = mysql_fetch_assoc($req_obj);

    /*
    -----------------------------------------------------------------
    Получаем данные владельца Альбома
    -----------------------------------------------------------------
    */
    //TODO: Переделать на класс Users
    $owner = Functions::getUser($res_obj['user_id']);
    if (!$owner) {
        echo __('user_does_not_exist');
        exit;
    }

    /*
    -----------------------------------------------------------------
    Показываем выбранную картинку
    -----------------------------------------------------------------
    */
    unset($_SESSION['ref']);
    $req_a = mysql_query("SELECT * FROM `" . TP . "album__cat` WHERE `id` = '" . $res_obj['album_id'] . "'");
    $res_a = mysql_fetch_assoc($req_a);
    if ($res_a['access'] == 1 && $owner['id'] != App::user()->id && App::user()->rights < 6) {
        // Если доступ закрыт
        echo __('access_forbidden') .
            '<div class="phdr"><a href="' . $url . '?act=list&amp;user=' . $owner['id'] . '">' . __('album_list') . '</a></div>';
        exit;
    }
    $context_top = '<div class="phdr"><a href="' . $url . '"><b>' . __('photo_albums') . '</b></a> | ' .
        '<a href="' . $url . '?act=list&amp;user=' . $owner['id'] . '">' . __('personal_2') . '</a></div>' .
        '<div class="menu"><a href="' . $url . '?act=show&amp;al=' . $res_obj['album_id'] . '&amp;img=' . $img . '&amp;user=' . $owner['id'] . '&amp;view"><img src="' . App::cfg()->sys->homeurl . 'files/users/album/' . $owner['id'] . '/' . $res_obj['tmb_name'] . '" /></a>';
    if (!empty($res_obj['description']))
        $context_top .= '<div class="gray">' . Functions::smilies(htmlspecialchars($res_obj['description'])) . '</div>';
    $context_top .= '<div class="sub">' .
        '<a href="profile.php?user=' . $owner['id'] . '"><b>' . $owner['nickname'] . '</b></a> | ' .
        '<a href="' . $url . '?act=show&amp;al=' . $res_a['id'] . '&amp;user=' . $owner['id'] . '">' . htmlspecialchars($res_a['name']) . '</a>';
    if ($res_obj['access'] == 4 || App::user()->rights >= 7) {
        $context_top .= Album::vote($res_obj) .
            '<div class="gray">' . __('count_views') . ': ' . $res_obj['views'] . ', ' . __('count_downloads') . ': ' . $res_obj['downloads'] . '</div>' .
            '<a href="' . $url . '?act=image_download&amp;img=' . $res_obj['id'] . '">' . __('download') . '</a>';
    }
    $context_top .= '</div></div>';

    /*
    -----------------------------------------------------------------
    Параметры комментариев
    -----------------------------------------------------------------
    */
    $arg = [
        'comments_table' => 'cms_album_comments',          // Таблица с комментариями
        'object_table'   => 'cms_album_files',             // Таблица комментируемых объектов
        'script'         => $url . '?act=comments',  // Имя скрипта (с параметрами вызова)
        'sub_id_name'    => 'img',                         // Имя идентификатора комментируемого объекта
        'sub_id'         => $img,                          // Идентификатор комментируемого объекта
        'owner'          => $owner['id'],                  // Владелец объекта
        'owner_delete'   => true,                          // Возможность владельцу удалять комментарий
        'owner_reply'    => true,                          // Возможность владельцу отвечать на комментарий
        'owner_edit'     => false,                         // Возможность владельцу редактировать комментарий
        'title'          => __('comments'),               // Название раздела
        'context_top'    => $context_top,                  // Выводится вверху списка
        'context_bottom' => ''                             // Выводится внизу списка
    ];

    /*
    -----------------------------------------------------------------
    Ставим метку прочтения
    -----------------------------------------------------------------
    */
    if (App::user()->id == $user['id'] && $res_obj['unread_comments'])
        mysql_query("UPDATE `" . TP . "album__files` SET `unread_comments` = '0' WHERE `id` = '$img' LIMIT 1");

    /*
    -----------------------------------------------------------------
    Показываем комментарии
    -----------------------------------------------------------------
    */
    $comm = new Comments($arg);

    /*
    -----------------------------------------------------------------
    Обрабатываем метки непрочитанных комментариев
    -----------------------------------------------------------------
    */
    if ($comm->added)
        mysql_query("UPDATE `" . TP . "album__files` SET `unread_comments` = '1' WHERE `id` = '$img' LIMIT 1");
} else {
    echo __('error_wrong_data');
}