<?php
/**
 * mobiCMS Content Management System (http://mobicms.net)
 *
 * For copyright and license information, please see the LICENSE.md
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://mobicms.net mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 * @license     LICENSE.md (see attached file)
 */

defined('MOBICMS') or die('Error: restricted access');

App::autoload()->import('Forum', __DIR__ . '/_sys/classes/Forum.php');

$admin_actions =
    [
        'admin/add'                 => 'admin/add.php',
        'admin/clean'               => 'admin/clean.php',
        'admin/clean/hidden_posts'  => 'admin/hiddenPosts.php',
        'admin/clean/hidden_topics' => 'admin/hiddenTopics.php',
        'admin/down'                => 'admin/moveDown.php',
        'admin/edit'                => 'admin/edit.php',
        'admin/sections'            => 'admin/sections.php',
        'admin/stat'                => 'admin/stat.php',
        'admin/up'                  => 'admin/moveUp.php',
    ];

$moder_actions =
    [
        'close'    => 'actions/close.php',
        'curators' => 'actions/curators.php',
        'pin'      => 'actions/pin.php',
    ];

$users_action =
    [
        'new_topic' => 'actions/new_topic.php',
        'say'       => 'say.php',
    ];

$common_actions = //TODO: распределить
    [
        'addfile'  => 'addfile.php',
        'addvote'  => 'addvote.php',
        'curators' => 'curators.php',
        'deltema'  => 'deltema.php',
        'delvote'  => 'delvote.php',
        'editpost' => 'editpost.php',
        'editvote' => 'editvote.php',
        'file'     => 'file.php',
        'files'    => 'files.php',
        'filter'   => 'filter.php',
        'loadtem'  => 'loadtem.php',
        'massdel'  => 'massdel.php',
        'per'      => 'per.php',
        'post'     => 'post.php',
        'ren'      => 'ren.php',
        'restore'  => 'restore.php',
        'tema'     => 'tema.php',
        'users'    => 'users.php',
        'vip'      => 'vip.php',
        'vote'     => 'vote.php',
        'who'      => 'who.php'
    ];

$query = App::router()->getQuery();
$include = __DIR__ . '/_sys/includes/';

if (isset($query[0])) {
    $act = implode('/', $query);

    if (App::user()->rights >= 7 && isset($admin_actions[$act])) {
        $include .= $admin_actions[$act];
    } elseif ((App::user()->rights >= 6 || App::user()->rights == 3) && isset($moder_actions[$act])) {
        $include .= $moder_actions[$act];
    } elseif (App::user()->id && isset($users_action[$act])) {
        $include .= $users_action[$act];
    } elseif (isset($common_actions[$act])) {
        $include .= $common_actions[$act];
    } else {
        $include = false;
    }
} else {
    $include .= 'index.php';
}

if ($include && is_file($include)) {
    require_once($include);
} else {
    header('Location: ' . App::cfg()->sys->homeurl . '404/');
}
