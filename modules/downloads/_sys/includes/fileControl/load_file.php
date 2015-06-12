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

$req_down = App::db()->query("SELECT * FROM `download__files` WHERE `id` = '" . App::vars()->id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();
if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name']) || ($res_down['type'] == 3 && App::user()->rights < 6 && App::user()->rights != 4)) {
    $error = true;
} else {
    $link = $res_down['dir'] . '/' . $res_down['name'];
}
$more = isset($_GET['more']) ? abs(intval($_GET['more'])) : false;
if ($more) {
    $req_more = App::db()->query("SELECT * FROM `download__more` WHERE `refid` = '" . App::vars()->id . "' AND `id` = '$more' LIMIT 1");
    $res_more = $req_more->fetch();
    if (!$req_more->rowCount() || !is_file($res_down['dir'] . '/' . $res_more['name'])) {
        $error = true;
    } else {
        $link = $res_down['dir'] . '/' . $res_more['name'];
    }
}
if ($error) {
    header('Location: ' . App::cfg()->sys->homeurl . '404');
} else {
    if (!isset($_SESSION['down_' . App::vars()->id])) {
        App::db()->exec("UPDATE `download__files` SET `field`=`field`+1 WHERE `id`=" . App::vars()->id);
        $_SESSION['down_' . App::vars()->id] = 1;
    }
    header('Location: ' . $link);
}