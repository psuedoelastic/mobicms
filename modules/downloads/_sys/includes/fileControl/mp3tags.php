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
Редактировать mp3 тегов
-----------------------------------------------------------------
*/
$req_down = App::db()->query("SELECT * FROM `download__files` WHERE `id` = '" . App::vars()->id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();
if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name']) || functions::format($res_down['name']) != 'mp3' || App::user()->rights < 6) {
    echo '<a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}
echo '<div class="phdr"><b>' . __('edit_mp3tags') . ':</b> ' . htmlspecialchars($res_down['rus_name']) . '</div>';
require(SYSPATH . 'lib/getid3/getid3.php');
$getID3 = new getID3;
$getID3->encoding = 'cp1251';
$getid = $getID3->analyze($res_down['dir'] . '/' . $res_down['name']);
if (!empty($getid['tags']['id3v2'])) $tagsArray = $getid['tags']['id3v2'];
elseif (!empty($getid['tags']['id3v1'])) $tagsArray = $getid['tags']['id3v1'];

if (isset($_POST['submit'])) {
    $tagsArray['artist'][0] = isset($_POST['artist']) ? Download::mp3tagsOut($_POST['artist'], 1) : '';
    $tagsArray['title'][0] = isset($_POST['title']) ? Download::mp3tagsOut($_POST['title'], 1) : '';
    $tagsArray['album'][0] = isset($_POST['album']) ? Download::mp3tagsOut($_POST['album'], 1) : '';
    $tagsArray['genre'][0] = isset($_POST['genre']) ? Download::mp3tagsOut($_POST['genre'], 1) : '';
    $tagsArray['year'][0] = isset($_POST['year']) ? (int)$_POST['year'] : 0;
    require(SYSPATH . 'lib/getid3/write.php');
    $tagsWriter = new getid3_writetags;
    $tagsWriter->filename = $res_down['dir'] . '/' . $res_down['name'];
    $tagsWriter->tagformats = ['id3v1', 'id3v2.3'];
    $tagsWriter->tag_encoding = 'cp1251';
    $tagsWriter->tag_data = $tagsArray;
    $tagsWriter->WriteTags();
    echo '<div class="gmenu">' . __('mp3tags_saved') . '</div>';


}
echo '<div class="list1"><form action="' . $url . '?act=mp3tags&amp;id=' . App::vars()->id . '" method="post">' .
    '<b>' . __('mp3_artist') . '</b>:<br /> <input name="artist" type="text" value="' . Download::mp3tagsOut($tagsArray['artist'][0]) . '" /><br />' .
    '<b>' . __('mp3_title') . '</b>:<br /> <input name="title" type="text" value="' . Download::mp3tagsOut($tagsArray['title'][0]) . '" /><br />' .
    '<b>' . __('mp3_album') . '</b>:<br /> <input name="album" type="text" value="' . Download::mp3tagsOut($tagsArray['album'][0]) . '" /><br />' .
    '<b>' . __('mp3_genre') . '</b>: <br /><input name="genre" type="text" value="' . Download::mp3tagsOut($tagsArray['genre'][0]) . '" /><br />' .
    '<b>' . __('mp3_year') . '</b>:<br /> <input name="year" type="text" value="' . (int)$tagsArray['year'][0] . '" /><br />' .
    '<input type="submit" name="submit" value="' . __('sent') . '"/></form></div>' .
    '<div class="phdr"><a href="' . $url . '?act=view&amp;id=' . App::vars()->id . '">' . __('back') . '</a></div>';