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
$url = App::router()->getUri(1);
$mod = urldecode(trim(App::request()->getQuery('mod', false)));

//TODO: Доработать!

/*
-----------------------------------------------------------------
Список посетителей. у которых есть фотографии
-----------------------------------------------------------------
*/
switch ($mod) {
    case 'boys':
        $sql = "WHERE `user__`.`sex` = 'm'";
        break;

    case 'girls':
        $sql = "WHERE `user__`.`sex` = 'w'";
        break;
    default:
        $sql = "";
}

$menu = [
    (!$mod ? '<b>' . __('all') . '</b>' : '<a href="' . $url . '?act=users">' . __('all') . '</a>'),
    ($mod == 'boys' ? '<b>' . __('mans') . '</b>' : '<a href="' . $url . '?act=users&amp;mod=boys">' . __('mans') . '</a>'),
    ($mod == 'girls' ? '<b>' . __('womans') . '</b>' : '<a href="' . $url . '?act=users&amp;mod=girls">' . __('womans') . '</a>')
];
echo '<div class="phdr"><a href="' . $url . '"><b>' . __('photo_albums') . '</b></a> | ' . __('list') . '</div>' .
    '<div class="topmenu">' . Functions::displayMenu($menu) . '</div>';
$total = mysql_result(mysql_query("SELECT COUNT(DISTINCT `user_id`)
    FROM `album__files`
    LEFT JOIN `user__` ON `album__files`.`user_id` = `user__`.`id`
" . $sql), 0);
if ($total) {
    $req = mysql_query("SELECT `album__files`.*, COUNT(`album__files`.`id`) AS `count`, `user__`.`id` AS `uid`, `user__`.`nickname`, `user__`.`sex`
        FROM `album__files`
        LEFT JOIN `user__` ON `album__files`.`user_id` = `user__`.`id` $sql
        GROUP BY `album__files`.`user_id` ORDER BY `user__`.`nickname` ASC " . App::db()->pagination()
    );
    $i = 0;
    for ($i = 0; $res = mysql_fetch_assoc($req); ++$i) {
        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
        echo Functions::loadImage(($res['sex'] == 'm' ? 'user.png' : 'user-female.png')) . '&#160;' .
            '<a href="' . $url . '?act=list&amp;user=' . $res['uid'] . '">' . $res['nickname'] . '</a> (' . $res['count'] . ')</div>';
    }
} else {
    echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
}
echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
if ($total > App::user()->settings['page_size']) {
    echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=users' . ($mod ? '&amp;mod=' . $mod : '') . '&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
        '<p><form action="' . $url . '?act=users' . ($mod ? '&amp;mod=' . $mod : '') . '" method="post">' .
        '<input type="text" name="page" size="2"/>' .
        '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
        '</form></p>';
}