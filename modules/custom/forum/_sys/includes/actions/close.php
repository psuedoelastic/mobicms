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

$id = abs(intval(App::request()->getQuery('id', 0)));

if ((App::user()->rights != 3 && App::user()->rights < 6) || !$id) {
    header('Location: ' . App::router()->getUri(1));
    exit;
}
if (App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `id` = " . $id . " AND `type` = 't'")->fetchColumn()) {
    if (isset($_GET['closed'])) {
        App::db()->exec("UPDATE `" . TP . "forum__` SET `edit` = '1' WHERE `id` = " . $id);
    } else {
        App::db()->exec("UPDATE `" . TP . "forum__` SET `edit` = '0' WHERE `id` = " . $id);
    }
}

header("Location: " . App::router()->getUri(1) . "?id=" . $id);
