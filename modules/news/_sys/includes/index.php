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

App::view()->uri = App::router()->getUri();

App::view()->total = App::db()->query("SELECT COUNT(*) FROM `news`")->fetchColumn();

if (App::view()->total) {
    App::view()->setRawVar('list', App::db()->query("SELECT * FROM `news` ORDER BY `id` DESC " . App::db()->pagination())->fetchAll());
}

App::view()->setTemplate('index.php');
