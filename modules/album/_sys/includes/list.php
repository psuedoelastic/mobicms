<?php

/**
 * @package     mobiCMS
 * @link        http://mobicms.net
 * @copyright   Copyright (C) 2008-2012 mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://mobicms.net/about
 */

defined('_IN_MOBICMS') or die('Error: restricted access');

//TODO: переделать SQL запросы

$url = App::router()->getUri(1);

/*
-----------------------------------------------------------------
Список альбомов юзера
-----------------------------------------------------------------
*/
//TODO: переделать запросы
if (isset($_SESSION['ap']))
    unset($_SESSION['ap']);
echo '<div class="phdr"><a href="' . $url . '"><b>' . __('photo_albums') . '</b></a> | ' . __('personal_2') . '</div>';
$req = mysql_query("SELECT * FROM `album__cat` WHERE `user_id` = '" . $user['id'] . "' " . ($user['id'] == App::user()->id || App::user()->rights >= 6 ? "" : "AND `access` > 1") . " ORDER BY `sort` ASC");
$total = mysql_num_rows($req);
echo '<div class="user"><p>' . Functions::displayUser($user, ['iphide' => 1,]) . '</p></div>';
if ($total) {
    for ($i = 0; $res = mysql_fetch_assoc($req); ++$i) {
        $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `album__files` WHERE `album_id` = '" . $res['id'] . "'"), 0);
        echo ($i % 2 ? '<div class="list2">' : '<div class="list1">') .
            Functions::loadModuleImage('album_' . $res['access'] . '.png') . '&#160;' .
            '<a href="' . $url . '?act=show&amp;al=' . $res['id'] . '&amp;user=' . $user['id'] . '"><b>' . htmlspecialchars($res['name']) . '</b></a>&#160;(' . $count . ')';
        if ($user['id'] == App::user()->id || App::user()->rights >= 6 || !empty($res['description'])) {
            $menu = [
                '<a href="' . $url . '?act=sort&amp;mod=up&amp;al=' . $res['id'] . '&amp;user=' . $user['id'] . '">' . __('up') . '</a>',
                '<a href="' . $url . '?act=sort&amp;mod=down&amp;al=' . $res['id'] . '&amp;user=' . $user['id'] . '">' . __('down') . '</a>',
                '<a href="' . $url . '?act=edit&amp;al=' . $res['id'] . '&amp;user=' . $user['id'] . '">' . __('edit') . '</a>',
                '<a href="' . $url . '?act=delete&amp;al=' . $res['id'] . '&amp;user=' . $user['id'] . '">' . __('delete') . '</a>'
            ];
            echo '<div class="sub">' .
                (!empty($res['description']) ? '<div class="gray">' . htmlspecialchars($res['description']) . '</div>' : '') .
                ($user['id'] == App::user()->id || App::user()->rights >= 6 ? implode(' | ', $menu) : '') .
                '</div>';
        }
        echo '</div>';
    }
} else {
    echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
}
if ($user['id'] == App::user()->id && $total < $max_album || App::user()->rights >= 7) {
    echo '<div class="gmenu">' .
        '<form action="' . $url . '?act=edit&amp;user=' . $user['id'] . '" method="post">' .
        '<p><input type="submit" value="' . __('album_create') . '"/></p>' .
        '</form></div>';
}
echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';