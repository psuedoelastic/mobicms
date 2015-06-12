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
//TODO: переделать счетчик на тех, у кого уже регистрация подтверждена
App::view()->total = App::db()->query("SELECT COUNT(*) FROM `user__` ")->fetchColumn();

App::view()->list = App::db()->query("
    SELECT *
    FROM `user__`
    ORDER BY `id` ASC" .
    App::db()->pagination()
)->fetchAll();

App::view()->setTemplate('user_list.php');