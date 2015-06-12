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

if ($id) {
    $req1 = App::db()->query("SELECT * FROM `forum__` WHERE `id` = " . $id);
    if ($req1->rowCount()) {
        $res1 = $req1->fetch();
        $sort = $res1['realid'];
        $req2 = App::db()->query("SELECT * FROM `forum__` WHERE `type` = '" . ($res1['type'] == 'f' ? 'f' : 'r') . "' AND `realid` > '$sort' ORDER BY `realid` ASC LIMIT 1");
        if ($req2->rowCount()) {
            $res2 = $req2->fetch();
            $id2 = $res2['id'];
            $sort2 = $res2['realid'];
            App::db()->exec("UPDATE `forum__` SET `realid` = '$sort2' WHERE `id` = " . $id);
            App::db()->exec("UPDATE `forum__` SET `realid` = '$sort' WHERE `id` = '$id2'");
        }
    }
}

header('Location: ' . App::router()->getUri(2) . 'sections/' . (isset($res1['type']) && $res1['type'] == 'r' ? '?id=' . $res1['refid'] : ''));
