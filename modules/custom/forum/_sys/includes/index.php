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

$uri = App::router()->getUri(0);
App::view()->uri = $uri;
$id = abs(intval(App::request()->getQuery('id', 0)));
$acl = App::cfg()->sys->acl_forum;

if (isset($_SESSION['ref'])) {
    unset($_SESSION['ref']);
}

$settings = Forum::settings();

// Список расширений файлов, разрешенных к выгрузке

// Файлы архивов
$ext_arch = [
    'zip',
    'rar',
    '7z',
    'tar',
    'gz'
];
// Звуковые файлы
$ext_audio = [
    'mp3',
    'amr'
];
// Файлы документов и тексты
$ext_doc = [
    'txt',
    'pdf',
    'doc',
    'rtf',
    'djvu',
    'xls'
];
// Файлы Java
$ext_java = [
    'jar',
    'jad'
];
// Файлы картинок
$ext_pic = [
    'jpg',
    'jpeg',
    'gif',
    'png',
    'bmp'
];
// Файлы SIS
$ext_sis = [
    'sis',
    'sisx'
];
// Файлы видео
$ext_video = [
    '3gp',
    'avi',
    'flv',
    'mpeg',
    'mp4'
];
// Файлы Windows
$ext_win = [
    'exe',
    'msi'
];
// Другие типы файлов (что не перечислены выше)
$ext_other = ['wmf'];

/*
-----------------------------------------------------------------
Ограничиваем доступ к Форуму
-----------------------------------------------------------------
*/
$error = '';
if ((!isset($acl) || !$acl) && App::user()->rights < 7) {
    $error = __('forum_closed');
} elseif (isset($acl) && $acl == 1 && !App::user()->id) {
    $error = __('access_guest_forbidden');
}
if ($error) {
    echo '<div class="rmenu"><p>' . $error . '</p></div>';
    exit;
}

/*
-----------------------------------------------------------------
Заголовки страниц форума
-----------------------------------------------------------------
*/
if (!$id) {
    $textl = '' . __('forum') . '';
} else {
    $req = App::db()->query("SELECT `text` FROM `" . TP . "forum__` WHERE `id`= " . $id);
    $res = $req->fetch();
    $hdr = strtr($res['text'], [
        '&quot;' => '',
        '&amp;'  => '',
        '&lt;'   => '',
        '&gt;'   => '',
        '&#039;' => ''
    ]);
    $hdr = mb_substr($hdr, 0, 30);
    $hdr = htmlspecialchars($hdr);
    $textl = mb_strlen($res['text']) > 30 ? $hdr . '...' : $hdr;
}

// Выводим уведомление об ограничениях (если установлены)
if (!isset($acl) || !$acl) {
    echo '<div class="alarm">' . __('forum_closed') . '</div>';
} elseif (isset($acl) && $acl == 3) {
    echo '<div class="rmenu">' . __('read_only') . '</div>';
}

if ($id) {
    // Определяем тип запроса (каталог, или тема)
    $type = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id`= " . $id);

    // Если темы не существует, показываем ошибку
    if (!$type->rowCount()) {
        echo __('error_topic_deleted') . ' <a href="' . $uri . '">' . __('to_forum') . '</a>';
        exit;
    }

    $type1 = $type->fetch();

    // Фиксация факта прочтения Топика
    if (App::user()->id && $type1['type'] == 't') {
        $req_r = App::db()->query("SELECT * FROM `" . TP . "forum__rdm` WHERE `topic_id` = " . $id . " AND `user_id` = " . App::user()->id . " LIMIT 1");

        if ($req_r->rowCount()) {
            $res_r = $req_r->fetch();
            if ($type1['time'] > $res_r['time']) {
                App::db()->exec("UPDATE `" . TP . "forum__rdm` SET `time` = '" . time() . "' WHERE `topic_id` = " . $id . " AND `user_id` = " . App::user()->id . " LIMIT 1");
            }
        } else {
            App::db()->exec("INSERT INTO `" . TP . "forum__rdm` SET `topic_id` = " . $id . ", `user_id` = " . App::user()->id . ", `time` = '" . time() . "'");
        }
    }

    // Получаем структуру форума (Breadcrumb)
    $res = true;
    $parent = $type1['refid'];

    while ($parent != '0' && $res != false) {
        $res = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = '$parent' LIMIT 1")->fetch();
        if ($res['type'] == 'f' || $res['type'] == 'r') {
            $tree[] = '<a href="' . $uri . '?id=' . $parent . '">' . htmlspecialchars($res['text']) . '</a>';
        }
        $parent = $res['refid'];
    }

    $tree[] = '<a href="' . $uri . '"><i class="home lg"></i></a>';
    krsort($tree);

    if ($type1['type'] != 't' && $type1['type'] != 'm') {
        $tree[] = '<strong>' . htmlspecialchars($type1['text']) . '</strong>';
    }

    App::view()->setRawVar('breadcrumb', implode('&#160; <i class="angle-right"></i> &#160;', $tree));

    // Счетчик файлов и ссылка на них
    $sql = (App::user()->rights == 9) ? "" : " AND `del` != '1'";

    if ($type1['type'] == 'f') {
        $count = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__files` WHERE `cat` = " . $id . $sql)->fetchColumn();
        if ($count > 0) {
            $filelink = '<a href="' . $uri . '?act=files&amp;c=' . $id . '">' . __('files_category') . '</a>';
        }
    } elseif ($type1['type'] == 'r') {
        $count = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__files` WHERE `subcat` = " . $id . $sql)->fetchColumn();
        if ($count > 0) {
            $filelink = '<a href="' . $uri . '?act=files&amp;s=' . $id . '">' . __('files_section') . '</a>';
        }
    } elseif ($type1['type'] == 't') {
        $count = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__files` WHERE `topic` = " . $id . $sql)->fetchColumn();
        if ($count > 0) {
            $filelink = '<a href="' . $uri . '?act=files&amp;t=' . $id . '">' . __('files_topic') . '</a>';
        }
    } else {
        $count = 0;
    }

    $filelink = isset($filelink) ? $filelink . '&#160;<span class="red">(' . $count . ')</span>' : false;

    // Счетчик "Кто в теме?"
    //TODO: Доработать!
    $wholink = false;

        /*
        -----------------------------------------------------------------
        Отрбражаем содержимое форума
        -----------------------------------------------------------------
        */
        switch ($type1['type']) {
            case 'f':
                /**
                 * Список разделов форума
                 */
                $req = App::db()->query("SELECT `id`, `text`, `soft` FROM `" . TP . "forum__` WHERE `type`='r' AND `refid` = " . $id . " ORDER BY `realid`");
                App::view()->total = $req->rowCount();

                if (App::view()->total > 0) {
                    for ($i = 0; $res = $req->fetch(); ++$i) {
                        $res['count'] = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `type` = 't' AND `refid` = '" . $res['id'] . "'")->fetchColumn();
                        App::view()->list[$i] = $res;
                    }
                }

                App::view()->backlink = $uri;
                App::view()->setTemplate('index.php');
                break;

            case 'r':
                /**
                 * Список топиков
                 */
                App::view()->total = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `type`='t' AND `refid` = " . $id . (App::user()->rights >= 7 ? '' : " AND `close`!='1'"))->fetchColumn();

                if ((App::user()->id && !isset(App::user()->ban['1']) && !isset(App::user()->ban['11']) && $acl != 3) || App::user()->rights) {
                    // Кнопка создания новой темы
                    echo '<div class="gmenu"><form action="' . $uri . 'new_topic/?id=' . $id . '" method="post"><input type="submit" value="' . __('new_topic') . '" /></form></div>';
                }

                if (App::view()->total > 0) {
                    $req = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `type`='t'" . (App::user()->rights >= 7 ? '' : " AND `close`!='1'") . " AND `refid` = " . $id . " ORDER BY `vip` DESC, `time` DESC " . App::db()->pagination());

                    for ($i = 0; $res = $req->fetch(); ++$i) {
                        $nam = App::db()->query("SELECT `from` FROM `" . TP . "forum__` WHERE `type` = 'm' AND `close` != '1' AND `refid` = '" . $res['id'] . "' ORDER BY `time` DESC LIMIT 1")->fetch();
                        $res['countmsg'] = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `type`='m' AND `refid`='" . $res['id'] . "'" . (App::user()->rights >= 7 ? '' : " AND `close` != '1'"))->fetchColumn();
                        $cpg = ceil($res['countmsg'] / App::user()->settings['page_size']);

                        if (App::user()->id) {
                            $res['unread'] = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__rdm` WHERE `time` >= '" . $res['time'] . "' AND `topic_id` = '" . $res['id'] . "' AND `user_id` = " . App::user()->id)->fetchColumn();
                        } else {
                            $res['unread'] = 1;
                        }

                        App::view()->buddies = $res['from'] . (!empty($nam['from']) ? ' / ' . $nam['from'] : '');
                        App::view()->list[$i] = $res;
                    }
                }

                if (App::view()->total > App::user()->settings['page_size']) {
                    echo '<div class="topmenu">' . Functions::displayPagination($uri . '?id=' . $id . '&amp;', App::vars()->start, App::view()->total, App::user()->settings['page_size']) . '</div>' .
                        '<p><form action="' . $uri . '?id=' . $id . '" method="post">' .
                        '<input type="text" name="page" size="2"/>' .
                        '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
                        '</form></p>';
                }

                App::view()->backlink = $uri . '?id=' . $type1['refid'];
                App::view()->setTemplate('forum_topiclist.php');
                break;

            case 't':
                /**
                 * Читаем топик
                 */
                $filter = isset($_SESSION['fsort_id']) && $_SESSION['fsort_id'] == $id ? 1 : 0;
                $sql = '';

                if ($filter && !empty($_SESSION['fsort_users'])) {
                    // Подготавливаем запрос на фильтрацию юзеров
                    $sw = 0;
                    $sql = ' AND (';
                    $fsort_users = unserialize($_SESSION['fsort_users']);
                    foreach ($fsort_users as $val) {
                        if ($sw) {
                            $sql .= ' OR ';
                        }
                        $sortid = intval($val);
                        $sql .= "`" . TP . "forum__`.`user_id` = '$sortid'";
                        $sw = 1;
                    }
                    $sql .= ')';
                }

                if (App::user()->id && !$filter) {
                    // Фиксация факта прочтения топика
                }

                if (App::user()->rights < 7 && $type1['close'] == 1) {
                    echo '<div class="rmenu"><p>' . __('topic_deleted') . '<br/><a href="?id=' . $type1['refid'] . '">' . __('to_section') . '</a></p></div>';
                    exit;
                }

                // Счетчик постов темы
                $colmes = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `type`='m'$sql AND `refid` = " . $id . (App::user()->rights >= 7 ? '' : " AND `close` != '1'"))->fetchColumn();

                // Выводим название топика
                echo '<div class="phdr"><a name="up" id="up"></a><a href="#down">' . App::image('down.png') . '</a>&#160;&#160;<b>' . $type1['text'] . '</b></div>';

                // Метки удаления темы
                if ($type1['close']) {
                    echo '<div class="rmenu">' . __('topic_delete_who') . ': <b>' . $type1['close_who'] . '</b></div>';
                } elseif (!empty($type1['close_who']) && App::user()->rights >= 7) {
                    echo '<div class="gmenu"><small>' . __('topic_delete_whocancel') . ': <b>' . $type1['close_who'] . '</b></small></div>';
                }

                // Метки закрытия темы
                if ($type1['edit']) {
                    echo '<div class="rmenu">' . __('topic_closed') . '</div>';
                }

                ////////////////////////////////////////////////////////////
                // Блок голосований                                       //
                ////////////////////////////////////////////////////////////
//                if ($type1['realid']) {
//                    $clip_forum = isset($_GET['clip']) ? '&amp;clip' : '';
//                    $vote_user = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__vote_users` WHERE `user` = " . App::user()->id . " AND `topic` = " . App::vars()->id)->fetchColumn();
//                    $topic_vote = App::db()->query("SELECT `name`, `time`, `count` FROM `" . TP . "forum__vote` WHERE `type`='1' AND `topic` = " . App::vars()->id . " LIMIT 1")->fetch();
//                    echo '<div  class="gmenu"><b>' . htmlspecialchars($topic_vote['name']) . '</b><br />';
//                    $vote_result = App::db()->query("SELECT `id`, `name`, `count` FROM `" . TP . "forum__vote` WHERE `type`='2' AND `topic` = " . App::vars()->id . " ORDER BY `id` ASC");
//                    if (!$type1['edit'] && !isset($_GET['vote_result']) && App::user()->id && $vote_user == 0) {
//                        // Выводим форму с опросами
//                        echo '<form action="' . $uri . '?act=vote&amp;id=' . App::vars()->id . '" method="post">';
//                        while ($vote = $vote_result->fetch()) {
//                            echo '<input type="radio" value="' . $vote['id'] . '" name="vote"/> ' . htmlspecialchars($vote['name']) . '<br />';
//                        }
//                        echo '<p><input type="submit" name="submit" value="' . __('vote') . '"/><br /><a href="' . $uri . '?id=' . App::vars()->id . '&amp;start=' . App::vars()->start . '&amp;vote_result' . $clip_forum .
//                            '">' . __('results') . '</a></p></form></div>';
//                    } else {
//                        // Выводим результаты голосования
//                        echo '<small>';
//                        while ($vote = $vote_result->fetch()) {
//                            $count_vote = $topic_vote['count'] ? round(100 / $topic_vote['count'] * $vote['count']) : 0;
//                            echo htmlspecialchars($vote['name']) . ' [' . $vote['count'] . ']<br />';
//                            echo '<img src="' . App::cfg()->homeurl . 'assets/misc/vote_img.php?img=' . $count_vote . '" alt="' . __('rating') . ': ' . $count_vote . '%" /><br />';
//                        }
//                        echo '</small></div><div class="bmenu">' . __('total_votes') . ': ';
//                        if (App::user()->rights > 6) {
//                            echo '<a href="' . $uri . '?act=users&amp;id=' . App::vars()->id . '">' . $topic_vote['count'] . '</a>';
//                        } else {
//                            echo $topic_vote['count'];
//                        }
//                        echo '</div>';
//                        if (App::user()->id && $vote_user == 0) {
//                            echo '<div class="bmenu"><a href="' . $uri . '?id=' . App::vars()->id . '&amp;start=' . App::vars()->start . $clip_forum . '">' . __('vote') . '</a></div>';
//                        }
//                    }
//                }

                // Управление кураторами
                $curators = !empty($type1['curators']) ? unserialize($type1['curators']) : [];
                $curator = false;
                if (App::user()->rights < 6 && App::user()->rights != 3 && App::user()->id) {
                    if (array_key_exists(App::user()->id, $curators)) {
                        $curator = true;
                    }
                }

                ////////////////////////////////////////////////////////////
                // Фиксация первого поста в теме                          //
                ////////////////////////////////////////////////////////////
//                if (($settings['postclip'] == 2 && ($settings['upfp'] ? App::vars()->start < (ceil($colmes - App::user()->settings['page_size'])) : App::vars()->start > 0)) || isset($_GET['clip'])) {
//                    $postres = App::db()->query("SELECT `" . TP . "forum__`.*, `" . TP . "user__`.`sex`, `" . TP . "user__`.`rights`, `" . TP . "user__`.`last_visit`, `" . TP . "user__`.`status`, `" . TP . "user__`.`datereg`
//                    FROM `" . TP . "forum__` LEFT JOIN `" . TP . "user__` ON `" . TP . "forum__`.`user_id` = `" . TP . "user__`.`id`
//                    WHERE `" . TP . "forum__`.`type` = 'm' AND `" . TP . "forum__`.`refid` = " . App::vars()->id . (App::user()->rights >= 7 ? "" : " AND `" . TP . "forum__`.`close` != '1'") . "
//                    ORDER BY `" . TP . "forum__`.`id` LIMIT 1")->fetch();
//                    echo '<div class="topmenu"><p>';
//                    if (App::user()->id && App::user()->id != $postres['user_id']) {
//                        echo '<a href="../users/profile.php?user=' . $postres['user_id'] . '&amp;fid=' . $postres['id'] . '"><b>' . $postres['from'] . '</b></a> ' .
//                            '<a href="' . $uri . '?act=say&amp;id=' . $postres['id'] . '&amp;start=' . App::vars()->start . '"> ' . __('reply_btn') . '</a> ' .
//                            '<a href="' . $uri . '?act=say&amp;id=' . $postres['id'] . '&amp;start=' . App::vars()->start . '&amp;cyt"> ' . __('cytate_btn') . '</a> ';
//                    } else {
//                        echo '<b>' . $postres['from'] . '</b> ';
//                    }
//                    $user_rights = [
//                        1 => 'Kil',
//                        3 => 'Mod',
//                        6 => 'Smd',
//                        7 => 'Adm',
//                        8 => 'SV'
//                    ];
//                    echo @$user_rights[$postres['rights']];
//                    echo(time() > $postres['last_visit'] + 300 ? '<span class="red"> [Off]</span>' : '<span class="green"> [ON]</span>');
//                    echo ' <span class="gray">(' . Functions::displayDate($postres['time']) . ')</span><br/>';
//                    if ($postres['close']) {
//                        echo '<span class="red">' . __('post_deleted') . '</span><br/>';
//                    }
//                    echo htmlspecialchars(mb_substr($postres['text'], 0, 500));
//                    if (mb_strlen($postres['text']) > 500) {
//                        echo '...<a href="' . $uri . '?act=post&amp;id=' . $postres['id'] . '">' . __('read_all') . '</a>';
//                    }
//                    echo '</p></div>';
//                }

                // Метка включения фильтра
//                if ($filter) {
//                    echo '<div class="rmenu">' . __('filter_on') . '</div>';
//                }

                // Задаем правила сортировки (новые внизу / вверху)
                if (App::user()->id) {
                    $order = $settings['upfp'] ? 'DESC' : 'ASC';
                } else {
                    $order = ((empty($_SESSION['uppost'])) || ($_SESSION['uppost'] == 0)) ? 'ASC' : 'DESC';
                }

                ////////////////////////////////////////////////////////////
                // Запрос в базу                                          //
                ////////////////////////////////////////////////////////////
                $req = App::db()->query("
                    SELECT `" . TP . "forum__`.*, `" . TP . "user__`.`sex`, `" . TP . "user__`.`rights`, `" . TP . "user__`.`last_visit`, `" . TP . "user__`.`status`, `" . TP . "user__`.`join_date`, `" . TP . "user__`.`avatar`
                    FROM `" . TP . "forum__`
                    LEFT JOIN `" . TP . "user__` ON `" . TP . "forum__`.`user_id` = `" . TP . "user__`.`id`
                    WHERE `" . TP . "forum__`.`type` = 'm'
                    AND `" . TP . "forum__`.`refid` = " . $id . (App::user()->rights >= 7 ? "" : "
                    AND `" . TP . "forum__`.`close` != '1'") . "$sql ORDER BY `" . TP . "forum__`.`id` $order " .
                    App::db()->pagination());

                // Верхнее поле "Написать"
//                if ((App::user()->id && !$type1['edit'] && $settings['upfp'] && App::cfg()->acl_forum != 3) || (App::user()->rights >= 7 && $settings['upfp'])) {
//                    echo '<div class="gmenu"><form name="form1" action="' . $uri . 'say/?id=' . App::vars()->id . '" method="post">';
//                    if ($settings['farea']) {
//                        echo '<p>' .
//                            '<textarea rows="' . App::user()->settings['field_h'] . '" name="msg"></textarea></p>' .
//                            '<p><input type="checkbox" name="addfiles" value="1" /> ' . __('add_file') .
//                            '</p><p><input type="submit" name="submit" value="' . __('write') . '" style="width: 107px; cursor: pointer;"/> ' .
//                            ($settings['preview'] ? '<input type="submit" value="' . __('preview') . '" style="width: 107px; cursor: pointer;"/>' : '') .
//                            '</p></form></div>';
//                    } else {
//                        echo '<p><input type="submit" name="submit" value="' . __('write') . '"/></p></form></div>';
//                    }
//                }

                if (App::user()->rights == 3 || App::user()->rights >= 6) {
                    echo '<form action="' . $uri . '?act=massdel" method="post">';
                }

                ////////////////////////////////////////////////////////////
                // Выводим списков постов темы                            //
                ////////////////////////////////////////////////////////////
                for ($i = 1; $res = $req->fetch(); ++$i) {
                    if ($res['close']) {
                        echo '<div class="rmenu">';
                    } else {
                        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                    }

                    if (App::user()->settings['avatars']) {
                        echo '<table cellpadding="0" cellspacing="0"><tr><td>';
                        if (file_exists((ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . 'avatar' . DIRECTORY_SEPARATOR . $res['user_id'] . '.gif'))) {
                            echo '<img src="' . App::cfg()->sys->homeurl . 'files/users/avatar/' . $res['user_id'] . '.gif" width="32" height="32" alt="' . $res['from'] . '" />&#160;';
                        } else {
                            echo App::image('empty.png', ['alt' => $res['from']]) . '&#160;';
                        }
                        echo '</td><td>';
                    }

                    if ($res['sex']) {
                        echo App::image('usr_' . ($res['sex'] == 'm' ? 'm' : 'w') . ($res['join_date'] > time() - 86400 ? '_new' : '') . '.png', '', 'align="middle"') . '&#160;';
                    } else {
                        echo App::image('delete.png', '', '', 'align="middle"') . '&#160;';
                    }

                    // Ник юзера и ссылка на его анкету
                    if (App::user()->id && App::user()->id != $res['user_id']) {
                        echo '<a href="../users/profile.php?user=' . $res['user_id'] . '"><b>' . $res['from'] . '</b></a> ';
                    } else {
                        echo '<b>' . $res['from'] . '</b> ';
                    }

                    // Метка должности
                    $user_rights = [
                        3 => '(FMod)',
                        6 => '(Smd)',
                        7 => '(Adm)',
                        9 => '(SV!)'
                    ];
                    echo @$user_rights[$res['rights']];

                    // Метка Онлайн / Офлайн
                    echo(time() > $res['last_visit'] + 300 ? '<span class="red"> [Off]</span> ' : '<span class="green"> [ON]</span> ');

                    // Ссылки на ответ и цитирование
                    if (App::user()->id && App::user()->id != $res['user_id']) {
                        echo '<a href="' . $uri . '?act=say&amp;id=' . $res['id'] . '&amp;start=' . App::vars()->start . '">' . __('reply_btn') . '</a>&#160;' .
                            '<a href="' . $uri . '?act=say&amp;id=' . $res['id'] . '&amp;start=' . App::vars()->start . '&amp;cyt">' . __('cytate_btn') . '</a> ';
                    }

                    // Время поста
                    echo ' <span class="gray">(' . Functions::displayDate($res['time']) . ')</span><br />';

                    // Статус юзера
                    if (!empty($res['status'])) {
                        echo '<div class="status">' . App::image('label.png') . '&#160;' . htmlspecialchars($res['status']) . '</div>';
                    }

                    if (App::user()->settings['avatars']) {
                        echo '</td></tr></table>';
                    }

                    ////////////////////////////////////////////////////////////
                    // Вывод текста поста                                     //
                    ////////////////////////////////////////////////////////////
                    $text = $res['text'];
                    if ($settings['postcut']) {
                        // Если текст длинный, обрезаем и даем ссылку на полный вариант
                        switch ($settings['postcut']) {
                            case 2:
                                $cut = 1000;
                                break;

                            case 3:
                                $cut = 3000;
                                break;
                            default :
                                $cut = 500;
                        }
                    }

                    if ($settings['postcut'] && mb_strlen($text) > $cut) {
                        $text = mb_substr($text, 0, $cut);
                        $text = htmlspecialchars($text);
                        $text = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $text);
                        if (App::user()->settings['smilies']) {
                            $text = Functions::smilies($text, $res['rights'] ? 1 : 0);
                        }
                        echo htmlspecialchars($text) . '...<br /><a href="' . $uri . '?act=post&amp;id=' . $res['id'] . '">' . __('read_all') . ' &gt;&gt;</a>';
                    } else {
                        // Или, обрабатываем тэги и выводим весь текст
                        $text = htmlspecialchars($text);
                        if (App::user()->settings['smilies']) {
                            $text = Functions::smilies($text, $res['rights'] ? 1 : 0);
                        }
                        echo $text;
                    }

                    // Если пост редактировался, показываем кем и когда
                    if ($res['kedit']) {
                        echo '<br /><span class="gray"><small>' . __('edited') . ' <b>' . $res['edit'] . '</b> (' . Functions::displayDate($res['tedit']) . ') <b>[' . $res['kedit'] . ']</b></small></span>';
                    }

                    // Если есть прикрепленный файл, выводим его описание
                    $freq = App::db()->query("SELECT * FROM `" . TP . "forum__files` WHERE `post` = '" . $res['id'] . "'");
                    if ($freq->rowCount() > 0) {
                        $fres = $freq->fetch();
                        $fls = round(@filesize(ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'forum' . DIRECTORY_SEPARATOR . $fres['filename']) / 1024, 2);
                        echo '<br /><span class="gray">' . __('attached_file') . ':';

                        // Предпросмотр изображений
                        $att_ext = strtolower(Functions::format(ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'forum' . DIRECTORY_SEPARATOR . $fres['filename']));
                        $pic_ext = [
                            'gif',
                            'jpg',
                            'jpeg',
                            'png'
                        ];
                        if (in_array($att_ext, $pic_ext)) {
                            echo '<div><a href="' . $uri . '?act=file&amp;id=' . $fres['id'] . '">';
                            echo '<img src="' . App::cfg()->sys->homeurl . 'assets/misc/forum_thumbinal.php?file=' . (urlencode($fres['filename'])) . '" alt="' . __('click_to_view') . '" /></a></div>';
                        } else {
                            echo '<br /><a href="' . $uri . '?act=file&amp;id=' . $fres['id'] . '">' . $fres['filename'] . '</a>';
                        }
                        echo ' (' . $fls . ' кб.)<br/>';
                        echo __('downloads') . ': ' . $fres['dlcount'] . ' ' . __('time') . '</span>';
                        $file_id = $fres['id'];
                    }

                    // Ссылки на редактирование / удаление постов
                    if (((App::user()->rights == 3 || App::user()->rights >= 6 || $curator) && App::user()->rights >= $res['rights']) || ($res['user_id'] == App::user()->id && !$settings['upfp'] && (App::vars()->start + $i) == $colmes && $res['time'] > time() - 300) || ($res['user_id'] == App::user()->id && $settings['upfp'] && App::vars()->start == 0 && $i == 1 && $res['time'] > time() - 300)) {
                        $menu = [
                            '<a href="' . $uri . '?act=editpost&amp;id=' . $res['id'] . '">' . __('edit') . '</a>',
                            (App::user()->rights >= 7 && $res['close'] == 1 ? '<a href="' . $uri . '?act=editpost&amp;mod=restore&amp;id=' . $res['id'] . '">' . __('restore') . '</a>' : ''),
                            ($res['close'] == 1 ? '' : '<a href="' . $uri . '?act=editpost&amp;mod=del&amp;id=' . $res['id'] . '">' . __('delete') . '</a>')
                        ];
                        echo '<div class="sub">';
                        if (App::user()->rights == 3 || App::user()->rights >= 6) {
                            echo '<input type="checkbox" name="delch[]" value="' . $res['id'] . '"/>&#160;';
                        }
                        echo implode(' | ', $menu);
                        if ($res['close']) {
                            echo '<div class="red">' . __('who_delete_post') . ': <b>' . $res['close_who'] . '</b></div>';
                        } elseif (!empty($res['close_who'])) {
                            echo '<div class="green">' . __('who_restore_post') . ': <b>' . $res['close_who'] . '</b></div>';
                        }
                        if (App::user()->rights == 3 || App::user()->rights >= 6) {
                            if ($res['ip_via_proxy']) {
                                //TODO: Переделать ссылку
                                echo '<div class="gray"><b class="red"><a href="' . App::cfg()->sys->homeurl . '/admin?act=search_ip&amp;ip=' . long2ip($res['ip']) . '">' . long2ip($res['ip']) . '</a></b> - ' .
                                    '<a href="' . App::cfg()->sys->homeurl . '/admin?act=search_ip&amp;ip=' . long2ip($res['ip_via_proxy']) . '">' . long2ip($res['ip_via_proxy']) . '</a>' .
                                    ' - ' . $res['soft'] . '</div>';
                            } else {
                                //TODO: Переделать ссылку
                                echo '<div class="gray"><a href="' . App::cfg()->sys->homeurl . '/admin?act=search_ip&amp;ip=' . long2ip($res['ip']) . '">' . long2ip($res['ip']) . '</a> - ' . $res['soft'] . '</div>';
                            }
                        }
                        echo '</div>';
                    }
                    echo '</div>';

                    App::view()->list[$i] = $res;
                }
                if (App::user()->rights == 3 || App::user()->rights >= 6) {
                    echo '<div class="rmenu"><input type="submit" value=" ' . __('delete') . ' "/></div>';
                    echo '</form>';
                }

                ////////////////////////////////////////////////////////////
                // Нижнее поле "Написать"                                 //
                ////////////////////////////////////////////////////////////
                if ((App::user()->id && !$type1['edit'] && !$settings['upfp'] && $acl != 3) || (App::user()->rights >= 7 && !$settings['upfp'])) {
                    echo '<div class="gmenu"><form name="form2" action="' . $uri . 'say/?id=' . $id . '" method="post">';
                    if ($settings['farea']) {
                        echo '<p>';
                        echo '<textarea rows="' . App::user()->settings['field_h'] . '" name="msg"></textarea><br/></p>' .
                            '<p><input type="checkbox" name="addfiles" value="1" /> ' . __('add_file');
                        echo '</p><p><input type="submit" name="submit" value="' . __('write') . '" style="width: 107px; cursor: pointer;"/> ' .
                            ($settings['preview'] ? '<input type="submit" value="' . __('preview') . '" style="width: 107px; cursor: pointer;"/>' : '') .
                            '</p></form></div>';
                    } else {
                        echo '<p><input type="submit" name="submit" value="' . __('write') . '"/></p></form></div>';
                    }
                }
                echo '<div class="phdr"><a name="down" id="down"></a><a href="#up">' . App::image('up.png') . '</a>' .
                    '&#160;&#160;' . __('total') . ': ' . $colmes . '</div>';
                if ($colmes > App::user()->settings['page_size']) {
                    echo '<div class="topmenu">' . Functions::displayPagination($uri . '?id=' . $id . '&amp;', App::vars()->start, $colmes, App::user()->settings['page_size']) . '</div>' .
                        '<p><form action="' . $uri . '?id=' . $id . '" method="post">' .
                        '<input type="text" name="page" size="2"/>' .
                        '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
                        '</form></p>';
                } else {
                    echo '<br />';
                }

                ////////////////////////////////////////////////////////////
                // Выводим список кураторов                               //
                ////////////////////////////////////////////////////////////
                if ($curators) {
                    $array = [];
                    foreach ($curators as $key => $value) {
                        $array[] = '<a href="' . App::cfg()->sys->homeurl . 'profile/' . $key . '">' . $value . '</a>';
                    }
                    echo '<p><div class="func">' . __('curators') . ': ' . implode(', ', $array) . '</div></p>';
                }

                ////////////////////////////////////////////////////////////
                // Ссылки на модераторские функции                        //
                ////////////////////////////////////////////////////////////
                if (App::user()->rights == 3 || App::user()->rights >= 6) {
                    echo '<p><div class="func">';
                    if (App::user()->rights >= 7) {
                        echo '<a href="' . $uri . 'curators/?id=' . $id . '&amp;start=' . App::vars()->start . '">' . __('curators') . '</a><br />';
                    }
                    echo isset($topic_vote) && $topic_vote > 0
                        ? '<a href="' . $uri . '?act=editvote&amp;id=' . $id . '">' . __('edit_vote') . '</a><br/><a href="' . $uri . '?act=delvote&amp;id=' . $id . '">' . __('delete_vote') . '</a><br/>'
                        : '<a href="' . $uri . '?act=addvote&amp;id=' . $id . '">' . __('add_vote') . '</a><br/>';
                    echo '<a href="' . $uri . '?act=ren&amp;id=' . $id . '">' . __('topic_rename') . '</a><br/>';

                    // Закрыть тему
                    if ($type1['edit'] == 1) {
                        echo '<a href="' . $uri . 'close/?id=' . $id . '">' . __('topic_open') . '</a><br/>';
                    } else {
                        echo '<a href="' . $uri . 'close/?id=' . $id . '&amp;closed">' . __('topic_close') . '</a><br/>';
                    }

                    // Удалить тему
                    if ($type1['close'] == 1) {
                        echo '<a href="' . $uri . '?act=restore&amp;id=' . $id . '">' . __('topic_restore') . '</a><br/>';
                    }
                    echo '<a href="' . $uri . '?act=deltema&amp;id=' . $id . '">' . __('topic_delete') . '</a><br/>';

                    // Закрепить тему
                    if ($type1['vip'] == 1) {
                        echo '<a href="' . $uri . 'pin/?id=' . $id . '">' . __('topic_unfix') . '</a>';
                    } else {
                        echo '<a href="' . $uri . 'pin/?id=' . $id . '&amp;vip">' . __('topic_fix') . '</a>';
                    }

                    echo '<br/><a href="' . $uri . '?act=per&amp;id=' . $id . '">' . __('topic_move') . '</a></div></p>';
                }

                if ($wholink) {
                    echo '<div>' . $wholink . '</div>';
                }

                if ($filter) {
                    echo '<div><a href="' . $uri . '?act=filter&amp;id=' . $id . '&amp;do=unset">' . __('filter_cancel') . '</a></div>';
                } else {
                    echo '<div><a href="' . $uri . '?act=filter&amp;id=' . $id . '&amp;start=' . App::vars()->start . '">' . __('filter_on_author') . '</a></div>';
                }

                echo '<a href="' . $uri . '?act=tema&amp;id=' . $id . '">' . __('download_topic') . '</a>';

                App::view()->backlink = $uri . '?id=' . $type1['refid'];
                App::view()->setTemplate('forum_topic.php');
                break;

            default:
                /**
                 * Если неверные данные, показываем ошибку
                 */
                echo __('error_wrong_data');
                break;
        }
    } else {
        /**
         * Список Категорий форума
         */
        $req = App::db()->query("SELECT `id`, `text`, `soft` FROM `" . TP . "forum__` WHERE `type` = 'f' ORDER BY `realid`");
        App::view()->total = $req->rowCount();

        if (App::view()->total > 0) {
            for ($i = 0; $res = $req->fetch(); ++$i) {
                $res['count'] = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `type` = 'r' AND `refid` = '" . $res['id'] . "'")->fetchColumn();
                App::view()->list[$i] = $res;
            }
        }

        App::view()->setTemplate('index.php');
    }

