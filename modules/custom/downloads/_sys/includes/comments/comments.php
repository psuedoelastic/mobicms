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
Комментарии
-----------------------------------------------------------------
*/
//TODO: Переделать на получение настроек из таблицы модулей
if (!App::cfg()->sys->acl_downloads_comm && App::user()->rights < 7) {
    echo __('comments_cloded') . ' <a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}
$req_down = App::db()->query("SELECT * FROM `" . TP . "download__files` WHERE `id` = '" . $id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();
if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name']) || ($res_down['type'] == 3 && App::user()->rights < 6 && App::user()->rights != 4)) {
    echo __('not_found_file') . ' <a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}
//TODO: Переделать на получение настроек из таблицы модулей
if (!App::cfg()->sys->acl_downloads_comm)
    echo '<div class="rmenu">' . __('comments_cloded') . '</div>';
$title_pages = htmlspecialchars(mb_substr($res_down['rus_name'], 0, 30));
$textl = __('comments') . ': ' . (mb_strlen($res_down['rus_name']) > 30 ? $title_pages . '...' : $title_pages);
/*
-----------------------------------------------------------------
Параметры комментариев
-----------------------------------------------------------------
*/
$arg = [
    'object_comm_count' => 'total', // Поле с числом комментариев
    'comments_table'    => 'cms_download_comments', // Таблица с комментариями
    'object_table'      => 'cms_download_files', // Таблица комментируемых объектов
    'script'            => $url . '?act=comments', // Имя скрипта (с параметрами вызова)
    'sub_id_name'       => 'id', // Имя идентификатора комментируемого объекта
    'sub_id'            => $id, // Идентификатор комментируемого объекта
    'owner'             => false, // Владелец объекта
    'owner_delete'      => false, // Возможность владельцу удалять комментарий
    'owner_reply'       => false, // Возможность владельцу отвечать на комментарий
    'owner_edit'        => false, // Возможность владельцу редактировать комментарий
    'title'             => __('comments'), // Название раздела
    'context_top'       => '<div class="phdr"><b>' . $textl . '</b></div>', // Выводится вверху списка
    'context_bottom'    => '<p><a href="' . $url . '?act=view&amp;id=' . $id . '">' . __('back') . '</a></p>' // Выводится внизу списка
];
/*
-----------------------------------------------------------------
Показываем комментарии
-----------------------------------------------------------------
*/
$comm = new comments($arg);