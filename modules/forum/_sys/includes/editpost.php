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

if (!App::user()->id || !$id) {
    echo __('error_wrong_data');
    exit;
}
$url = App::router()->getUri(1);

$nick = App::db()->quote(App::user()->data['nickname']);
$req = App::db()->query("SELECT * FROM `forum__` WHERE `id` = " . $id . " AND `type` = 'm' " . (App::user()->rights >= 7 ? "" : " AND `close` != '1'"));
if ($req->rowCount()) {
    /*
    -----------------------------------------------------------------
    Предварительные проверки
    -----------------------------------------------------------------
    */
    $res = $req->fetch();
    if (App::user()->rights < 6 && App::user()->rights != 3 && App::user()->id) {
        $topic = App::db()->query("SELECT `curators` FROM `forum__` WHERE `id` = " . $res['refid'])->fetch();
        $curators = !empty($topic['curators']) ? unserialize($topic['curators']) : [];
        if (array_key_exists(App::user()->id, $curators)) {
            App::user()->rights = 3;
        }
    }
    $page = ceil(App::db()->query("SELECT COUNT(*) FROM `forum__` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">= " : "<= ") . $id . (App::user()->rights < 7 ? " AND `close` != '1'" : ''))->fetchColumn() / App::user()->settings['page_size']);
    $posts = App::db()->query("SELECT COUNT(*) FROM `forum__` WHERE `refid` = '" . $res['refid'] . "' AND `close` != '1'")->fetchColumn();
    $link = $url . '?id=' . $res['refid'] . '&amp;page=' . $page;
    $error = false;
    if (App::user()->rights == 3 || App::user()->rights >= 6) {
        // Проверка для Администрации
        if ($res['user_id'] != App::user()->id) {
            $req_u = App::db()->query("SELECT * FROM `user__` WHERE `id` = '" . $res['user_id'] . "'");
            if ($req_u->rowCount()) {
                $res_u = $req_u->fetch();
                if ($res_u['rights'] > App::user()->rights)
                    $error = __('error_edit_rights') . '<br /><a href="' . $link . '">' . __('back') . '</a>';
            }
        }
    } else {
        // Проверка для обычных юзеров
        if ($res['user_id'] != App::user()->id)
            $error = __('error_edit_another') . '<br /><a href="' . $link . '">' . __('back') . '</a>';
        if (!$error) {
            $req_m = App::db()->query("SELECT * FROM `forum__` WHERE `refid` = '" . $res['refid'] . "' ORDER BY `id` DESC LIMIT 1");
            $res_m = $req_m->fetch();
            if ($res_m['user_id'] != App::user()->id)
                $error = __('error_edit_last') . '<br /><a href="' . $link . '">' . __('back') . '</a>';
            elseif ($res['time'] < time() - 300)
                $error = __('error_edit_timeout') . '<br /><a href="' . $link . '">' . __('back') . '</a>';
        }
    }
} else {
    $error = __('error_post_deleted') . '<br /><a href="' . $url . '">' . __('forum') . '</a>';
}
if (!$error) {
    switch (App::request()->getQuery('mod', '')) {
        case 'restore':
            /*
            -----------------------------------------------------------------
            Восстановление удаленного поста
            -----------------------------------------------------------------
            */
            $req_u = App::db()->query("SELECT `count_forum__` FROM `user__` WHERE `id` = '" . $res['user_id'] . "'");
            if ($req_u->rowCount()) {
                // Добавляем один балл к счетчику постов юзера
                $res_u = $req_u->fetch();
                App::db()->exec("UPDATE `user__` SET `count_forum__` = '" . ($res_u['count_forum'] + 1) . "' WHERE `id` = '" . $res['user_id'] . "'");
            }
            App::db()->exec("UPDATE `forum__` SET `close` = '0', `close_who` = " . $nick . " WHERE `id` = " . $id);
            $req_f = App::db()->query("SELECT * FROM `forum__files` WHERE `post` = " . $id . " LIMIT 1");
            if ($req_f->rowCount()) {
                App::db()->exec("UPDATE `forum__files` SET `del` = '0' WHERE `post` = " . $id . " LIMIT 1");
            }
            header('Location: ' . $link);
            break;

        case 'delete':
            /*
            -----------------------------------------------------------------
            Удаление поста и прикрепленного файла
            -----------------------------------------------------------------
            */
            if ($res['close'] != 1) {
                $req_u = App::db()->query("SELECT `count_forum__` FROM `user__` WHERE `id` = '" . $res['user_id'] . "'");
                if ($req_u->rowCount()) {
                    // Вычитаем один балл из счетчика постов юзера
                    $res_u = $req_u->fetch();
                    $postforum = $res_u['count_forum'] > 0 ? $res_u['count_forum'] - 1 : 0;
                    App::db()->exec("UPDATE `user__` SET `count_forum__` = '" . $postforum . "' WHERE `id` = '" . $res['user_id'] . "'");
                }
            }
            if (App::user()->rights == 9 && !isset($_GET['hide'])) {
                // Удаление поста (для Супервизоров)
                $req_f = App::db()->query("SELECT * FROM `forum__files` WHERE `post` = " . $id . " LIMIT 1");
                if ($req_f->rowCount()) {
                    // Если есть прикрепленный файл, удаляем его
                    $res_f = $req_f->fetch();
                    unlink(ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'forum' . DIRECTORY_SEPARATOR . $res_f['filename']);
                    App::db()->exec("DELETE FROM `forum__files` WHERE `post` = " . $id . " LIMIT 1");
                }
                // Формируем ссылку на нужную страницу темы
                $page = ceil(App::db()->query("SELECT COUNT(*) FROM `forum__` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? "> " : "< ") . $id)->fetchColumn() / App::user()->settings['page_size']);
                App::db()->exec("DELETE FROM `forum__` WHERE `id` = " . $id);
                if ($posts < 2) {
                    // Пересылка на удаление всей темы
                    header('Location: ' . $url . '?act=deltema&id=' . $res['refid']);
                } else {
                    header('Location: ' . $url . '?id=' . $res['refid'] . '&page=' . $page);
                }
            } else {
                // Скрытие поста
                $req_f = App::db()->query("SELECT * FROM `forum__files` WHERE `post` = " . $id . " LIMIT 1");
                if ($req_f->rowCount()) {
                    // Если есть прикрепленный файл, скрываем его
                    App::db()->exec("UPDATE `forum__files` SET `del` = '1' WHERE `post` = " . $id . " LIMIT 1");
                }
                if ($posts == 1) {
                    // Если это был последний пост темы, то скрываем саму тему
                    $res_l = App::db()->query("SELECT `refid` FROM `forum__` WHERE `id` = '" . $res['refid'] . "'")->fetch();
                    App::db()->exec("UPDATE `forum__` SET `close` = '1', `close_who` = " . $nick . " WHERE `id` = '" . $res['refid'] . "' AND `type` = 't'");
                    header('Location: ' . $url . '?id=' . $res_l['refid']);
                } else {
                    App::db()->exec("UPDATE `forum__` SET `close` = '1', `close_who` = " . $nick . " WHERE `id` = " . $id);
                    header('Location: ' . $url . '?id=' . $res['refid'] . '&page=' . $page);
                }
            }
            break;

        case 'del':
            /*
            -----------------------------------------------------------------
            Удаление поста, предварительное напоминание
            -----------------------------------------------------------------
            */
            echo '<div class="phdr"><a href="' . $link . '"><b>' . __('forum') . '</b></a> | ' . __('delete_post') . '</div>' .
                '<div class="rmenu"><p>';
            if ($posts == 1)
                echo __('delete_last_post_warning') . '<br />';
            echo __('delete_confirmation') . '</p>' .
                '<p><a href="' . $link . '">' . __('cancel') . '</a> | <a href="' . $url . '?act=editpost&amp;mod=delete&amp;id=' . $id . '">' . __('delete') . '</a>';
            if (App::user()->rights == 9)
                echo ' | <a href="' . $url . '?act=editpost&amp;mod=delete&amp;hide&amp;id=' . $id . '">' . __('hide') . '</a>';
            echo '</p></div>';
            echo '<div class="phdr"><small>' . __('delete_post_help') . '</small></div>';
            break;

        default:
            /*
            -----------------------------------------------------------------
            Редактирование поста
            -----------------------------------------------------------------
            */
            $msg = isset($_POST['msg']) ? trim($_POST['msg']) : '';
            if (isset($_POST['submit'])) {
                if (empty($_POST['msg'])) {
                    echo __('error_empty_message') . ' <a href="' . $url . '?act=editpost&amp;id=' . $id . '">' . __('repeat') . '</a>';
                    exit;
                }

                $stmt = App::db()->prepare("
                    UPDATE `forum__`
                    (`tedit`, `edit`, `kedit`, `text`)
                    VALUES (?, ?, ?, ?)
                    WHERE `id` = " . $id
                );

                $stmt->execute([
                    time(),
                    App::user()->data['nickname'],
                    ++$res['kedit'],
                    $msg
                ]);
                $stmt = null;

                header('Location: ' . $url . '?id=' . $res['refid'] . '&page=' . $page);
            } else {
                $msg_pre = htmlspecialchars($msg);
                if (App::user()->settings['smilies'])
                    $msg_pre = Functions::smilies($msg_pre, App::user()->rights ? 1 : 0);
                $msg_pre = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $msg_pre);
                echo '<div class="phdr"><a href="' . $link . '"><b>' . __('forum') . '</b></a> | ' . __('edit_message') . '</div>';
                if ($msg && !isset($_POST['submit'])) {
                    $user = App::db()->query("SELECT * FROM `user__` WHERE `id` = '" . $res['user_id'] . "' LIMIT 1")->fetch();
                    echo '<div class="list1">' . Functions::displayUser($user, ['iphide' => 1, 'header' => '<span class="gray">(' . Functions::displayDate($res['time']) . ')</span>', 'body' => $msg_pre]) . '</div>';
                }
                echo '<div class="rmenu"><form name="form" action="?act=editpost&amp;id=' . $id . '&amp;start=' . App::vars()->start . '" method="post"><p>';
                echo '<textarea rows="' . App::user()->settings['field_h'] . '" name="msg">' . (empty($_POST['msg']) ? htmlentities($res['text'], ENT_QUOTES, 'UTF-8') : htmlspecialchars($_POST['msg'])) . '</textarea><br/>';
                echo '</p><p><input type="submit" name="submit" value="' . __('save') . '" style="width: 107px; cursor: pointer;"/> ' .
                    ($set_forum['preview'] ? '<input type="submit" value="' . __('preview') . '" style="width: 107px; cursor: pointer;"/>' : '') .
                    '</p></form></div>' .
                    '<div class="phdr"><a href="../pages/faq.php?act=trans">' . __('translit') . '</a> | <a href="../pages/faq.php?act=smilies">' . __('smilies') . '</a></div>' .
                    '<p><a href="' . $link . '">' . __('back') . '</a></p>';
                //TODO: Исправить ссылку на каталог смайлов
            }
    }
} else {
    /*
    -----------------------------------------------------------------
    Выводим сообщения об ошибках
    -----------------------------------------------------------------
    */
    echo $error;
}
