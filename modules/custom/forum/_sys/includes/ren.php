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
    if (!$id) {
        echo __('error_wrong_data');
        exit;
    }
    $url = App::router()->getUri(1);
    $typ = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = " . $id);
    $ms = $typ->fetch();
    if ($ms['type'] != "t") {
        echo __('error_wrong_data');
        exit;
    }
    if (isset($_POST['submit'])) {
        $nn = isset($_POST['nn']) ? htmlspecialchars($_POST['nn']) : false;
        if (!$nn) {
            echo __('error_topic_name') . ' <a href="' . $url . '?act=ren&amp;id=' . $id . '">' . __('repeat') . '</a>';
            exit;
        }

        // Проверяем, есть ли тема с таким же названием?
        $stmt = App::db()->prepare("
            SELECT * FROM `" . TP . "forum__`
            WHERE `type` = ?
            AND `refid`  = ?
            AND `text`   = ?
        ");
        $stmt->execute(['t', $ms['refid'], $nn]);
        if ($stmt->rowCount()) {
            echo __('error_topic_exists') . '<a href="' . $url . '?act=ren&amp;id=' . $id . '">' . __('repeat') . '</a>';
            exit;
        }
        $stmt = null;

        $stmt = $stmt = App::db()->prepare("
            UPDATE `" . TP . "forum__` SET
            `text`     = ?
            WHERE `id` = ?
        ");
        $stmt->execute([$nn, $id]);
        $stmt = null;

        header("Location: " . $url . "?id=" . $id);
    } else {
        /*
        -----------------------------------------------------------------
        Переименовываем тему
        -----------------------------------------------------------------
        */
        echo '<div class="phdr"><a href="' . $url . '?id=' . $id . '"><b>' . __('forum') . '</b></a> | ' . __('topic_rename') . '</div>' .
            '<div class="menu"><form action="' . $url . '?act=ren&amp;id=' . $id . '" method="post">' .
            '<p><h3>' . __('topic_name') . '</h3>' .
            '<input type="text" name="nn" value="' . $ms['text'] . '"/></p>' .
            '<p><input type="submit" name="submit" value="' . __('save') . '"/></p>' .
            '</form></div>' .
            '<div class="phdr"><a href="' . $url . '?id=' . $id . '">' . __('back') . '</a></div>';
    }
} else {
    echo __('access_forbidden');
}
