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
 *
 * @module      Online
 * @author      Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version     v.1.0.0 2015-02-01
 */

defined('MOBICMS') or die('Error: restricted access');

$admin_actions =
    [
        'guests' => 'guests.php',
        'ip' => 'ip.php'
    ];

$common_actions =
    [
        'history' => 'history.php'
    ];

$query = App::router()->getQuery();
$include = __DIR__ . '/_sys/includes/';

if (isset($query[0])) {
    if (App::user()->rights > 0 && isset($admin_actions[$query[0]])) {
        $include .= $admin_actions[$query[0]];
    } elseif (isset($common_actions[$query[0]])) {
        $include .= $common_actions[$query[0]];
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
