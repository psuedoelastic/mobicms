<?php
/*
 * mobiCMS Content Management System (http://mobicms.net)
 *
 * For copyright and license information, please see the LICENSE.md
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://mobicms.net mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 * @license     LICENSE.md (see attached file)
 *
 * @module      News
 * @author      Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version     v.1.0.0 2015-02-01
 */

defined('MOBICMS') or die('Error: restricted access');

$admin_actions =
    [
        'add'    => 'add.php',
        'admin'  => 'admin.php',
        'clean'  => 'clean.php',
        'delete' => 'delete.php',
        'edit'   => 'edit.php',
    ];

$common_actions = [];

$query = App::router()->getQuery(0);
$include = __DIR__ . '/_sys/includes/';

if (!empty($query)) {
    if (App::user()->rights >= 7 && isset($admin_actions[$query])) {
        $include .= $admin_actions[$query];
    } elseif (isset($common_actions[$query])) {
        $include .= $common_actions[$query];
    } else {
        $include = false;
    }
} else {
    $include .= 'index.php';
}

if ($include && is_file($include)) {
    require_once $include;
} else {
    header('Location: ' . App::cfg()->sys->homeurl . '404/');
}
