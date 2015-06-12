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

$uri = App::router()->getUri() . 'links/';
$id = abs(intval(App::request()->getQuery('id', 0)));

switch (trim(App::request()->getQuery('act', ''))) {
    case 'edit':
        /*
        -----------------------------------------------------------------
        Добавляем / редактируем ссылку
        -----------------------------------------------------------------
        */
        echo '<div class="phdr"><a href="' . $uri . '"><b>' . __('advertisement') . '</b></a> | ' . ($id ? __('link_edit') : __('link_add')) . '</div>';
        if ($id) {
            // Если ссылка редактироется, запрашиваем ее данные в базе
            $req = App::db()->query("SELECT * FROM `system__advt` WHERE `id` = " . $id);
            if ($req->rowCount()) {
                $res = $req->fetch();
            } else {
                echo __('error_wrong_data') . ' <a href="' . $uri . '">' . __('back') . '</a>';
                exit;
            }
        } else {
            $res =
                [
                    'link'       => 'http://',
                    'show'       => 0,
                    'name'       => '',
                    'color'      => '',
                    'count_link' => 0,
                    'day'        => 7,
                    'view'       => 0,
                    'type'       => 0,
                    'layout'     => 0,
                    'bold'       => 0,
                    'italic'     => 0,
                    'underline'  => 0,
                ];
        }
        if (isset($_POST['submit'], $_POST['form_token'], $_SESSION['form_token'])
            && $_POST['form_token'] == $_SESSION['form_token']
        ) {
            $url = isset($_POST['link']) ? trim($_POST['link']) : '';
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $bold = isset($_POST['bold']) ? 1 : 0;
            $italic = isset($_POST['italic']) ? 1 : 0;
            $underline = isset($_POST['underline']) ? 1 : 0;
            $show = isset($_POST['show']) ? 1 : 0;
            $view = isset($_POST['view']) ? abs(intval($_POST['view'])) : 0;
            $day = isset($_POST['day']) ? abs(intval($_POST['day'])) : 0;
            $count = isset($_POST['count']) ? abs(intval($_POST['count'])) : 0;
            $day = isset($_POST['day']) ? abs(intval($_POST['day'])) : 0;
            $layout = isset($_POST['layout']) ? abs(intval($_POST['layout'])) : 0;
            $type = isset($_POST['type']) && $_POST['type'] >= 0 && $_POST['type'] <= 3 ? intval($_POST['type']) : 0;
            $mesto = isset($_POST['mesto']) ? abs(intval($_POST['mesto'])) : 0;
            $color = isset($_POST['color']) ? mb_substr(trim($_POST['color']), 0, 6) : '';
            $error = [];
            if (!$url || !$name)
                $error[] = __('error_empty_fields');
            if (!$mesto) {
                $total = App::db()->query("SELECT COUNT(*) FROM `system__advt` WHERE `mesto` = '" . $mesto . "' AND `type` = '" . $type . "'")->fetchColumn();
                if ($total != 0)
                    $error[] = __('links_place_occupied');
            }
            if ($color) {
                if (preg_match("/[^\da-fA-F_]+/", $color))
                    $error[] = __('error_wrong_symbols');
                if (strlen($color) < 6)
                    $error[] = __('error_color');
            }
            if ($error) {
                echo $error . ' <a href="' . $uri . '?from=addlink">' . __('back') . '</a>';
                exit;
            }
            if ($id) {
                // Обновляем ссылку после редактирования
                $stmt = App::db()->prepare("
                    UPDATE `system__advt` SET
                    `type` = ?,
                    `view` = ?,
                    `link` = ?,
                    `name` = ?,
                    `color` = ?,
                    `count_link` = ?,
                    `day` = ?,
                    `layout` = ?,
                    `bold` = ?,
                    `show` = ?,
                    `italic` = ?,
                    `underline` = ?
                    WHERE `id` = ?
                ");

                $stmt->execute(
                    [
                        $type,
                        $view,
                        $url,
                        $name,
                        $color,
                        $count,
                        $day,
                        $layout,
                        $bold,
                        $show,
                        $italic,
                        $underline,
                        $id
                    ]
                );
                $stmt = null;
            } else {
                // Добавляем новую ссылку
                $req = App::db()->query("SELECT `mesto` FROM `system__advt` ORDER BY `mesto` DESC LIMIT 1");
                if ($req->rowCount()) {
                    $res = $req->fetch();
                    $mesto = $res['mesto'] + 1;
                } else {
                    $mesto = 1;
                }
                $stmt = App::db()->prepare("
                    INSERT INTO `system__advt`
                    (`type`, `view`, `mesto`, `link`, `name`, `color`, `count_link`, `day`, `layout`, `show`, `time`, `bold`, `italic`, `underline`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute(
                    [
                        $type,
                        $view,
                        $mesto,
                        $url,
                        $name,
                        $color,
                        $count,
                        $day,
                        $layout,
                        $show,
                        time(),
                        $bold,
                        $italic,
                        $underline
                    ]
                );
                $stmt = null;
            }

            App::db()->exec("UPDATE `user__` SET `lastpost` = '" . time() . "' WHERE `id` = " . App::user()->id);
            echo '<div class="menu"><p>' . ($id ? __('link_edit_ok') : __('link_add_ok')) . '<br />' .
                '<a href="' . $uri . '?sort=' . $type . '">' . __('continue') . '</a></p></div>';
        } else {
            //TODO: Переделать на конструктор форм
            App::view()->res = $res;
            App::view()->form_token = mt_rand(100, 10000);
            $_SESSION['form_token'] = App::view()->form_token;
            App::view()->contents = App::view()->includeTpl('links_edit');
        }
        break;

    case 'down':
        /*
        -----------------------------------------------------------------
        Перемещаем на позицию вниз
        -----------------------------------------------------------------
        */
        if ($id) {
            $req = App::db()->query("SELECT `mesto`, `type` FROM `system__advt` WHERE `id` = " . $id);
            if ($req->rowCount()) {
                $res = $req->fetch();
                $mesto = $res['mesto'];
                $req = App::db()->query("SELECT * FROM `system__advt` WHERE `mesto` > '$mesto' AND `type` = '" . $res['type'] . "' ORDER BY `mesto` ASC");
                if ($req->rowCount()) {
                    $res = $req->fetch();
                    $id2 = $res['id'];
                    $mesto2 = $res['mesto'];
                    App::db()->exec("UPDATE `system__advt` SET `mesto` = '$mesto2' WHERE `id` = " . $id);
                    App::db()->exec("UPDATE `system__advt` SET `mesto` = '$mesto' WHERE `id` = '$id2'");
                }
            }
        }

        header('Location: ' . getenv("HTTP_REFERER"));
        break;

    case 'up':
        /*
        -----------------------------------------------------------------
        Перемещаем на позицию вверх
        -----------------------------------------------------------------
        */
        if ($id) {
            $req = App::db()->query("SELECT `mesto`, `type` FROM `system__advt` WHERE `id` = " . $id);
            if ($req->rowCount()) {
                $res = $req->fetch();
                $mesto = $res['mesto'];
                $req = App::db()->query("SELECT * FROM `system__advt` WHERE `mesto` < '$mesto' AND `type` = '" . $res['type'] . "' ORDER BY `mesto` DESC");
                if ($req->rowCount()) {
                    $res = $req->fetch();
                    $id2 = $res['id'];
                    $mesto2 = $res['mesto'];
                    App::db()->exec("UPDATE `system__advt` SET `mesto` = '$mesto2' WHERE `id` = " . $id);
                    App::db()->exec("UPDATE `system__advt` SET `mesto` = '$mesto' WHERE `id` = '$id2'");
                }
            }
        }

        header('Location: ' . getenv("HTTP_REFERER") . '');
        break;

    case 'del':
        /*
        -----------------------------------------------------------------
        Удаляем ссылку
        -----------------------------------------------------------------
        */
        if ($id) {
            if (isset($_POST['submit'])) {
                App::db()->exec("DELETE FROM `system__advt` WHERE `id` = " . $id);

                header('Location: ' . $_POST['ref']);
            } else {
                echo '<div class="phdr"><a href="' . $uri . '"><b>' . __('advertisement') . '</b></a> | ' . __('delete') . '</div>' .
                    '<div class="rmenu"><form action="' . $uri . '?act=del&amp;id=' . $id . '" method="post">' .
                    '<p>' . __('link_deletion_warning') . '</p>' .
                    '<p><input type="submit" name="submit" value="' . __('delete') . '" /></p>' .
                    '<input type="hidden" name="ref" value="' . htmlspecialchars($_SERVER['HTTP_REFERER']) . '" />' .
                    '</form></div>' .
                    '<div class="phdr"><a href="' . htmlspecialchars($_SERVER['HTTP_REFERER']) . '">' . __('cancel') . '</a></div>';
            }
        }
        break;

    case 'clear':
        /*
        -----------------------------------------------------------------
        Очистка базы от неактивных ссылок
        -----------------------------------------------------------------
        */
        if (isset($_POST['submit'])) {
            App::db()->exec("DELETE FROM `system__advt` WHERE `to` = '1'");
            App::db()->query("OPTIMIZE TABLE `system__advt`");

            header('location: ' . $uri);
        } else {
            echo '<div class="phdr"><a href="' . $uri . '"><b>' . __('advertisement') . '</b></a> | ' . __('links_delete_hidden') . '</div>' .
                '<div class="menu"><form method="post" action="' . $uri . '?act=clear">' .
                '<p>' . __('link_clear_warning') . '</p>' .
                '<p><input type="submit" name="submit" value="' . __('delete') . '" />' .
                '</p></form></div>' .
                '<div class="phdr"><a href="' . $uri . '">' . __('cancel') . '</a></div>';
        }
        break;

    case 'show':
        /*
        -----------------------------------------------------------------
        Восстанавливаем / скрываем ссылку
        -----------------------------------------------------------------
        */
        if ($id) {
            $req = App::db()->query("SELECT * FROM `system__advt` WHERE `id` = " . $id);
            if ($req->rowCount()) {
                $res = $req->fetch();
                App::db()->exec("UPDATE `system__advt` SET `to`='" . ($res['to'] ? 0 : 1) . "' WHERE `id` = " . $id);
            }
        }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;

    default:
        /*
        -----------------------------------------------------------------
        Главное меню модуля управления рекламой
        -----------------------------------------------------------------
        */
        echo '<div class="phdr"><a href="' . App::router()->getUri() . '"><b>' . __('admin_panel') . '</b></a> | ' . __('advertisement') . '</div>';
        $array_placing =
            [
                __('link_add_placing_all'),
                __('link_add_placing_front'),
                __('link_add_placing_child')
            ];
        $array_show =
            [
                __('to_all'),
                __('to_guest'),
                __('to_users')
            ];
        $type = isset($_GET['type']) ? intval($_GET['type']) : 0;
        $array_menu =
            [
                (!$type ? __('endwise') : '<a href="' . $uri . '">' . __('endwise') . '</a>'),
                ($type == 1 ? __('above_content') : '<a href="' . $uri . '?type=1">' . __('above_content') . '</a>'),
                ($type == 2 ? __('below_content') : '<a href="' . $uri . '?type=2">' . __('below_content') . '</a>'),
                ($type == 3 ? __('below') : '<a href="' . $uri . '?type=3">' . __('below') . '</a>')
            ];
        echo '<div class="topmenu">' . Functions::displayMenu($array_menu) . '</div>';
        $total = App::db()->query("SELECT COUNT(*) FROM `system__advt` WHERE `type` = '$type'")->fetchColumn();
        if ($total) {
            $req = App::db()->query("SELECT * FROM `system__advt` WHERE `type` = '$type' ORDER BY `mesto` ASC " . App::db()->pagination());
            $i = 0;
            while ($res = $req->fetch()) {
                echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                $name = str_replace('|', '; ', $res['name']);
                $name = htmlspecialchars($name);
                // Если был задан цвет, то применяем
                if (!empty($res['color']))
                    $name = '<span style="color:#' . $res['color'] . '">' . $name . '</span>';
                // Если было задано начертание шрифта, то применяем
                $font = $res['bold'] ? 'font-weight: bold;' : false;
                $font .= $res['italic'] ? ' font-style:italic;' : false;
                $font .= $res['underline'] ? ' text-decoration:underline;' : false;
                if ($font)
                    $name = '<span style="' . $font . '">' . $name . '</span>';

                // Выводим рекламмную ссылку с атрибутами
                echo '<p>' . Functions::getImage(($res['to'] ? 'red' : 'green') . '.png', '', 'class="left"') . '&#160;' .
                    '<a href="' . htmlspecialchars($res['link']) . '">' . htmlspecialchars($res['link']) . '</a>&nbsp;[' . $res['count'] . ']<br />' . $name . '</p>';
                $menu =
                    [
                        '<a href="' . $uri . '?act=up&amp;id=' . $res['id'] . '">' . __('up') . '</a>',
                        '<a href="' . $uri . '?act=down&amp;id=' . $res['id'] . '">' . __('down') . '</a>',
                        '<a href="' . $uri . '?act=edit&amp;id=' . $res['id'] . '">' . __('edit') . '</a>',
                        '<a href="' . $uri . '?act=del&amp;id=' . $res['id'] . '">' . __('delete') . '</a>',
                        '<a href="' . $uri . '?act=show&amp;id=' . $res['id'] . '">' . ($res['to'] ? __('to_show') : __('hide')) . '</a>'
                    ];
                echo '<div class="sub">' .
                    '<div>' . Functions::displayMenu($menu) . '</div>' .
                    '<p><span class="gray">' . __('installation_date') . ':</span> ' . Functions::displayDate($res['time']) . '<br />' .
                    '<span class="gray">' . __('placing') . ':</span>&nbsp;' . $array_placing[$res['layout']] . '<br />' .
                    '<span class="gray">' . __('to_show') . ':</span>&nbsp;' . $array_show[$res['view']];
                // Вычисляем условия договора на рекламу
                $agreement = [];
                $remains = [];
                if (!empty($res['count_link'])) {
                    $agreement[] = $res['count_link'] . ' ' . __('transitions_n');
                    $remains_count = $res['count_link'] - $res['count'];
                    if ($remains_count > 0)
                        $remains[] = $remains_count . ' ' . __('transitions_n');
                }
                if (!empty($res['day'])) {
                    $agreement[] = Functions::timeCount($res['day'] * 86400);
                    $remains_count = $res['day'] * 86400 - (time() - $res['time']);
                    if ($remains_count > 0)
                        $remains[] = Functions::timeCount($remains_count);
                }
                // Если был договор, то выводим описание
                if ($agreement) {
                    echo '<br /><span class="gray">' . __('agreement') . ':</span>&nbsp;' . implode($agreement, ', ');
                    if ($remains)
                        echo '<br /><span class="gray">' . __('remains') . ':</span> ' . implode($remains, ', ');
                }
                echo ($res['show'] ? '<br /><span class="red"><b>' . __('link_direct') . '</b></span>' : '') . '</p></div></div>';
                ++$i;
            }
        } else {
            echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
        }
        echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
        if ($total > App::user()->settings['page_size']) {
            echo '<div class="topmenu">' . Functions::displayPagination($uri . '?type=' . $type . '&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
                '<p><form action="' . $uri . '?type=' . $type . '" method="post">' .
                '<input type="text" name="page" size="2"/>' .
                '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/></form></p>';
        }
        echo '<p><a href="' . $uri . '?act=edit">' . __('link_add') . '</a><br />' .
            '<a href="' . $uri . '?act=clear">' . __('links_delete_hidden') . '</a><br />' .
            '<a href="' . App::router()->getUri() . '">' . __('admin_panel') . '</a></p>';
}