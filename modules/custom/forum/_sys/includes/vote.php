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

if (App::user()->id) {
    $topic = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `type`='t' AND `id` = " . $id . " AND `edit` != '1'")->fetchColumn();
    $vote = abs(intval($_POST['vote']));
    $topic_vote = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__vote` WHERE `type` = '2' AND `id` = '$vote' AND `topic` = " . $id)->fetchColumn();
    $vote_user = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__vote_users` WHERE `user` = " . App::user()->id . " AND `topic` = " . $id)->fetchColumn();
    if ($topic_vote == 0 || $vote_user > 0 || $topic == 0) {
        echo __('error_wrong_data');
        exit;
    }
    App::db()->exec("INSERT INTO `" . TP . "forum__vote_users` SET `topic` = " . $id . ", `user` = " . App::user()->id . ", `vote` = '$vote'");
    App::db()->exec("UPDATE `" . TP . "forum__vote` SET `count` = count + 1 WHERE id = '$vote'");
    App::db()->exec("UPDATE `" . TP . "forum__vote` SET `count` = count + 1 WHERE topic = " . $id . " AND `type` = '1'");
    echo __('vote_accepted') . '<br /><a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . __('back') . '</a>';
} else {
    echo __('access_guest_forbidden');
}
