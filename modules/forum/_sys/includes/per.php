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
    $typ = App::db()->query("SELECT * FROM `forum__` WHERE `id` = " . $id . " AND `type` = 't'");
    if (!$typ->rowCount()) {
        echo __('error_wrong_data');
        exit;
    }
    if (isset($_POST['submit'])) {
        $razd = isset($_POST['razd']) ? abs(intval($_POST['razd'])) : false;
        if (!$razd) {
            echo __('error_wrong_data');
            exit;
        }
        $typ1 = App::db()->query("SELECT * FROM `forum__` WHERE `id` = '$razd' AND `type` = 'r'");
        if (!$typ1->rowCount()) {
            echo __('error_wrong_data');
            exit;
        }

        App::db()->exec("UPDATE `forum__` SET `refid` = '$razd' WHERE `id` = " . $id);
        header("Location: " . $url . "?id=" . $id);
    } else {
        /*
        -----------------------------------------------------------------
        Перенос темы
        -----------------------------------------------------------------
        */
        $ms = $typ->fetch();
        if (empty($_GET['other'])) {
            $rz = App::db()->query("SELECT * FROM `forum__` WHERE id='" . $ms['refid'] . "'");
            $rz1 = $rz->fetch();
            $other = $rz1['refid'];
        } else {
            $other = abs(intval($_GET['other']));
        }
        $fr = App::db()->query("SELECT * FROM `forum__` WHERE id='" . $other . "'");
        $fr1 = $fr->fetch();
        echo '<div class="phdr"><a href="' . $url . '?id=' . $id . '"><b>' . __('forum') . '</b></a> | ' . __('topic_move') . '</div>' .
            '<form action="' . $url . '?act=per&amp;id=' . $id . '" method="post">' .
            '<div class="gmenu"><p>' .
            '<h3>' . __('category') . '</h3>' . $fr1['text'] . '</p>';

        $raz = App::db()->query("SELECT * FROM `forum__` WHERE `refid` = '$other' AND `type` = 'r' AND `id` != '" . $ms['refid'] . "' ORDER BY `realid` ASC");
        if ($raz->rowCount()) {
            echo '<p><h3>' . __('section') . '</h3>' .
                '<select name="razd">';
            while ($raz1 = $raz->fetch()) {
                echo '<option value="' . $raz1['id'] . '">' . $raz1['text'] . '</option>';
            }
            echo '</select></p>';
        }
        echo '<p><input type="submit" name="submit" value="' . __('move') . '"/></p>' .
            '</div></form>' .
            '<div class="phdr">' . __('other_categories') . '</div>';
        $frm = App::db()->query("SELECT * FROM `forum__` WHERE `type` = 'f' AND `id` != '$other' ORDER BY `realid` ASC");
        for ($i = 0; $frm1 = $frm->fetch(); ++$i) {
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            echo '<a href="' . $url . '?act=per&amp;id=' . $id . '&amp;other=' . $frm1['id'] . '">' . $frm1['text'] . '</a></div>';
        }
        echo '<div class="phdr"><a href="' . $url . '">' . __('back') . '</a></div>';
    }
}
