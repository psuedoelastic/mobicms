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
    $url = App::router()->getUri(1);
    $topic_vote = App::db()->query("SELECT COUNT(*) FROM `forum__vote` WHERE `type`='1' AND `topic`=" . $id)->fetchColumn();
    if ($topic_vote == 0) {
        echo __('error_wrong_data');
        exit;
    }
    if (isset($_GET['delvote']) && !empty($_GET['vote'])) {
        $vote = abs(intval($_GET['vote']));
        $totalvote = App::db()->query("SELECT COUNT(*) FROM `forum__vote` WHERE `type` = '2' AND `id` = '$vote' AND `topic` = " . $id)->fetchColumn();
        $countvote = App::db()->query("SELECT COUNT(*) FROM `forum__vote` WHERE `type` = '2' AND `topic` = " . $id)->fetchColumn();
        if ($countvote <= 2)
            header('location: ?act=editvote&id=' . $id . '');
        if ($totalvote != 0) {
            if (isset($_GET['yes'])) {
                App::db()->exec("DELETE FROM `forum__vote` WHERE `id` = '$vote'");
                $countus = App::db()->query("SELECT COUNT(*) FROM `forum__vote_users` WHERE `vote` = '$vote' AND `topic` = " . $id)->fetchColumn();
                $topic_vote = App::db()->query("SELECT `count` FROM `forum__vote` WHERE `type` = '1' AND `topic` = " . $id . " LIMIT 1")->fetch();
                $totalcount = $topic_vote['count'] - $countus;
                App::db()->exec("UPDATE `forum__vote` SET  `count` = '$totalcount'   WHERE `type` = '1' AND `topic` = " . $id);
                App::db()->exec("DELETE FROM `forum__vote_users` WHERE `vote` = '$vote'");
                header('location: ?act=editvote&id=' . $id . '');
            } else {
                echo '<div class="rmenu"><p>' . __('voting_variant_warning') . '<br />' .
                    '<a href="' . $url . '?act=editvote&amp;id=' . $id . '&amp;vote=' . $vote . '&amp;delvote&amp;yes">' . __('delete') . '</a><br />' .
                    '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . __('cancel') . '</a></p></div>';
            }
        } else {
            header('location: ?act=editvote&id=' . $id . '');
        }
    } else if (isset($_POST['submit'])) {
        $vote_name = App::db()->quote(mb_substr(trim($_POST['name_vote']), 0, 50));
        if (!empty($vote_name)) {
            App::db()->exec("UPDATE `forum__vote` SET  `name` = " . $vote_name . "  WHERE `topic` = " . $id . " AND `type` = '1'");
        }
        $vote_result = App::db()->query("SELECT `id` FROM `forum__vote` WHERE `type`='2' AND `topic` = " . $id);
        while ($vote = $vote_result->fetch()) {
            if (!empty($_POST[$vote['id'] . 'vote'])) {
                $text = App::db()->quote(mb_substr(trim($_POST[$vote['id'] . 'vote']), 0, 30));
                App::db()->exec("UPDATE `forum__vote` SET  `name` = " . $text . "  WHERE `id` = '" . $vote['id'] . "'");
            }
        }
        $countvote = App::db()->query("SELECT COUNT(*) FROM `forum__vote` WHERE `type`='2' AND `topic` = " . $id)->fetchColumn();
        for ($vote = $countvote; $vote < 20; $vote++) {
            if (!empty($_POST[$vote])) {
                $text = App::db()->quote(mb_substr(trim($_POST[$vote]), 0, 30));
                App::db()->exec("INSERT INTO `forum__vote` SET `name` = " . $text . ",  `type` = '2', `topic` = " . $id);
            }
        }
        echo '<div class="gmenu"><p>' . __('voting_changed') . '<br /><a href="' . $url . '?id=' . $id . '">' . __('continue') . '</a></p></div>';
    } else {
        /*
        -----------------------------------------------------------------
        Форма редактирования опроса
        -----------------------------------------------------------------
        */
        $countvote = App::db()->query("SELECT COUNT(*) FROM `forum__vote` WHERE `type` = '2' AND `topic` = " . $id)->fetchColumn();
        $topic_vote = App::db()->query("SELECT `name` FROM `forum__vote` WHERE `type` = '1' AND `topic` = " . $id . " LIMIT 1")->fetch();
        echo '<div class="phdr"><a href="' . $url . '?id=' . $id . '"><b>' . __('forum') . '</b></a> | ' . __('edit_vote') . '</div>' .
            '<form action="' . $url . '?act=editvote&amp;id=' . $id . '" method="post">' .
            '<div class="gmenu"><p>' .
            '<b>' . __('voting') . ':</b><br/>' .
            '<input type="text" size="20" maxlength="150" name="name_vote" value="' . htmlentities($topic_vote['name'], ENT_QUOTES, 'UTF-8') . '"/>' .
            '</p></div>' .
            '<div class="menu"><p>';
        $vote_result = App::db()->query("SELECT `id`, `name` FROM `forum__vote` WHERE `type` = '2' AND `topic` = " . $id);
        $i = 0;
        while ($vote = $vote_result->fetch()) {
            echo __('answer') . ' ' . ($i + 1) . ' (max. 50): <br/>' .
                '<input type="text" name="' . $vote['id'] . 'vote" value="' . htmlentities($vote['name'], ENT_QUOTES, 'UTF-8') . '"/>';
            if ($countvote > 2)
                echo '&nbsp;<a href="' . $url . '?act=editvote&amp;id=' . $id . '&amp;vote=' . $vote['id'] . '&amp;delvote">[x]</a>';
            echo '<br/>';
            ++$i;
        }
        if ($countvote < 20) {
            if (isset($_POST['plus']))
                ++$_POST['count_vote'];
            elseif (isset($_POST['minus']))
                --$_POST['count_vote'];
            if (empty($_POST['count_vote']))
                $_POST['count_vote'] = $countvote;
            elseif ($_POST['count_vote'] > 20)
                $_POST['count_vote'] = 20;
            for ($vote = $i; $vote < $_POST['count_vote']; $vote++) {
                echo 'Ответ ' . ($vote + 1) . '(max. 50): <br/><input type="text" name="' . $vote . '" value="' . htmlspecialchars($_POST[$vote]) . '"/><br/>';
            }
            echo '<input type="hidden" name="count_vote" value="' . abs(intval($_POST['count_vote'])) . '"/>' . ($_POST['count_vote'] < 20 ? '<input type="submit" name="plus" value="' . __('add') . '"/>' : '')
                . ($_POST['count_vote'] - $countvote ? '<input type="submit" name="minus" value="' . __('delete_last') . '"/>' : '');
        }
        echo '</p></div><div class="gmenu">' .
            '<p><input type="submit" name="submit" value="' . __('save') . '"/></p>' .
            '</div></form>' .
            '<div class="phdr"><a href="' . $url . '?id=' . $id . '">' . __('cancel') . '</a></div>';
    }
}
