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
 */

defined('MOBICMS') or die('Error: restricted access');

if (isset($_SESSION['ref'])) {
    unset($_SESSION['ref']);
}

App::view()->total_users = App::db()->query('SELECT COUNT(*) FROM `user__`')->fetchColumn();
App::view()->setTemplate('index.php');
