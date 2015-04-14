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

// Вспомогательная Функция обработки ссылок форума
function forum_link($m)
{
    if (!isset($m[3])) {
        return '[url=' . $m[1] . ']' . $m[2] . '[/url]';
    } else {
        $p = parse_url($m[3]);
        if ('http://' . $p['host'] . $p['path'] . '?id=' == App::cfg()->sys->homeurl . 'forum/?id=') {
            $thid = abs(intval(preg_replace('/(.*?)id=/si', '', $m[3])));
            $req = App::db()->query("SELECT `text` FROM `" . TP . "forum__` WHERE `id`= '$thid' AND `type` = 't' AND `close` != '1'");
            if ($req->rowCount()) {
                $res = $req->fetch();
                $name = strtr($res['text'], [
                    '&quot;' => '',
                    '&amp;'  => '',
                    '&lt;'   => '',
                    '&gt;'   => '',
                    '&#039;' => '',
                    '['      => '',
                    ']'      => ''
                ]);
                if (mb_strlen($name) > 40)
                    $name = mb_substr($name, 0, 40) . '...';

                return '[url=' . $m[3] . ']' . $name . '[/url]';
            } else {
                return $m[3];
            }
        } else
            return $m[3];
    }
}

// Проверка на флуд
$flood = Functions::antiFlood();
if ($flood) {
    echo __('error_flood') . ' ' . $flood . __('sec') . ', <a href="' . $url . '?id=' . $id . '&amp;start=' . App::vars()->start . '">' . __('back') . '</a>';
    exit;
}
$req_r = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = " . $id . " AND `type` = 'r' LIMIT 1");
if (!$req_r->rowCount()) {
    echo __('error_wrong_data');
    exit;
}
$th = isset($_POST['th']) ? htmlspecialchars(mb_substr(trim($_POST['th']), 0, 100)) : '';
$msg = isset($_POST['msg']) ? trim($_POST['msg']) : '';
$msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', 'forum_link', $msg);
if (isset($_POST['submit'])) {
    $error = [];
    if (empty($th))
        $error[] = __('error_topic_name');
    if (mb_strlen($th) < 2)
        $error[] = __('error_topic_name_lenght');
    if (empty($msg))
        $error[] = __('error_empty_message');
    if (mb_strlen($msg) < 4)
        $error[] = __('error_message_short');
    if (!$error) {
        $msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', 'forum_link', $msg);

        // Прверяем, есть ли уже такая тема в текущем разделе?
        $stmt = App::db()->prepare("
            SELECT COUNT(*) FROM `" . TP . "forum__`
            WHERE `type` = ?
            AND `refid` = ?
            AND `text` = ?
        ");

        $stmt->execute(['t', $id, $th]);

        if ($stmt->fetchColumn()) {
            $error[] = __('error_topic_exists');
        }

        $stmt = null;

        // Проверяем, не повторяется ли сообщение?
        $req = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `user_id` = " . App::user()->id . " AND `type` = 'm' ORDER BY `time` DESC");
        if ($req->rowCount()) {
            $res = $req->fetch();
            if ($msg == $res['text'])
                $error[] = __('error_message_exists');
        }
    }
    if (!$error) {
        // Добавляем тему
        $stmt = App::db()->prepare("
            INSERT INTO `" . TP . "forum__`
            (`refid`, `type`, `time`, `user_id`, `from`, `text`, `soft`, `edit`)
            VALUES (?, 't', ?, ?, ?, ?, '', '')
        ");

        $stmt->execute([
            $id,
            time(),
            App::user()->id,
            App::user()->data['nickname'],
            $th
        ]);
        $rid = App::db()->lastInsertId();
        $stmt = null;

        // Добавляем текст поста
        $stmt = App::db()->prepare("
            INSERT INTO `" . TP . "forum__`
            (`refid`, `type`, `time`, `user_id`, `from`, `ip`, `ip_via_proxy`, `soft`, `text`, `edit`)
            VALUES (?, 'm', ?, ?, ?, ?, ?, ?, ?, '')
        ");

        $stmt->execute([
            $rid,
            time(),
            App::user()->id,
            App::user()->data['nickname'],
            App::network()->getIp(),
            App::network()->getIpViaProxy(),
            App::network()->getUserAgent(),
            $msg
        ]);
        $postid = App::db()->lastInsertId();

        // Записываем счетчик постов юзера
        App::db()->exec("UPDATE `" . TP . "user__` SET
            `count_forum` = '" . ++App::user()->data['count_forum'] . "',
            `lastpost` = '" . time() . "'
            WHERE `id` = " . App::user()->id . "
        ");

        // Ставим метку о прочтении
        App::db()->exec("INSERT INTO `" . TP . "forum__rdm` SET
            `topic_id` = '$rid',
            `user_id` = " . App::user()->id . ",
            `time` = '" . time() . "'
        ");

        if (isset($_POST['addfiles'])) {
            header('Location: ' . $url . '?id=' . $postid . '&act=addfile');
        } else {
            header('Location: ' . $url . '?id=' . $rid);
        }
    } else {
        // Выводим сообщение об ошибке
        echo $error . ' <a href="' . $url . '?act=nt&amp;id=' . $id . '">' . __('repeat') . '</a>';
        exit;
    }
} else {
    $res_r = $req_r->fetch();
    $res_c = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = '" . $res_r['refid'] . "'")->fetch();
    if (!App::user()->data['count_forum']) {
        if (!isset($_GET['yes'])) {
            echo '<div class="news-text">' . __('rules_text_1') . '</div>';
            echo '<p><a href="' . $url . 'new_topic/?id=' . $id . '&amp;yes">' . __('agree') . '</a> | <a href="' . $url . '?id=' . $id . '">' . __('not_agree') . '</a></p>';
            exit;
        }
    }
    $msg_pre = htmlspecialchars($msg);
    if (App::user()->settings['smilies'])
        $msg_pre = Functions::smilies($msg_pre, App::user()->rights ? 1 : 0);
    $msg_pre = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $msg_pre);
    echo '<div class="phdr"><a href="' . $url . '?id=' . $id . '"><b>' . __('forum') . '</b></a> | ' . __('new_topic') . '</div>';
    if ($msg && $th && !isset($_POST['submit']))
        echo '<div class="list1">' . Functions::getIcon('forum_normal.png') . '&#160;<span style="font-weight: bold">' . $th . '</span></div>' .
            '<div class="list2">' . Functions::displayUser(App::user()->data, ['iphide' => 1,
                                                                               'header' => '<span class="gray">(' . Functions::displayDate(time()) . ')</span>',
                                                                               'body'   => $msg_pre]) . '</div>';
    echo '<form name="form" action="' . $url . 'new_topic/?id=' . $id . '" method="post">' .
        '<div class="gmenu">' .
        '<p><h3>' . __('section') . '</h3>' .
        '<a href="' . $url . '?id=' . $res_c['id'] . '">' . $res_c['text'] . '</a> | <a href="' . $url . '?id=' . $res_r['id'] . '">' . $res_r['text'] . '</a></p>' .
        '<p><h3>' . __('new_topic_name') . '</h3>' .
        '<input type="text" size="20" maxlength="100" name="th" value="' . $th . '"/></p>' .
        '<p><h3>' . __('post') . '</h3></p>';
    echo '<p><textarea rows="' . App::user()->settings['field_h'] . '" name="msg">' . (isset($_POST['msg']) ? htmlspecialchars($_POST['msg']) : '') . '</textarea></p>' .
        '<p><input type="checkbox" name="addfiles" value="1" ' . (isset($_POST['addfiles']) ? 'checked="checked" ' : '') . '/> ' . __('add_file');
    echo '</p><p><input type="submit" name="submit" value="' . __('save') . '" style="width: 107px; cursor: pointer;"/> ' .
        ($settings['preview'] ? '<input type="submit" value="' . __('preview') . '" style="width: 107px; cursor: pointer;"/>' : '') .
        '</p></div></form>' .
        '<div class="phdr"><a href="../pages/faq.php?act=trans">' . __('translit') . '</a> | ' .
        '<a href="../pages/faq.php?act=smilies">' . __('smilies') . '</a></div>' .
        '<p><a href="' . $url . '?id=' . $id . '">' . __('back') . '</a></p>';
}
