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

$uri = App::router()->getUri(2);
App::view()->uri = App::router()->getUri(2);
$id = abs(intval(App::request()->getQuery('id', 0)));

if ($id) {
    $query = App::db()->query("SELECT * FROM `forum__` WHERE `refid` = " . $id . " AND `type` = 'r' ORDER BY `realid` ASC");
    $cat = App::db()->query("SELECT `text` FROM `forum__` WHERE `id` = " . $id . " AND `type` = 'f'")->fetch();
    App::view()->title = $cat['text'] . ': ' . __('section_list');
} else {
    $query = App::db()->query("SELECT * FROM `forum__` WHERE `type` = 'f' ORDER BY `realid` ASC");
    App::view()->title = __('category_list');
}

App::view()->total = $query->rowCount();

if (App::view()->total) {
    foreach ($query as $val) {
        $val['counter'] = App::db()->query("SELECT COUNT(*) FROM `forum__` WHERE `type` = 'r' AND `refid` = '" . $val['id'] . "'")->fetchColumn();
        App::view()->list[] = $val;
    }
}

App::view()->setTemplate('admin_sections.php');