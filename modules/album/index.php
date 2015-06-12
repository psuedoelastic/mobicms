<?php
/**
 * mobiCMS Content Management System (http://mobicms.net)
 *
 * For copyright and license information, please see the LICENSE.txt
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://mobicms.net mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 *
 * @module      Photo Albums
 * @author      Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version     v.1.0.0 2015-02-01
 */

defined('MOBICMS') or die('Error: restricted access');

define('ALBUMPATH', FILES_PATH . 'users' . DS . 'album' . DS);
$max_album = 10;
$max_photo = 200;
$al = isset($_REQUEST['al']) ? abs(intval($_REQUEST['al'])) : null;
$img = isset($_REQUEST['img']) ? abs(intval($_REQUEST['img'])) : null;
$user = isset($_GET['user']) ? abs(intval($_GET['user'])) : null;

/*
-----------------------------------------------------------------
Закрываем от неавторизованных юзеров
-----------------------------------------------------------------
*/
// Ограничиваем доступ к Альбомам
$error = '';
if (!App::cfg()->sys->acl_albums && App::user()->rights < 7) {
    $error = __('section_closed');
} elseif (App::cfg()->sys->acl_albums == 1 && !App::user()->id) {
    $error = __('access_guest_forbidden');
}

if ($error) {
    echo $error;
    exit;
}


App::view()->img = $img;
//TODO: Переделать на класс Users
$user = Users::get($user);
App::view()->user = $user;
App::view()->al = $al;

/*
-----------------------------------------------------------------
Переключаем режимы работы
-----------------------------------------------------------------
*/
$actions =
    [
        'comments'       => 'comments.php',
        'delete'         => 'delete.php',
        'edit'           => 'edit.php',
        'image_delete'   => 'image_delete.php',
        'image_download' => 'image_download.php',
        'image_edit'     => 'image_edit.php',
        'image_move'     => 'image_move.php',
        'image_upload'   => 'image_upload.php',
        'list'           => 'list.php',
        'new'            => 'new.php',
        'new_comm'       => 'new_comm.php',
        'show'           => 'show.php',
        'sort'           => 'sort.php',
        'users'          => 'users.php',
        'vote'           => 'vote.php',
    ];

if (isset($actions[App::vars()->act]) && is_file(__DIR__ . DS . '_sys' . DS . 'includes' . DS . $actions[App::vars()->act])) {
    require_once(__DIR__ . DS . '_sys' . DS . 'includes' . DS . $actions[App::vars()->act]);
} else {
    App::view()->new = App::db()->query("SELECT COUNT(*) FROM `album__files` WHERE `time` > '" . (time() - 259200) . "' AND `access` > '1'")->fetchColumn();
    App::view()->count_m = App::db()->query("SELECT COUNT(DISTINCT `user_id`) FROM `album__files` LEFT JOIN `user__` ON `album__files`.`user_id` = `user__`.`id` WHERE `user__`.`sex` = 'm'")->fetchColumn();
    App::view()->count_w = App::db()->query("SELECT COUNT(DISTINCT `user_id`) FROM `album__files` LEFT JOIN `user__` ON `album__files`.`user_id` = `user__`.`id` WHERE `user__`.`sex` = 'w'")->fetchColumn();
    App::view()->count_my = App::db()->query("SELECT COUNT(*) FROM `album__files` WHERE `user_id` = " . App::user()->id)->fetchColumn();

    App::view()->link = App::router()->getUri(2);
    App::view()->setTemplate('index.php');
}