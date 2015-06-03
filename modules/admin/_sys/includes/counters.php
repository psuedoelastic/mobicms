<?php
/*
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

$uri = App::router()->getUri() . 'counters/';
$id = abs(intval(App::request()->getQuery('id', 0)));

switch (trim(App::request()->getQuery('act', ''))) {
    case 'view':
        // Предварительный просмотр счетчиков
        if ($id) {
            $req = App::db()->query("SELECT * FROM `" . TP . "system__counters` WHERE `id` = " . $id);
            if ($req->rowCount()) {
                if (isset($_GET['go']) && $_GET['go'] == 'on') {
                    App::db()->exec("UPDATE `" . TP . "system__counters` SET `switch` = '1' WHERE `id` = " . $id);
                    $req = App::db()->query("SELECT * FROM `" . TP . "system__counters` WHERE `id` = " . $id);
                } elseif (isset($_GET['go']) && $_GET['go'] == 'off') {
                    App::db()->exec("UPDATE `" . TP . "system__counters` SET `switch` = '0' WHERE `id` = " . $id);
                    $req = App::db()->query("SELECT * FROM `" . TP . "system__counters` WHERE `id` = " . $id);
                }
                $res = $req->fetch();
                echo '<div class="phdr"><a href="' . $uri . '"><b>' . __('counters') . '</b></a> | ' . __('viewing') . '</div>' .
                    '<div class="menu">' . ($res['switch'] == 1 ? '<span class="green">[ON]</span>' : '<span class="red">[OFF]</span>') . '&#160;<b>' . htmlspecialchars($res['name']) . '</b></div>' .
                    ($res['switch'] == 1 ? '<div class="gmenu">' : '<div class="rmenu">') . '<p><h3>' . __('counter_mod1') . '</h3>' . $res['link1'] . '</p>' .
                    '<p><h3>' . __('counter_mod2') . '</h3>' . $res['link2'] . '</p>' .
                    '<p><h3>' . __('display_mode') . '</h3>';
                switch ($res['mode']) {
                    case 2:
                        echo __('counter_help1');
                        break;

                    case 3:
                        echo __('counter_help2');
                        break;

                    default:
                        echo __('counter_help12');
                }
                echo '</p></div>';
                echo '<div class="phdr">'
                    . ($res['switch'] == 1 ? '<a href="' . $uri . '?act=view&amp;go=off&amp;id=' . $id . '">' . __('lng_off') . '</a>'
                        : '<a href="' . $uri . '?act=view&amp;go=on&amp;id=' . $id . '">' . __('lng_on') . '</a>')
                    . ' | <a href="' . $uri . '?act=edit&amp;id=' . $id . '">' . __('edit') . '</a> | <a href="' . $uri . '?act=del&amp;id=' . $id . '">' . __('delete') . '</a></div>';
            } else {
                echo __('error_wrong_data');
            }
        }
        break;

    case 'up':
        // Перемещение счетчика на одну позицию вверх
        if ($id) {
            $req = App::db()->query("SELECT `sort` FROM `" . TP . "system__counters` WHERE `id` = " . $id);
            if ($req->rowCount()) {
                $res = $req->fetch();
                $sort = $res['sort'];
                $req = App::db()->query("SELECT * FROM `" . TP . "system__counters` WHERE `sort` < '$sort' ORDER BY `sort` DESC LIMIT 1");
                if ($req->rowCount()) {
                    $res = $req->fetch();
                    $id2 = $res['id'];
                    $sort2 = $res['sort'];
                    App::db()->exec("UPDATE `" . TP . "system__counters` SET `sort` = '$sort2' WHERE `id` = " . $id);
                    App::db()->exec("UPDATE `" . TP . "system__counters` SET `sort` = '$sort' WHERE `id` = '$id2'");
                }
            }
        }

        header('Location: ' . $uri);
        break;

    case 'down':
        // Перемещение счетчика на одну позицию вниз
        if ($id) {
            $req = App::db()->query("SELECT `sort` FROM `" . TP . "system__counters` WHERE `id` = " . $id);
            if ($req->rowCount()) {
                $res = $req->fetch();
                $sort = $res['sort'];
                $req = App::db()->query("SELECT * FROM `" . TP . "system__counters` WHERE `sort` > '$sort' ORDER BY `sort` ASC LIMIT 1");
                if ($req->rowCount()) {
                    $res = $req->fetch();
                    $id2 = $res['id'];
                    $sort2 = $res['sort'];
                    App::db()->exec("UPDATE `" . TP . "system__counters` SET `sort` = '$sort2' WHERE `id` = " . $id);
                    App::db()->exec("UPDATE `" . TP . "system__counters` SET `sort` = '$sort' WHERE `id` = '$id2'");
                }
            }
        }

        header('Location: ' . $uri);
        break;

    case 'del':
        // Удаление счетчика
        if (!$id) {
            echo __('error_wrong_data') . ' <a href="' . $uri . '">' . __('back') . '</a>';
            exit;
        }
        $req = App::db()->query("SELECT * FROM `" . TP . "system__counters` WHERE `id` = " . $id);
        if ($req->rowCount()) {
            if (isset($_POST['submit'])) {
                App::db()->exec("DELETE FROM `" . TP . "system__counters` WHERE `id` = " . $id);
                echo '<p>' . __('counter_deleted') . '<br/><a href="' . $uri . '">' . __('continue') . '</a></p>';
                exit;
            } else {
                echo '<form action="' . $uri . '?act=del&amp;id=' . $id . '" method="post">';
                echo '<div class="phdr"><a href="' . $uri . '"><b>' . __('counters') . '</b></a> | ' . __('delete') . '</div>';
                $res = $req->fetch();
                echo '<div class="rmenu"><p><h3>' . htmlspecialchars($res['name']) . '</h3>' . __('delete_confirmation') . '</p><p><input type="submit" value="' . __('delete') . '" name="submit" /></p></div>';
                echo '<div class="phdr"><a href="' . $uri . '">' . __('cancel') . '</a></div></form>';
            }
        } else {
            echo __('error_wrong_data') . ' <a href="' . $uri . '">' . __('back') . '</a>';
            exit;
        }
        break;

    case 'edit':
        // Форма добавления счетчика
        if (isset($_POST['submit'])) {
            // Предварительный просмотр
            $name = isset($_POST['name']) ? mb_substr(trim($_POST['name']), 0, 25) : '';
            $link1 = isset($_POST['link1']) ? trim($_POST['link1']) : '';
            $link2 = isset($_POST['link2']) ? trim($_POST['link2']) : '';
            $mode = isset($_POST['mode']) ? intval($_POST['mode']) : 1;
            if (empty($name) || empty($link1)) {
                echo __('error_empty_fields') . ' <a href="' . $uri . '?act=edit' . ($id ? '&amp;id=' . $id : '') . '">' . __('back') . '</a>';
                exit;
            }
            echo '<div class="phdr"><a href="' . $uri . '"><b>' . __('counters') . '</b></a> | ' . __('preview') . '</div>' .
                '<div class="menu"><p><h3>' . __('title') . '</h3><b>' . htmlspecialchars($name) . '</b></p>' .
                '<p><h3>' . __('counter_mod1') . '</h3>' . $link1 . '</p>' .
                '<p><h3>' . __('counter_mod2') . '</h3>' . $link2 . '</p></div>' .
                '<div class="rmenu">' . __('counter_preview_help') . '</div>' .
                '<form action="' . $uri . '?act=add" method="post">' .
                '<input type="hidden" value="' . $name . '" name="name" />' .
                '<input type="hidden" value="' . htmlspecialchars($link1) . '" name="link1" />' .
                '<input type="hidden" value="' . htmlspecialchars($link2) . '" name="link2" />' .
                '<input type="hidden" value="' . $mode . '" name="mode" />';
            if ($id) {
                echo '<input type="hidden" value="' . $id . '" name="id" />';
            }
            echo '<div class="bmenu"><input type="submit" value="' . __('save') . '" name="submit" /></div>' .
                '</form>';
        } else {
            $name = '';
            $link1 = '';
            $link2 = '';
            $mode = 0;
            if ($id) {
                // запрос к базе, если счетчик редактируется
                $req = App::db()->query("SELECT * FROM `" . TP . "system__counters` WHERE `id` = " . $id);
                if ($req->rowCount()) {
                    $res = $req->fetch();
                    $name = htmlspecialchars($res['name']);
                    $link1 = htmlspecialchars($res['link1']);
                    $link2 = htmlspecialchars($res['link2']);
                    $mode = $res['mode'];
                    $switch = 1;
                } else {
                    echo __('error_wrong_data') . ' <a href="' . $uri . '">' . __('back') . '</a>';
                    exit;
                }
            }
            echo '<form action="' . $uri . '?act=edit" method="post">' .
                '<div class="phdr"><a href="' . $uri . '"><b>' . __('counters') . '</b></a> | ' . __('add') . '</div>' .
                '<div class="menu"><p><h3>' . __('title') . '</h3><input type="text" name="name" value="' . $name . '" /></p>' .
                '<p><h3>' . __('counter_mod1') . '</h3><textarea rows="3" name="link1">' . $link1 . '</textarea><br /><small>' . __('counter_mod1_description') . '</small></p>' .
                '<p><h3>' . __('counter_mod2') . '</h3><textarea rows="3" name="link2">' . $link2 . '</textarea><br /><small>' . __('counter_mod2_description') . '</small></p>' .
                '<p><h3>' . __('view_mode') . '</h3>' . '<input type="radio" value="1" ' . ($mode == 0 || $mode == 1 ? 'checked="checked" ' : '') . 'name="mode" />&#160;' . __('default') . '<br />' .
                '<small>' . __('counter_mod_default_help') . '</small></p><p>' .
                '<input type="radio" value="2" ' . ($mode == 2 ? 'checked="checked" ' : '') . 'name="mode" />&#160;' . __('counter_mod1') . '<br />' .
                '<input type="radio" value="3" ' . ($mode == 3 ? 'checked="checked" ' : '') . 'name="mode" />&#160;' . __('counter_mod2') . '</p></div>' .
                '<div class="rmenu"><small>' . __('counter_add_help') . '</small></div>';
            if ($id)
                echo '<input type="hidden" value="' . $id . '" name="id" />';
            echo '<div class="bmenu"><input type="submit" value="' . __('viewing') . '" name="submit" /></div>';
            echo '</form>';
        }
        break;

    case 'add':
        // Запись счетчика в базу
        $name = isset($_POST['name']) ? mb_substr($_POST['name'], 0, 25) : '';
        $link1 = isset($_POST['link1']) ? $_POST['link1'] : '';
        $link2 = isset($_POST['link2']) ? $_POST['link2'] : '';
        $mode = isset($_POST['mode']) ? intval($_POST['mode']) : 1;
        if (empty($name) || empty($link1)) {
            echo __('error_empty_fields') . ' <a href="' . $uri . '?act=edit' . ($id ? '&amp;id=' . $id : '') . '">' . __('back') . '</a>';
            exit;
        }
        if ($id) {
            // Режим редактирования
            $req = App::db()->query("SELECT * FROM `" . TP . "system__counters` WHERE `id` = " . $id);
            if ($req->rowCount() != 1) {
                echo __('error_wrong_data');
                exit;
            }

            $stmt = App::db()->prepare("
                UPDATE `" . TP . "system__counters` SET
                `name`     = ?,
                `link1`    = ?,
                `link2`    = ?,
                `mode`     = ?
                WHERE `id` = ?
            ");

            $stmt->execute(
                [
                    htmlspecialchars($name),
                    $link1,
                    $link2,
                    $mode,
                    $id
                ]
            );
            $stmt = null;
        } else {
            // Получаем значение сортировки
            $req = App::db()->query("SELECT `sort` FROM `" . TP . "system__counters` ORDER BY `sort` DESC LIMIT 1");
            if ($req->rowCount()) {
                $res = $req->fetch();
                $sort = $res['sort'] + 1;
            } else {
                $sort = 1;
            }

            // Режим добавления
            $stmt = App::db()->prepare("
                INSERT INTO `" . TP . "system__counters`
                (`name`, `sort`, `link1`, `link2`, `mode`)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute(
                [
                    htmlspecialchars($name),
                    $sort,
                    $link1,
                    $link2,
                    $mode
                ]
            );
            $stmt = null;
        }
        echo '<div class="gmenu"><p>' . ($id ? __('counter_edit_conf') : __('counter_add_conf')) . '<br/>' .
            '<a href="' . $uri . '">' . __('continue') . '</a>' .
            '</p></div>';
        break;

    default:
        /*
        -----------------------------------------------------------------
        Вывод списка счетчиков
        -----------------------------------------------------------------
        */
        echo '<div class="phdr"><a href="' . App::router()->getUri() . '"><b>' . __('admin_panel') . '</b></a> | ' . __('counters') . '</div>' .
            '<div class="gmenu"><form action="' . $uri . '?act=edit" method="post"><input type="submit" name="delete" value="' . __('add') . '"/></form></div>';
        $req = App::db()->query("SELECT * FROM `" . TP . "system__counters` ORDER BY `sort` ASC");
        $total = $req->rowCount();
        if ($total) {
            for ($i = 0; $res = $req->fetch(); ++$i) {
                echo ($i % 2 ? '<div class="list2">' : '<div class="list1">') .
                    Functions::getImage(($res['switch'] == 1 ? 'green' : 'red') . '.png', '', 'class="left"') . '&#160;' . //TODO: Переделать вызов функции
                    '<a href="' . $uri . '?act=view&amp;id=' . $res['id'] . '"><b>' . htmlspecialchars($res['name']) . '</b></a><br />' .
                    '<div class="sub">' .
                    '<a href="' . $uri . '?act=up&amp;id=' . $res['id'] . '">' . __('up') . '</a> | ' .
                    '<a href="' . $uri . '?act=down&amp;id=' . $res['id'] . '">' . __('down') . '</a> | ' .
                    '<a href="' . $uri . '?act=edit&amp;id=' . $res['id'] . '">' . __('edit') . '</a> | ' .
                    '<a href="' . $uri . '?act=del&amp;id=' . $res['id'] . '">' . __('delete') . '</a>' .
                    '</div>' .
                    '</div>';
            }
        } else {
            echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
        }
        echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
}
echo '<p>' . (App::request()->getQuery('mod', 0) ? '<a href="' . $uri . '">' . __('counters') . '</a><br />' : '') .
    '<a href="' . App::router()->getUri() . '">' . __('admin_panel') . '</a></p>';