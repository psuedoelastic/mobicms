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
    $vote_name = isset($_POST['vote_name']) ? mb_substr(trim($_POST['vote_name']), 0, 50) : '';
    $vote_count = isset($_POST['vote_count']) ? abs(intval($_POST['vote_count'])) : 2;

    if ($vote_count > 20) {
        $vote_count = 20;
    } else if ($vote_count < 2) {
        $vote_count = 2;
    }

    $topic = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `type`='t' AND `id` = " . $id . " AND `edit` != '1'")->fetchColumn();
    $topic_vote = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__vote` WHERE `type`='1' AND `topic` = " . $id)->fetchColumn();

    if ($topic_vote != 0 || $topic == 0) {
        echo __('error_wrong_data') . '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . __('back') . '</a>';
        exit;
    }

    if (isset($_POST['submit'])) {
        if (!empty($vote_name) && !empty($_POST[0]) && !empty($_POST[1])) {
            $stmt = App::db()->prepare("
                INSERT INTO `" . TP . "forum__vote`
                (`name`, `time`, `type`, `topic`)
                VALUES (?, ?, 1, ?)
            ");

            $stmt->execute([
                $vote_name,
                time(),
                $id
            ]);
            $stmt = null;

            App::db()->exec("UPDATE `" . TP . "forum__` SET  `realid` = '1'  WHERE `id` = " . $id);

            $stmt = App::db()->prepare("
                INSERT INTO `" . TP . "forum__vote`
                (`name`, `type`, `topic`)
                VALUES (?, 2, ?)
            ");
            for ($vote = 0; $vote < $vote_count; $vote++) {
                $text = mb_substr(trim($_POST[$vote]), 0, 30);
                if (empty($text)) continue;
                $stmt->execute([$text, $id]);
            }
            $stmt = null;

            echo __('voting_added') . '<br /><a href="?id=' . $id . '">' . __('continue') . '</a>';
        } else
            echo __('error_empty_fields') . '<br /><a href="?act=addvote&amp;id=' . $id . '">' . __('repeat') . '</a>';
    } else {
        echo '<form action="' . App::router()->getUri(1) . '?act=addvote&amp;id=' . $id . '" method="post">' .
            '<br />' . __('voting') . ':<br/>' .
            '<input type="text" size="20" maxlength="150" name="vote_name" value="' . htmlspecialchars($vote_name) . '"/><br/>';
        if (isset($_POST['plus'])) ++$vote_count;
        elseif (isset($_POST['minus'])) --$vote_count;
        for ($i = 0; $i < $vote_count; $i++) {
            $answer[$i] = isset($_POST[$i]) ? htmlspecialchars($_POST[$i]) : '';
            echo __('answer') . ' ' . ($i + 1) . '(max. 50): <br/><input type="text" name="' . $i . '" value="' . $answer[$i] . '"/><br/>';
        }
        echo '<input type="hidden" name="vote_count" value="' . $vote_count . '"/>';
        echo ($vote_count < 20) ? '<br/><input type="submit" name="plus" value="' . __('add_answer') . '"/>' : '';
        echo $vote_count > 2 ? '<input type="submit" name="minus" value="' . __('delete_last') . '"/><br/>' : '<br/>';
        echo '<p><input type="submit" name="submit" value="' . __('save') . '"/></p></form>';
        echo '<a href="' . App::router()->getUri(1) . '?id=' . $id . '">' . __('back') . '</a>';
    }
} else {
    header('location: ../404.php');
}
