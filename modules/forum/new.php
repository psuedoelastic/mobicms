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

unset($_SESSION['fsort_id']);
unset($_SESSION['fsort_users']);
if (empty($_SESSION['uid'])) {
    if (isset($_GET['newup'])) {
        $_SESSION['uppost'] = 1;
    }
    if (isset($_GET['newdown'])) {
        $_SESSION['uppost'] = 0;
    }
}

/*
-----------------------------------------------------------------
Настройки форума
-----------------------------------------------------------------
*/
if (!App::user()->id || ($set_forum = App::user()->getData('set_forum')) === false) {
    $set_forum = [
        'farea'    => 0,
        'upfp'     => 0,
        'preview'  => 1,
        'postclip' => 1,
        'postcut'  => 2
    ];
}

if (App::user()->id) {
    switch (App::request()->getQuery('act', '')) {
        case 'reset':
            /*
            -----------------------------------------------------------------
            Отмечаем все темы как прочитанные
            -----------------------------------------------------------------
            */
            $req = App::db()->query("SELECT `forum__`.`id`
                FROM `forum__` LEFT JOIN `forum__rdm` ON `forum__`.`id` = `forum__rdm`.`topic_id` AND `forum__rdm`.`user_id` = " . App::user()->id . "
                WHERE `forum__`.`type`='t'
                AND `forum__rdm`.`topic_id` Is Null"
            );
            $stmt = App::db()->prepare("
                INSERT INTO `forum__rdm`
                (`topic_id`, `user_id`, `time`)
                VALUES (?, " . App::user()->id . ", " . time() . ")
            ");
            while ($res = $req->fetch()) {
                $stmt->execute([$res['id']]);
            }
            $stmt = null;

            $req = App::db()->query("SELECT `forum__`.`id` AS `id`
                FROM `forum__` LEFT JOIN `forum__rdm` ON `forum__`.`id` = `forum__rdm`.`topic_id` AND `forum__rdm`.`user_id` = " . App::user()->id . "
                WHERE `forum__`.`type`='t'
                AND `forum__`.`time` > `forum__rdm`.`time`"
            );
            $stmt = App::db()->prepare("
                UPDATE `forum__rdm` SET
                `time` = " . time() . "
                WHERE `topic_id` = ?
                AND `user_id` = " . App::user()->id
            );
            while ($res = $req->fetch()) {
                $stmt->execute([$res['id']]);
            }
            $stmt = null;

            echo '<div class="menu"><p>' . __('unread_reset_done') . '<br /><a href="' . $url . '">' . __('to_forum') . '</a></p></div>';
            break;

        case 'select':
            /*
            -----------------------------------------------------------------
            Форма выбора диапазона времени
            -----------------------------------------------------------------
            */
            echo '<div class="phdr"><a href="' . $url . '"><b>' . __('forum') . '</b></a> | ' . __('unread_show_for_period') . '</div>' .
                '<div class="menu"><p><form action="' . $url . '?act=period" method="post">' . __('unread_period') . ':<br/>' .
                '<input type="text" maxlength="3" name="vr" value="24" size="3"/>' .
                '<input type="submit" name="submit" value="' . __('show') . '"/></form></p></div>' .
                '<div class="phdr"><a href="' . $url . '">' . __('back') . '</a></div>';
            break;

        case 'period':
            /*
            -----------------------------------------------------------------
            Показ новых тем за выбранный период
            -----------------------------------------------------------------
            */
            $vr = isset($_REQUEST['vr']) ? abs(intval($_REQUEST['vr'])) : null;
            if (!$vr) {
                echo __('error_time_empty') . '<br/><a href="' . $url . '?act=period">' . __('repeat') . '</a><br/>';
                exit;
            }
            $vr1 = time() - $vr * 3600;
            if (App::user()->rights == 9) {
                $req = App::db()->query("SELECT COUNT(*) FROM `forum__` WHERE `type`='t' AND `time` > '$vr1'");
            } else {
                $req = App::db()->query("SELECT COUNT(*) FROM `forum__` WHERE `type`='t' AND `time` > '$vr1' AND `close` != '1'");
            }
            $count = $req->fetchColumn();
            echo '<div class="phdr"><a href="' . $url . '"><b>' . __('forum') . '</b></a> | ' . __('unread_all_for_period') . ' ' . $vr . ' ' . __('hours') . '</div>';
            if ($count > App::user()->settings['page_size'])
                echo '<div class="topmenu">' . Functions::displayPagination('index.php?act=new&amp;mod=period&amp;vr=' . $vr . '&amp;', App::vars()->start, $count, App::user()->settings['page_size']) . '</div>';
            if ($count > 0) {
                if (App::user()->rights == 9) {
                    $req = App::db()->query("SELECT * FROM `forum__` WHERE `type`='t' AND `time` > '" . $vr1 . "' ORDER BY `time` DESC " . App::db()->pagination());
                } else {
                    $req = App::db()->query("SELECT * FROM `forum__` WHERE `type`='t' AND `time` > '" . $vr1 . "' AND `close` != '1' ORDER BY `time` DESC " . App::db()->pagination());
                }
                for ($i = 0; $res = $req->fetch(); ++$i) {
                    echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                    $q3 = App::db()->query("SELECT `id`, `refid`, `text` FROM `forum__` WHERE `type`='r' AND `id`='" . $res['refid'] . "'");
                    $razd = $q3->fetch();
                    $q4 = App::db()->query("SELECT `text` FROM `forum__` WHERE `type`='f' AND `id`='" . $razd['refid'] . "'");
                    $frm = $q4->fetch();
                    $colmes = App::db()->query("SELECT * FROM `forum__` WHERE `refid` = '" . $res['id'] . "' AND `type` = 'm'" . (App::user()->rights >= 7 ? '' : " AND `close` != '1'") . " ORDER BY `time` DESC");
                    $colmes1 = $colmes->rowCount();
                    $cpg = ceil($colmes1 / App::user()->settings['page_size']);
                    $nick = $colmes->fetch();
                    if ($res['edit']) {
                        echo Functions::getIcon('forum_closed.png');
                    } elseif ($res['close']) {
                        echo Functions::getIcon('forum_deleted.png');
                    } else {
                        echo Functions::getIcon('forum_new.png');
                    }
                    if ($res['realid'] == 1)
                        echo '&#160;' . Functions::loadModuleImage('chart.png');
                    echo '&#160;<a href="' . $url . '?id=' . $res['id'] . ($cpg > 1 && $set_forum['upfp'] && $set_forum['postclip'] ? '&amp;clip' : '') . ($set_forum['upfp'] && $cpg > 1 ? '&amp;page=' . $cpg : '') . '">' . $res['text'] .
                        '</a>&#160;[' . $colmes1 . ']';
                    if ($cpg > 1)
                        echo '<a href="' . $url . '?id=' . $res['id'] . (!$set_forum['upfp'] && $set_forum['postclip'] ? '&amp;clip' : '') . ($set_forum['upfp'] ? '' : '&amp;page=' . $cpg) . '">&#160;&gt;&gt;</a>';
                    echo '<br /><div class="sub"><a href="' . $url . '?id=' . $razd['id'] . '">' . $frm['text'] . '&#160;/&#160;' . $razd['text'] . '</a><br />';
                    echo $res['from'];
                    if ($colmes1 > 1) {
                        echo '&#160;/&#160;' . $nick['from'];
                    }
                    echo ' <span class="gray">' . date("d.m.y / H:i", $nick['time']) . '</span>' .
                        '</div></div>';
                }
            } else {
                echo '<div class="menu"><p>' . __('unread_period_empty') . '</p></div>';
            }
            echo '<div class="phdr">' . __('total') . ': ' . $count . '</div>';
            if ($count > App::user()->settings['page_size']) {
                echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=period&amp;vr=' . $vr . '&amp;', App::vars()->start, $count, App::user()->settings['page_size']) . '</div>' .
                    '<p><form action="' . $url . '?act=period&amp;vr=' . $vr . '" method="post">' .
                    '<input type="text" name="page" size="2"/>' .
                    '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
                    '</form></p>';
            }
            echo '<p><a href="' . $url . '">' . __('back') . '</a></p>';
            break;

        default:
            /*
            -----------------------------------------------------------------
            Вывод непрочитанных тем (для зарегистрированных)
            -----------------------------------------------------------------
            */
            $total = Counters::forumMessagesNew();
            echo '<div class="phdr"><a href="' . $url . '"><b>' . __('forum') . '</b></a> | ' . __('unread') . '</div>';
            if ($total > App::user()->settings['page_size']) {
                echo '<div class="topmenu">' . Functions::displayPagination($url . '?', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
            }
            if ($total > 0) {
                $req = App::db()->query("SELECT * FROM `forum__`
                    LEFT JOIN `forum__rdm` ON `forum__`.`id` = `forum__rdm`.`topic_id` AND `forum__rdm`.`user_id` = " . App::user()->id . "
                    WHERE `forum__`.`type`='t'" . (App::user()->rights >= 7 ? "" : " AND `forum__`.`close` != '1'") . "
                    AND (`forum__rdm`.`topic_id` IS NULL
                    OR `forum__`.`time` > `forum__rdm`.`time`)
                    ORDER BY `forum__`.`time` DESC
                    " . App::db()->pagination()
                );
                for ($i = 0; $res = $req->fetch(); ++$i) {
                    if ($res['close']) {
                        echo '<div class="rmenu">';
                    } else {
                        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                    }
                    $q3 = App::db()->query("SELECT `id`, `refid`, `text` FROM `forum__` WHERE `type` = 'r' AND `id` = '" . $res['refid'] . "' LIMIT 1");
                    $razd = $q3->fetch();
                    $q4 = App::db()->query("SELECT `id`, `text` FROM `forum__` WHERE `type`='f' AND `id` = '" . $razd['refid'] . "' LIMIT 1");
                    $frm = $q4->fetch();
                    $colmes = App::db()->query("SELECT `from`, `time` FROM `forum__` WHERE `refid` = '" . $res['id'] . "' AND `type` = 'm'" . (App::user()->rights >= 7 ? '' : " AND `close` != '1'") . " ORDER BY `time` DESC");
                    $colmes1 = $colmes->rowCount();
                    $cpg = ceil($colmes1 / App::user()->settings['page_size']);
                    $nick = $colmes->fetch();
                    // Значки
                    $icons = [
                        (isset($np) ? (!$res['vip'] ? Functions::getIcon('forum_normal.png') : '') : Functions::getIcon('forum_new.png')),
                        ($res['vip'] ? Functions::getIcon('forum_pin.png') : ''),
                        ($res['realid'] ? Functions::loadModuleImage('chart.png') : ''),
                        ($res['edit'] ? Functions::getIcon('forum_closed.png') : '')
                    ];
                    echo Functions::displayMenu($icons, '&#160;', '&#160;');
                    echo '<a href="' . $url . '?id=' . $res['id'] . ($cpg > 1 && $set_forum['upfp'] && $set_forum['postclip'] ? '&amp;clip' : '') . ($set_forum['upfp'] && $cpg > 1 ? '&amp;page=' . $cpg : '') . '">' . $res['text'] .
                        '</a>&#160;[' . $colmes1 . ']';
                    if ($cpg > 1)
                        echo '&#160;<a href="' . $url . '?id=' . $res['id'] . (!$set_forum['upfp'] && $set_forum['postclip'] ? '&amp;clip' : '') . ($set_forum['upfp'] ? '' : '&amp;page=' . $cpg) . '">&gt;&gt;</a>';
                    echo '<div class="sub">' . $res['from'] . ($colmes1 > 1 ? '&#160;/&#160;' . $nick['from'] : '') .
                        ' <span class="gray">(' . Functions::displayDate($nick['time']) . ')</span><br />' .
                        '<a href="' . $url . '?id=' . $frm['id'] . '">' . $frm['text'] . '</a>&#160;/&#160;<a href="' . $url . '?id=' . $razd['id'] . '">' . $razd['text'] . '</a>' .
                        '</div></div>';
                }
            } else {
                echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
            }
            echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
            if ($total > App::user()->settings['page_size']) {
                echo '<div class="topmenu">' . Functions::displayPagination($url . '?', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
                    '<p><form action="' . $url . '" method="post">' .
                    '<input type="text" name="page" size="2"/>' .
                    '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
                    '</form></p>';
            }
            echo '<p>';
            if ($total) {
                echo '<a href="' . $url . '?act=reset">' . __('unread_reset') . '</a><br/>';
            }
            echo '<a href="' . $url . '?act=select">' . __('unread_show_for_period') . '</a></p>';
    }
} else {
    /*
    -----------------------------------------------------------------
    Вывод 10 последних тем (для незарегистрированных)
    -----------------------------------------------------------------
    */
    echo '<div class="phdr"><a href="' . $url . '"><b>' . __('forum') . '</b></a> | ' . __('unread_last_10') . '</div>';
    $req = App::db()->query("SELECT * FROM `forum__` WHERE `type` = 't' AND `close` != '1' ORDER BY `time` DESC LIMIT 10");
    if ($req->rowCount()) {
        for ($i = 0; $res = $req->fetch(); ++$i) {
            $q3 = App::db()->query("SELECT `id`, `refid`, `text` FROM `forum__` WHERE type='r' AND id='" . $res['refid'] . "' LIMIT 1");
            $razd = $q3->fetch();
            $q4 = App::db()->query("SELECT `id`, `refid`, `text` FROM `forum__` WHERE type='f' AND id='" . $razd['refid'] . "' LIMIT 1");
            $frm = $q4->fetch();
            $nikuser = App::db()->query("SELECT `from`, `time` FROM `forum__` WHERE `type` = 'm' AND `close` != '1' AND `refid` = '" . $res['id'] . "'ORDER BY `time` DESC");
            $colmes1 = $nikuser->rowCount();
            $cpg = ceil($colmes1 / App::user()->settings['page_size']);
            $nam = $nikuser->fetch();
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            // Значки
            $icons = [
                //TODO: Разобраться с переменной!
                ($np ? (!$res['vip'] ? Functions::getIcon('forum_normal.png') : '') : Functions::getIcon('forum_new.png')),
                ($res['vip'] ? Functions::getIcon('forum_pin.png') : ''),
                ($res['realid'] ? Functions::loadModuleImage('chart.png') : ''),
                ($res['edit'] ? Functions::getIcon('forum_closed.png') : '')
            ];
            echo Functions::displayMenu($icons, '&#160;', '&#160;');
            echo '<a href="' . $url . '?id=' . $res['id'] . ($cpg > 1 && $_SESSION['uppost'] ? '&amp;clip&amp;page=' . $cpg : '') . '">' . $res['text'] . '</a>&#160;[' . $colmes1 . ']';
            if ($cpg > 1) {
                echo '&#160;<a href="' . $url . '?id=' . $res['id'] . ($_SESSION['uppost'] ? '' : '&amp;clip&amp;page=' . $cpg) . '">&gt;&gt;</a>';
            }
            echo '<br/><div class="sub"><a href="' . $url . '?id=' . $razd['id'] . '">' . $frm['text'] . '&#160;/&#160;' . $razd['text'] . '</a><br />';
            echo $res['from'];
            if (!empty($nam['from'])) {
                echo '&#160;/&#160;' . $nam['from'];
            }
            echo ' <span class="gray">' . date("d.m.y / H:i", $nam['time']) . '</span>';
            echo '</div></div>';
        }
    } else {
        echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
    }
    echo '<div class="phdr"><a href="' . $url . '">' . __('to_forum') . '</a></div>';
}
