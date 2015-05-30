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
 * @module      Help System
 * @author      Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version     v.1.0.0 2015-02-01
 */

defined('MOBICMS') or die('Error: restricted access');

$query = App::router()->getQuery();

if (isset($query[0])) {
    switch ($query[0]) {
        case 'avatars':
            require_once __DIR__ . '/_sys/includes/avatars.php';
            break;

        case 'rules':
            App::view()->setTemplate('rules.php');
            break;

        default:
            header('Location ' . App::cfg()->sys->homeurl . '404/');
            exit;
    }
} else {
    App::view()->setTemplate('index.php');
}
