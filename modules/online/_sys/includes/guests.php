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

App::view()->uri = App::router()->getUri();
App::view()->total = App::db()->query("SELECT COUNT(*) FROM `system__sessions` WHERE `user_id` = 0 AND `session_timestamp`  > " . (time() - 300))->fetchColumn();

if (App::view()->total) {
    App::view()->list = App::db()->query("
        SELECT
            `user_id` AS `id`,
            `session_timestamp` AS `last_visit`,
            `ip`,
            `ip_via_proxy`,
            `user_agent`,
            `place`,
            `views`,
            `movings`
        FROM
            `system__sessions`
        WHERE
            `user_id` = 0 AND `session_timestamp`  > " . (time() - 300) . "
        ORDER BY
            `views` DESC" . App::db()->pagination()
    )->fetchAll();
}

App::view()->list_header = __('guests');
App::view()->setTemplate('guests.php');
