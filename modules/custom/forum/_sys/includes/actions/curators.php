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

if (App::user()->rights >= 7) {
    $url = App::router()->getUri(1);
    $topic = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = " . $id . " AND `type` = 't'")->fetch();

    if (empty($topic) || App::user()->rights < 7) {
        echo __('error_topic_deleted');
        exit;
    }

    $req = App::db()->query("
        SELECT `" . TP . "forum__`.*, `" . TP . "user__`.`id`
        FROM `" . TP . "forum__`
        LEFT JOIN `" . TP . "user__` ON `" . TP . "forum__`.`user_id` = `" . TP . "user__`.`id`
        WHERE `" . TP . "forum__`.`refid` = " . $id . "
        AND `" . TP . "user__`.`rights` < 6
        AND `" . TP . "user__`.`rights` != 3
        GROUP BY `" . TP . "forum__`.`from`
        ORDER BY `" . TP . "forum__`.`from`
        ");

    $total = $req->rowCount();

    echo '<div class="phdr"><a href="' . $url . '?id=' . $id . '&amp;start=' . App::vars()->start . '"><b>' . __('forum') . '</b></a> | ' . __('curators') . '</div>' .
        '<div class="bmenu">' . $topic['text'] . '</div>';

    $curators = [];
    $users = !empty($topic['curators']) ? unserialize($topic['curators']) : [];

    if (isset($_POST['submit'])) {
        $users = isset($_POST['users']) ? $_POST['users'] : [];
        if (!is_array($users)) $users = [];
    }

    if ($total > 0) {
        echo '<form action="' . $url . 'curators/?id=' . $id . '&amp;start=' . App::vars()->start . '" method="post">';

        $i = 0;
        while ($res = $req->fetch()) {
            $checked = array_key_exists($res['user_id'], $users) ? true : false;
            if ($checked) $curators[$res['user_id']] = $res['from'];
            {
                echo ($i++ % 2 ? '<div class="list2">' : '<div class="list1">') .
                    '<input type="checkbox" name="users[' . $res['user_id'] . ']" value="' . $res['from'] . '"' . ($checked ? ' checked="checked"' : '') . '/>&#160;' .
                    '<a href="' . App::cfg()->sys->homeurl . 'profile/' . $res['user_id'] . '">' . $res['from'] . '</a></div>';
            }
        }

        echo '<div class="gmenu"><input type="submit" value="' . __('assign') . '" name="submit" /></div></form>';

        if (isset($_POST['submit'])) {
            $stmt = App::db()->prepare("
                UPDATE `" . TP . "forum__` SET
                `curators` = ?
                WHERE `id` = " . $id
            );
            $stmt->execute([serialize($curators)]);
            $stmt = null;
        }

    } else {
        echo __('list_empty');
    }

    echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>' .
        '<p><a href="' . $url . '?id=' . $id . '&amp;start=' . App::vars()->start . '">' . __('back') . '</a></p>';
}
