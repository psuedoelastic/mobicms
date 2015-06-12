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

if (App::user()->rights == 3 || App::user()->rights >= 6) {
    $topic_vote = App::db()->query("SELECT COUNT(*) FROM `forum__vote` WHERE `type`='1' AND `topic` = " . $id)->fetchColumn();
    if ($topic_vote == 0) {
        echo __('error_wrong_data');
        exit;
    }
    if (isset($_GET['yes'])) {
        App::db()->exec("DELETE FROM `forum__vote` WHERE `topic` = " . $id);
        App::db()->exec("DELETE FROM `forum__vote_users` WHERE `topic` = " . $id);
        App::db()->exec("UPDATE `forum__` SET  `realid` = '0'  WHERE `id` = " . $id);
        echo __('voting_deleted') . '<br /><a href="' . $_SESSION['prd'] . '">' . __('continue') . '</a>';
    } else {
        echo '<p>' . __('voting_delete_warning') . '</p>';
        echo '<p><a href="?act=delvote&amp;id=' . $id . '&amp;yes">' . __('delete') . '</a><br />';
        echo '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . __('cancel') . '</a></p>';
        $_SESSION['prd'] = htmlspecialchars(getenv("HTTP_REFERER"));
    }
} else {
    header('location: ../404.php');
}
