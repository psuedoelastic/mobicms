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

// Закрываем доступ для определенных ситуаций
if (!$id || isset(App::user()->ban['1']) || isset(App::user()->ban['11']) || (!App::user()->rights && App::cfg()->sys->acl_forum == 3)) {
    echo __('access_forbidden');
    exit;
}

$url = App::router()->getUri(1);
$settings = Forum::settings();

// Проверка на флуд
$flood = Functions::antiFlood();
if ($flood) {
    echo __('error_flood') . ' ' . $flood . __('sec'), '<a href="?id=' . $id . '&amp;start=' . App::vars()->start . '">' . __('back') . '</a>';
    exit;
}

$type = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = " . $id)->fetch();
switch ($type['type']) {
    case 't':
        // Добавление простого сообщения
        if (($type['edit'] == 1 || $type['close'] == 1) && App::user()->rights < 7) {
            // Проверка, закрыта ли тема
            echo __('error_topic_closed') . ' <a href="' . $url . '?id=' . $id . '">' . __('back') . '</a>';
            exit;
        }
        $msg = isset($_POST['msg']) ? trim($_POST['msg']) : '';

        //Обрабатываем ссылки
        $msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', 'Forum::forum_link', $msg);
        if (isset($_POST['submit']) && !empty($_POST['msg'])) {
            // Проверяем на минимальную длину
            if (mb_strlen($msg) < 4) {
                echo __('error_message_short') . '<a href="' . $url . '?id=' . $id . '">' . __('back') . '</a>';
                exit;
            }
            // Проверяем, не повторяется ли сообщение?
            $req = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `user_id` = " . App::user()->id . " AND `type` = 'm' ORDER BY `time` DESC");
            if ($req->rowCount()) {
                $res = $req->fetch();
                if ($msg == $res['text']) {
                    echo __('error_message_exists') . ' <a href="?id=' . $id . '&amp;start=' . App::vars()->start . '">' . __('back') . '</a>';
                    exit;
                }
            }
            // Удаляем фильтр, если он был
            if (isset($_SESSION['fsort_id']) && $_SESSION['fsort_id'] == $id) {
                unset($_SESSION['fsort_id']);
                unset($_SESSION['fsort_users']);
            }

            // Добавляем сообщение в базу
            $stmt = App::db()->prepare("
                INSERT INTO `" . TP . "forum__`
                (`refid`, `type`, `time`, `user_id`, `from`, `ip`, `ip_via_proxy`, `soft`, `text`, `edit`, `curators`)
                VALUES (?, 'm', ?, ?, ?, ?, ?, ?, ?, '', '')
            ");

            $stmt->execute([
                $id,
                time(),
                App::user()->id,
                App::user()->data['nickname'],
                App::network()->getIp(),
                App::network()->getIpViaProxy(),
                App::network()->getUserAgent(),
                $msg
            ]);
            $fadd = App::db()->lastInsertId();

            // Обновляем время топика
            App::db()->exec("UPDATE `" . TP . "forum__` SET `time` = '" . time() . "' WHERE `id` = " . $id);

            // Обновляем статистику юзера
            App::db()->exec("UPDATE `" . TP . "user__` SET
                `count_forum` = '" . ++App::user()->data['count_forum'] . "',
                `lastpost` = '" . time() . "'
                WHERE `id` = " . App::user()->id
            );

            // Вычисляем, на какую страницу попадает добавляемый пост
            $page = $settings['upfp'] ? 1 : ceil(App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `type` = 'm' AND `refid` = " . $id . (App::user()->rights >= 7 ? '' : " AND `close` != '1'"))->fetchColumn() / App::user()->settings['page_size']);
            if (isset($_POST['addfiles']))
                header("Location: " . $url . "?id=$fadd&act=addfile");
            else
                header("Location: " . $url . "?id=" . $id . "&page=$page");
        } else {
            if (!App::user()->data['count_forum']) {
                if (!isset($_GET['yes'])) {
                    echo '<p>' . __('forum_rules_text') . '</p>' .
                        '<p><a href="' . $url . 'say/?id=' . $id . '&amp;yes">' . __('agree') . '</a> | ' .
                        '<a href="' . $url . '?id=' . $id . '">' . __('not_agree') . '</a></p>';
                    exit;
                }
            }
            $msg_pre = htmlspecialchars($msg);
            if (App::user()->settings['smilies'])
                $msg_pre = Functions::smilies($msg_pre, App::user()->rights ? 1 : 0);
            $msg_pre = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $msg_pre);
            echo '<div class="phdr"><b>' . __('topic') . ':</b> ' . $type['text'] . '</div>';
            if ($msg && !isset($_POST['submit']))
                echo '<div class="list1">' . Functions::displayUser(App::user()->data, ['iphide' => 1,
                                                                                        'header' => '<span class="gray">(' . Functions::displayDate(time()) . ')</span>',
                                                                                        'body'   => $msg_pre]) . '</div>';
            echo '<form name="form" action="' . $url . 'say/?id=' . $id . '&amp;start=' . App::vars()->start . '" method="post"><div class="gmenu">' .
                '<p><h3>' . __('post') . '</h3>';
            echo '<textarea rows="' . App::user()->settings['field_h'] . '" name="msg">' . (empty($_POST['msg']) ? '' : htmlspecialchars($msg)) . '</textarea></p>' .
                '<p><input type="checkbox" name="addfiles" value="1" ' . (isset($_POST['addfiles']) ? 'checked="checked" ' : '') . '/> ' . __('add_file');
            echo '</p><p><input type="submit" name="submit" value="' . __('sent') . '" style="width: 107px; cursor: pointer;"/> ' .
                ($settings['preview'] ? '<input type="submit" value="' . __('preview') . '" style="width: 107px; cursor: pointer;"/>' : '') .
                '</p></div></form>';
        }
        echo '<div class="phdr"><a href="../pages/faq.php?act=trans">' . __('translit') . '</a> | ' .
            '<a href="../pages/faq.php?act=smilies">' . __('smilies') . '</a></div>' .
            '<p><a href="?id=' . $id . '&amp;start=' . App::vars()->start . '">' . __('back') . '</a></p>';
        break;

    case 'm':
        // Добавление сообщения с цитированием поста
        $th = $type1['refid'];
        $th1 = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = " . $th)->fetch();
        if (($th1['edit'] == 1 || $th1['close'] == 1) && App::user()->rights < 7) {
            echo __('error_topic_closed') . ' <a href="' . $url . '?id=' . $th1['id'] . '">' . __('back') . '</a>';
            exit;
        }
        if ($type['user_id'] == App::user()->id) {
            //TODO: перевести
            echo 'Нельзя отвечать на свое же сообщение' . ' <a href="' . $url . '?id=' . $th1['id'] . '">' . __('back') . '</a>';
            exit;
        }
        $shift = (App::cfg()->sys->timeshift + App::cfg()->sys->timeshift) * 3600;
        $vr = date("d.m.Y / H:i", $type['time'] + $shift);
        $msg = isset($_POST['msg']) ? trim($_POST['msg']) : '';
        $txt = isset($_POST['txt']) ? intval($_POST['txt']) : false;
        $to = $type['from'];
        if (!empty($_POST['citata'])) {
            // Если была цитата, форматируем ее и обрабатываем
            $citata = isset($_POST['citata']) ? trim($_POST['citata']) : '';
            $citata = htmlspecialchars($citata);
            $citata = preg_replace('#\[c\](.*?)\[/c\]#si', '', $citata);
            $citata = mb_substr($citata, 0, 200);
            $tp = date("d.m.Y/H:i", $type['time']);
            $msg = '[c]' . $to . ' (' . $tp . ")\r\n" . $citata . '[/c]' . $msg;
        } elseif (isset($_POST['txt'])) {
            // Если был ответ, обрабатываем реплику
            switch ($txt) {
                case 2:
                    $repl = $type['from'] . ', ' . __('reply_1') . ', ';
                    break;

                case 3:
                    $repl = $type['from'] . ', ' . __('reply_2') . ' ([url=' . App::cfg()->sys->homeurl . 'forum/?act=post&id=' . $type['id'] . ']' . $vr . '[/url]) ' . __('reply_3') . ', ';
                    break;

                case 4:
                    $repl = $type['from'] . ', ' . __('reply_4') . ' ';
                    break;

                default :
                    $repl = $type['from'] . ', ';
            }
            $msg = $repl . ' ' . $msg;
        }
        //Обрабатываем ссылки
        $msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', 'forum_link', $msg);
        if (isset($_POST['submit'])) {
            if (empty($_POST['msg'])) {
                echo __('error_empty_message') . ' <a href="' . $url . '?act=say&amp;id=' . $th . (isset($_GET['cyt']) ? '&amp;cyt' : '') . '">' . __('repeat') . '</a>';
                exit;
            }
            // Проверяем на минимальную длину
            if (mb_strlen($msg) < 4) {
                echo __('error_message_short') . ' <a href="' . $url . '?id=' . $id . '">' . __('back') . '</a>';
                exit;
            }
            // Проверяем, не повторяется ли сообщение?
            $req = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `user_id` = " . App::user()->id . " AND `type` = 'm' ORDER BY `time` DESC LIMIT 1");
            if ($req->rowCount()) {
                $res = $req->fetch();
                if ($msg == $res['text']) {
                    echo __('error_message_exists') . ' <a href="' . $url . '?id=' . $th . '&amp;start=' . App::vars()->start . '">' . __('back') . '</a>';
                    exit;
                }
            }
            // Удаляем фильтр, если он был
            if (isset($_SESSION['fsort_id']) && $_SESSION['fsort_id'] == $th) {
                unset($_SESSION['fsort_id']);
                unset($_SESSION['fsort_users']);
            }
            // Добавляем сообщение в базу
            // Добавляем сообщение в базу
            $stmt = $stmt = App::db()->prepare("
                INSERT INTO `" . TP . "forum__`
                (`refid`, `type`, `time`, `user_id`, `from`, `ip`, `ip_via_proxy`, `soft`, `text`, `edit`, `curators`)
                VALUES (?, 'm', ?, ?, ?, ?, ?, ?, ?, '', '')
            ");

            $stmt->execute([
                $th,
                time(),
                App::user()->id,
                App::user()->data['nickname'],
                App::network()->getIp(),
                App::network()->getIpViaProxy(),
                App::network()->getUserAgent(),
                $msg
            ]);
            $fadd = App::db()->lastInsertId();

            // Обновляем время топика
            App::db()->exec("UPDATE `" . TP . "forum__`
                SET `time` = '" . time() . "'
                WHERE `id` = '$th'
            ");

            // Обновляем статистику юзера
            App::db()->exec("UPDATE `" . TP . "user__` SET
                `count_forum__` = '" . ++App::user()->data['count_forum'] . "',
                `lastpost` = '" . time() . "'
                WHERE `id` = " . App::user()->id
            );

            // Вычисляем, на какую страницу попадает добавляемый пост
            $page = $settings['upfp'] ? 1 : ceil(App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `type` = 'm' AND `refid` = '$th'" . (App::user()->rights >= 7 ? '' : " AND `close` != '1'"))->fetchColumn() / App::user()->settings['page_size']);
            $addfiles = intval($_POST['addfiles']);
            if ($addfiles == 1) {
                header("Location: " . $url . "?id=$fadd&act=addfile");
            } else {
                header("Location: " . $url . "?id=$th&page=$page");
            }
        } else {
            $qt = " $type[text]";
            if ((App::user()->data['count_forum'] == "" || App::user()->data['count_forum'] == 0)) {
                if (!isset($_GET['yes'])) {
                    echo '<p>' . __('forum_rules_text') . '</p>';
                    echo '<p><a href="' . $url . '?act=say&amp;id=' . $id . '&amp;yes&amp;cyt">' . __('agree') . '</a> | <a href="' . $url . '?id=' . $type['refid'] . '">' . __('not_agree') . '</a></p>';
                    exit;
                }
            }
            $msg_pre = htmlspecialchars($msg);
            if (App::user()->settings['smilies'])
                $msg_pre = Functions::smilies($msg_pre, App::user()->rights ? 1 : 0);
            $msg_pre = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $msg_pre);
            echo '<div class="phdr"><b>' . __('topic') . ':</b> ' . $th1['text'] . '</div>';
            $qt = str_replace("<br/>", "\r\n", $qt);
            $qt = trim(preg_replace('#\[c\](.*?)\[/c\]#si', '', $qt));
            $qt = htmlspecialchars($qt);
            if (!empty($_POST['msg']) && !isset($_POST['submit']))
                echo '<div class="list1">' . Functions::displayUser(App::user()->data, ['iphide' => 1,
                                                                                        'header' => '<span class="gray">(' . Functions::displayDate(time()) . ')</span>',
                                                                                        'body'   => $msg_pre]) . '</div>';
            echo '<form name="form" action="?act=say&amp;id=' . $id . '&amp;start=' . App::vars()->start . (isset($_GET['cyt']) ? '&amp;cyt' : '') . '" method="post"><div class="gmenu">';
            if (isset($_GET['cyt'])) {
                // Форма с цитатой
                echo '<p><b>' . $type['from'] . '</b> <span class="gray">(' . date("d.m.Y/H:i", $type['time']) . ')</span></p>' .
                    '<p><h3>' . __('cytate') . '</h3>' .
                    '<textarea rows="' . App::user()->settings['field_h'] . '" name="citata">' . (empty($_POST['citata']) ? $qt : htmlspecialchars($_POST['citata'])) . '</textarea>' .
                    '<br /><small>' . __('cytate_help') . '</small></p>';
            } else {
                // Форма с репликой
                echo '<p><h3>' . __('reference') . '</h3>' .
                    '<input type="radio" value="0" ' . (!$txt ? 'checked="checked"' : '') . ' name="txt" />&#160;<b>' . $type['from'] . '</b>,<br />' .
                    '<input type="radio" value="2" ' . ($txt == 2 ? 'checked="checked"' : '') . ' name="txt" />&#160;<b>' . $type['from'] . '</b>, ' . __('reply_1') . ',<br />' .
                    '<input type="radio" value="3" ' . ($txt == 3 ? 'checked="checked"'
                        : '') . ' name="txt" />&#160;<b>' . $type['from'] . '</b>, ' . __('reply_2') . ' (<a href="' . $url . '?act=post&amp;id=' . $type['id'] . '">' . $vr . '</a>) ' . __('reply_3') . ',<br />' .
                    '<input type="radio" value="4" ' . ($txt == 4 ? 'checked="checked"' : '') . ' name="txt" />&#160;<b>' . $type['from'] . '</b>, ' . __('reply_4') . '</p>';
            }
            echo '<p><h3>' . __('post') . '</h3>';
            echo '<textarea rows="' . App::user()->settings['field_h'] . '" name="msg">' . (empty($_POST['msg']) ? '' : htmlspecialchars($_POST['msg'])) . '</textarea></p>' .
                '<p><input type="checkbox" name="addfiles" value="1" ' . (isset($_POST['addfiles']) ? 'checked="checked" ' : '') . '/> ' . __('add_file');
            echo '</p><p><input type="submit" name="submit" value="' . __('sent') . '" style="width: 107px; cursor: pointer;"/> ' .
                ($settings['preview'] ? '<input type="submit" value="' . __('preview') . '" style="width: 107px; cursor: pointer;"/>' : '') .
                '</p></div></form>';
        }
        echo '<div class="phdr"><a href="../pages/faq.php?act=trans">' . __('translit') . '</a> | ' .
            '<a href="../pages/faq.php?act=smilies">' . __('smilies') . '</a></div>' .
            '<p><a href="?id=' . $type['refid'] . '&amp;start=' . App::vars()->start . '">' . __('back') . '</a></p>';
        break;

    default:
        echo __('error_topic_deleted') . ' <a href="' . $url . '">' . __('to_forum') . '</a>';
}
