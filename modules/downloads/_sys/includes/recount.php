<?php

/**
 * @package     mobiCMS
 * @link        http://mobicms.net
 * @copyright   Copyright (C) 2008-2011 mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://mobicms.net/about
 */

defined('MOBICMS') or die('Error: restricted access');

if (App::user()->rights == 4 || App::user()->rights >= 6) {
    $req_down = App::db()->query("SELECT `dir`, `name`, `id` FROM `download__category`");
    while ($res_down = $req_down->fetch()) {
        $dir_files = App::db()->query("SELECT COUNT(*) FROM `download__files` WHERE `type` = '2' AND `dir` LIKE '" . ($res_down['dir']) . "%'")->fetchColumn();
        App::db()->exec("UPDATE `download__category` SET `total` = '$dir_files' WHERE `id` = '" . $res_down['id'] . "'");
    }
}
header('Location: ' . App::router()->getUri(1) . '?id=' . App::request()->getQuery('id', ''));