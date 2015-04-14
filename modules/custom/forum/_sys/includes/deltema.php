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

    // Проверяем, существует ли тема
    $req = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = " . $id . " AND `type` = 't'");
    if (!$req->rowCount()) {
        echo __('error_topic_deleted');
        exit;
    }
    $res = $req->fetch();
    if (isset($_POST['submit'])) {
        $del = isset($_POST['del']) ? intval($_POST['del']) : null;
        if ($del == 2 && App::user()->rights == 9) {
            /*
            -----------------------------------------------------------------
            Удаляем топик
            -----------------------------------------------------------------
            */
            $req1 = App::db()->query("SELECT * FROM `" . TP . "forum__files` WHERE `topic` = " . $id);
            if ($req1->rowCount()) {
                while ($res1 = $req1->fetch()) {
                    unlink(ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'forum' . DIRECTORY_SEPARATOR . $res1['filename']);
                }
                App::db()->exec("DELETE FROM `" . TP . "forum__files` WHERE `topic` = " . $id);
                App::db()->query("OPTIMIZE TABLE `" . TP . "forum__files`");
            }
            App::db()->exec("DELETE FROM `" . TP . "forum__` WHERE `refid` = " . $id);
            App::db()->exec("DELETE FROM `" . TP . "forum__` WHERE `id` = " . $id);
        } elseif ($del = 1) {
            /*
            -----------------------------------------------------------------
            Скрываем топик
            -----------------------------------------------------------------
            */
            $nick = App::db()->quote(App::user()->data['nickname']);
            App::db()->exec("UPDATE `" . TP . "forum__` SET `close` = '1', `close_who` = " . $nick . " WHERE `id` = " . $id);
            App::db()->exec("UPDATE `" . TP . "forum__files` SET `del` = '1' WHERE `topic` = " . $id);
        }
        header('Location: ' . $url . '?id=' . $res['refid']);
    } else {
        /*
        -----------------------------------------------------------------
        Меню выбора режима удаления темы
        -----------------------------------------------------------------
        */
        echo '<div class="phdr"><a href="' . $url . '?id=' . $id . '"><b>' . __('forum') . '</b></a> | ' . __('topic_delete') . '</div>' .
            '<div class="rmenu"><form method="post" action="' . $url . '?act=deltema&amp;id=' . $id . '">' .
            '<p><h3>' . __('delete_confirmation') . '</h3>' .
            '<input type="radio" value="1" name="del" checked="checked"/>&#160;' . __('hide') . '<br />' .
            (App::user()->rights == 9 ? '<input type="radio" value="2" name="del" />&#160;' . __('delete') : '') .
            '</p><p><input type="submit" name="submit" value="' . __('do') . '" /></p>' .
            '<p><a href="' . $url . '?id=' . $id . '">' . __('cancel') . '</a>' .
            '</p></form></div>' .
            '<div class="phdr">&#160;</div>';
    }
} else {
    echo __('access_forbidden');
}
