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
$url = App::router()->getUri(1);

/*
-----------------------------------------------------------------
Качаем JAD файл
-----------------------------------------------------------------
*/
$req_down = App::db()->query("SELECT * FROM `" . TP . "download__files` WHERE `id` = '" . App::vars()->id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();
if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name']) || (functions::format($res_down['name']) != 'jar' && !isset($_GET['more'])) || ($res_down['type'] == 3 && App::user()->rights < 6 && App::user()->rights != 4)) {
    echo __('not_found_file') . ' <a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}
if (isset($_GET['more'])) {
    $more = abs(intval($_GET['more']));
    $req_more = App::db()->query("SELECT * FROM `" . TP . "download__more` WHERE `id` = '$more' LIMIT 1");
    $res_more = $req_more->fetch();
    if (!$req_more->rowCount() || !is_file($res_down['dir'] . '/' . $res_more['name']) || functions::format($res_more['name']) != 'jar') {
        echo __('not_found_file') . '<a href="' . $url . '">' . __('download_title') . '</a>';
        exit;
    }
    $down_file = $res_down['dir'] . '/' . $res_more['name'];
    $jar_file = $res_more['name'];
} else {
    $down_file = $res_down['dir'] . '/' . $res_down['name'];
    $jar_file = $res_down['name'];
}
if (!isset($_SESSION['down_' . App::vars()->id])) {
    App::db()->exec("UPDATE `" . TP . "download__files` SET `field`=`field`+1 WHERE `id`=" . App::vars()->id);
    $_SESSION['down_' . App::vars()->id] = 1;
}
$size = filesize($down_file);
require(SYSPATH . 'lib/pclzip.lib.php');
$zip = new PclZip($down_file);
$content = $zip->extract(PCLZIP_OPT_BY_NAME, 'META-INF/MANIFEST.MF', PCLZIP_OPT_EXTRACT_AS_STRING);

App::view()->setLayout(false);
$out = $content[0]['content'] . "\n" . 'MIDlet-Jar-Size: ' . $size . "\n" . 'MIDlet-Jar-URL: ' . App::cfg()->sys->homeurl . $res_down['dir'] . '/' . $jar_file;
Functions::downloadFile($out, basename($down_file) . '.jad');