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
Скачка TXT файла в JAR
-----------------------------------------------------------------
*/
$dir_clean = opendir(ROOT_PATH . 'files/download/temp/created_java/files');
while ($file = readdir($dir_clean)) {
    if ($file != 'index.php' && $file != '.htaccess' && $file != '.' && $file != '..') {
        $time_file = filemtime(ROOT_PATH . 'files/download/temp/created_java/files/' . $file);
        if ($time_file < (time() - 300))
            @unlink(ROOT_PATH . 'files/download/temp/created_java/files/' . $file);
    }
}
closedir($dir_clean);
$req_down = App::db()->query("SELECT * FROM `download__files` WHERE `id` = '" . App::vars()->id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();
$format_file = functions::format($res_down['name']);
if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name']) || ($format_file != 'txt' && !isset($_GET['more'])) || ($res_down['type'] == 3 && App::user()->rights < 6 && App::user()->rights != 4)) {
    echo __('not_found_file') . '<a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}
if (isset($_GET['more'])) {
    $more = abs(intval($_GET['more']));
    $req_more = App::db()->query("SELECT * FROM `download__more` WHERE `id` = '$more' LIMIT 1");
    $res_more = $req_more->fetch();
    $format_file = functions::format($res_more['name']);
    if (!$req_more->rowCount() || !is_file($res_down['dir'] . '/' . $res_more['name']) || $format_file != 'txt') {
        echo __('not_found_file') . '<a href="' . $url . '">' . __('download_title') . '</a>';
        exit;
    }
    $down_file = $res_down['dir'] . '/' . $res_more['name'];
    $title_pages = $res_more['rus_name'];
    $txt_file = $res_more['name'];
} else {
    $down_file = $res_down['dir'] . '/' . $res_down['name'];
    $title_pages = $res_down['rus_name'];
    $txt_file = $res_down['name'];
}
if (!isset($_SESSION['down_' . App::vars()->id])) {
    App::db()->exec("UPDATE `download__files` SET `field`=`field`+1 WHERE `id`=" . App::vars()->id);
    $_SESSION['down_' . App::vars()->id] = 1;
}
$file = str_replace('.' . $format_file, '', $txt_file);
$name = str_replace('.' . $format_file, '', $txt_file);
$tmp = 'files/download/temp/created_java/files/' . $name . '.jar';
$tmp_jad = 'files/download/temp/created_java/files/' . $name . '.jar.jad';
if (!file_exists($tmp)) {
    $midlet_name = mb_substr($res_down['rus_name'], 0, 10);
    $midlet_name = iconv('UTF-8', 'windows-1251', $midlet_name);
    $book_text = file_get_contents($res_down['dir'] . '/' . $res_down['name']);
    $charset_text = strtolower(mb_detect_encoding($book_text, 'UTF-8, windows-1251'));
    if ($charset_text != 'windows-1251')
        @$book_text = iconv('utf-8', 'windows-1251', $book_text);
    $files = fopen("files/download/temp/created_java/java/textfile.txt", 'w+');
    flock($files, LOCK_EX);
    $book_name = iconv('UTF-8', 'windows-1251', $res_down['rus_name']);
    $result = "\r\n" . $book_name . "\r\n\r\n----------\r\n\r\n" . trim($book_text) . "\r\n\r\nDownloaded from " . App::cfg()->sys->homeurl;
    fputs($files, $result);
    flock($files, LOCK_UN);
    fclose($files);
    $manifest_text = 'Manifest-Version: 1.0
MIDlet-1: Файл #' . App::vars()->id . ', , br.BookReader
MIDlet-Name: $tmp_jad
MIDlet-Vendor: mobiCMS
MIDlet-Version: 1.5.3
MIDletX-No-Command: true
MIDletX-LG-Contents: true
MicroEdition-Configuration: CLDC-1.0
MicroEdition-Profile: MIDP-1.0
TCBR-Platform: Generic version (all phones)';
    $files = fopen("files/download/temp/created_java/java/META-INF/MANIFEST.MF", 'w+');
    flock($files, LOCK_EX);
    fputs($files, $manifest_text);
    flock($files, LOCK_UN);
    fclose($files);
    require(SYSPATH . 'lib/pclzip.lib.php');
    $archive = new PclZip($tmp);
    $list = $archive->create('files/download/temp/created_java/java', PCLZIP_OPT_REMOVE_PATH, 'files/download/temp/created_java/java');
    if (!file_exists($tmp)) {
        echo __('error_jar_file');
        exit;
    }
}
if (!file_exists($tmp_jad)) {
    $filesize = filesize($tmp);
    $jad_text = 'Manifest-Version: 1.0
MIDlet-1: Файл #' . App::vars()->id . ', , br.BookReader
MIDlet-Name: Файл #' . App::vars()->id . '
MIDlet-Vendor: mobiCMS
MIDlet-Version: 1.5.3
MIDletX-No-Command: true
MIDletX-LG-Contents: true
MicroEdition-Configuration: CLDC-1.0
MicroEdition-Profile: MIDP-1.0
TCBR-Platform: Generic version (all phones)
MIDlet-Jar-Size: ' . $filesize . '
MIDlet-Jar-URL: ' . App::cfg()->sys->homeurl . '/' . $tmp; //TODO: Переделать ссылку
    $files = fopen($tmp_jad, 'w+');
    flock($files, LOCK_EX);
    fputs($files, $jad_text);
    flock($files, LOCK_UN);
    fclose($files);
}
/*
-----------------------------------------------------------------
Ссылки на файлы
-----------------------------------------------------------------
*/

echo '<div class="phdr"><b>' . htmlspecialchars($title_pages) . '</b></div>' .
    '<div class="menu">' . __('download') . ': <a href="' . htmlspecialchars($tmp) . '">JAR</a> | <a href="' . htmlspecialchars($tmp_jad) . '">JAD</a></div>' .
    '<div class="phdr">' . __('time_limit') . '</div>' .
    '<p><a href="' . $url . '?act=view&amp;id=' . App::vars()->id . '">' . __('back') . '</a></p>';