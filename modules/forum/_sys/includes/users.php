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

$url = App::router()->getUri(1);
$id = abs(intval(App::request()->getQuery('id', 0)));

$topic_vote = App::db()->query("SELECT COUNT(*) FROM `forum__vote` WHERE `type` = '1' AND `topic` = " . $id)->fetchColumn();
if ($topic_vote == 0) {
    echo __('error_wrong_data');
    exit;
} else {
    $topic_vote = App::db()->query("SELECT `name`, `time`, `count` FROM `forum__vote` WHERE `type` = '1' AND `topic` = " . $id . " LIMIT 1")->fetch();
    echo '<div  class="phdr">' . __('voting_users') . ' &laquo;<b>' . htmlentities($topic_vote['name'], ENT_QUOTES, 'UTF-8') . '</b>&raquo;</div>';
    $total = App::db()->query("SELECT COUNT(*) FROM `forum__vote_users` WHERE `topic`=" . $id)->fetchColumn();
    $req = App::db()->query("SELECT `forum__vote_users`.*, `user__`.`rights`, `user__`.`last_visit`, `user__`.`nickname`, `user__`.`sex`, `user__`.`status`, `user__`.`join_date`, `user__`.`id`
    FROM `forum__vote_users` LEFT JOIN `user__` ON `forum__vote_users`.`user` = `user__`.`id`
    WHERE `forum__vote_users`.`topic`=" . $id . " ORDER BY `forum__vote_users`.`id` DESC " . App::db()->pagination());
    for ($i = 0; $res = $req->fetch(); ++$i) {
        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
        echo Functions::displayUser($res, ['iphide' => 1]);
        echo '</div>';
    }
    if ($total == 0)
        echo '<div class="menu">' . __('voting_users_empty') . '</div>';
    echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
    if ($total > App::user()->settings['page_size']) {
        echo '<p>' . Functions::displayPagination($url . '?act=users&amp;id=' . $id . '&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</p>' .
            '<p><form action="' . $url . '?act=users&amp;id=' . $id . '" method="post">' .
            '<input type="text" name="page" size="2"/>' .
            '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/></form></p>';
    }
    echo '<p><a href="' . $url . '?id=' . $id . '">' . __('to_topic') . '</a></p>';
}
