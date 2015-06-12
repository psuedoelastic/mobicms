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
    header('Location: http://mobicms.net/404.php');
    exit;
}
$req = App::db()->query("SELECT * FROM `forum__` WHERE `id` = " . $id . " AND (`type` = 't' OR `type` = 'm')");
if ($req->rowCount()) {
    $res = $req->fetch();
    $nick = App::db()->quote(App::user()->data['nickname']);
    App::db()->exec("UPDATE `forum__` SET `close` = '0', `close_who` = " . $nick . " WHERE `id` = " . $id);
    if ($res['type'] == 't') {
        header('Location: ' . App::router()->getUri(1) . '?id=' . $id);
    } else {
        $page = ceil(App::db()->query("SELECT COUNT(*) FROM `forum__` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">= " : "<= ") . $id)->fetchColumn() / App::user()->settings['page_size']);
        header('Location: ' . App::router()->getUri(1) . '?id=' . $res['refid'] . '&page=' . $page);
    }
} else {
    header('Location: ' . App::router()->getUri(1));
}
