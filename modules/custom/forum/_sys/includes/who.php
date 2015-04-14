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

$url = App::router()->getUri(1);
$id = abs(intval(App::request()->getQuery('id', 0)));

if (!App::user()->id) {
    header('Location: ' . $url);
    exit;
}

//TODO: Переделать на новую систему ONLINE

if ($id) {
    /*
    -----------------------------------------------------------------
    Показываем общий список тех, кто в выбранной теме
    -----------------------------------------------------------------
    */
    $req = mysql_query("SELECT `text` FROM `" . TP . "forum__` WHERE `id` = " . $id . " AND `type` = 't'");
    if (mysql_num_rows($req)) {
        $res = mysql_fetch_assoc($req);
        echo '<div class="phdr"><b>' . __('who_in_topic') . ':</b> <a href="' . $url . '?id=' . $id . '">' . $res['text'] . '</a></div>';
        if (App::user()->rights > 0)
            echo '<div class="topmenu">' . ($do == 'guest' ? '<a href="' . $url . '?act=who&amp;id=' . $id . '">' . __('authorized') . '</a> | ' . __('guests')
                    : __('authorized') . ' | <a href="' . $url . '?act=who&amp;do=guest&amp;id=' . $id . '">' . __('guests') . '</a>') . '</div>';
        $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `" . ($do == 'guest' ? 'cms_sessions' : 'users') . "` WHERE `last_visit` > " . (time() - 300) . " AND `place` = 'forum," . $id . "'"), 0);
        if ($total) {
            $req = mysql_query("SELECT * FROM `" . ($do == 'guest' ? 'cms_sessions' : 'users') . "` WHERE `last_visit` > " . (time() - 300) . " AND `place` = 'forum," . $id . "' ORDER BY " . ($do == 'guest' ? "`movings` DESC"
                    : "`name` ASC") . " " . App::db()->pagination());
            while (($res = mysql_fetch_assoc($req)) !== false) {
                echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                App::user()->settings['avatars'] = 0;
                echo Functions::displayUser($res, 0, (App::request()->getQuery('act') == 'guest' || (App::user()->rights >= 1 && App::user()->rights >= $res['rights']) ? 1 : 0));
                echo '</div>';
                ++$i;
            }
        } else {
            echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
        }
    } else {
        header('Location: ' . $url);
    }
    echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>' .
        '<p><a href="' . $url . '?id=' . $id . '">' . __('to_topic') . '</a></p>';
} else {
    /*
    -----------------------------------------------------------------
    Показываем общий список тех, кто в форуме
    -----------------------------------------------------------------
    */
    echo '<div class="phdr"><a href="' . $url . '"><b>' . __('forum') . '</b></a> | ' . __('who_in_forum') . '</div>';
    if (App::user()->rights > 0)
        echo '<div class="topmenu">' . ($do == 'guest' ? '<a href="' . $url . '?act=who">' . __('users') . '</a> | <b>' . __('guests') . '</b>'
                : '<b>' . __('users') . '</b> | <a href="' . $url . '?act=who&amp;do=guest">' . __('guests') . '</a>') . '</div>';
    $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `" . ($do == 'guest' ? "cms_sessions" : "users") . "` WHERE `last_visit` > " . (time() - 300) . " AND `place` LIKE 'forum%'"), 0);
    if ($total > App::user()->settings['page_size']) echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=who&amp;' . ($do == 'guest' ? 'do=guest&amp;' : ''), App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
    if ($total) {
        $req = mysql_query("SELECT * FROM `" . ($do == 'guest' ? "cms_sessions" : "users") . "` WHERE `last_visit` > " . (time() - 300) . " AND `place` LIKE 'forum%' ORDER BY " . ($do == 'guest' ? "`movings` DESC"
                : "`name` ASC") . " " . App::db()->pagination());
        $i = 0;
        while (($res = mysql_fetch_assoc($req)) !== false) {
            if ($res['id'] == App::user()->id) echo '<div class="gmenu">';
            else echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            // Вычисляем местоположение
            $place = '';
            switch ($res['place']) {
                case 'forum':
                    $place = '<a href="' . $url . '">' . __('place_main') . '</a>';
                    break;

                case 'forumwho':
                    $place = __('place_list');
                    break;

                case 'forumfiles':
                    $place = '<a href="' . $url . '?act=files">' . __('place_files') . '</a>';
                    break;

                case 'forumnew':
                    $place = '<a href="' . $url . '?act=new">' . __('place_new') . '</a>';
                    break;

                case 'forumsearch':
                    $place = '<a href="' . $url . '/search">' . __('place_search') . '</a>';
                    break;

                default:
                    $where = explode(",", $res['place']);
                    if ($where[0] == 'forum' && intval($where[1])) {
                        $req_t = mysql_query("SELECT `type`, `refid`, `text` FROM `" . TP . "forum__` WHERE `id` = '$where[1]'");
                        if (mysql_num_rows($req_t)) {
                            $res_t = mysql_fetch_assoc($req_t);
                            $link = '<a href="' . $url . '?id=' . $where[1] . '">' . $res_t['text'] . '</a>';
                            switch ($res_t['type']) {
                                case 'f':
                                    $place = __('place_category') . ' &quot;' . $link . '&quot;';
                                    break;

                                case 'r':
                                    $place = __('place_section') . ' &quot;' . $link . '&quot;';
                                    break;

                                case 't':
                                    $place = (isset($where[2]) ? __('place_write') . ' &quot;' : __('place_topic') . ' &quot;') . $link . '&quot;';
                                    break;

                                case 'm':
                                    $req_m = mysql_query("SELECT `text` FROM `" . TP . "forum__` WHERE `id` = '" . $res_t['refid'] . "' AND `type` = 't'");
                                    if (mysql_num_rows($req_m)) {
                                        $res_m = mysql_fetch_assoc($req_m);
                                        $place = (isset($where[2]) ? __('place_answer') : __('place_topic')) . ' &quot;<a href="' . $url . '?id=' . $res_t['refid'] . '">' . $res_m['text'] . '</a>&quot;';
                                    }
                                    break;
                            }
                        }
                    }
            }
            $arg = [
                'stshide' => 1,
                'header'  => ('<br />' . Functions::getIcon('info.png', '', '', 'align="middle"') . '&#160;' . $place)
            ];
            echo Functions::displayUser($res, $arg);
            echo '</div>';
            ++$i;
        }
    } else {
        echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
    }
    echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
    if ($total > App::user()->settings['page_size']) {
        echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=who&amp;' . ($do == 'guest' ? 'do=guest&amp;' : ''), App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
            '<p><form action="' . $url . '?act=who' . ($do == 'guest' ? '&amp;do=guest' : '') . '" method="post">' .
            '<input type="text" name="page" size="2"/>' .
            '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
            '</form></p>';
    }
    echo '<p><a href="' . $url . '">' . __('to_forum') . '</a></p>';
}
