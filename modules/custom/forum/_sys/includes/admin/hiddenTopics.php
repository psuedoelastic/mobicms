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

$uri = App::router()->getUri(2);

echo '<div class="phdr"><a href="' . $uri . '"><b>' . __('forum_management') . '</b></a> | ' . __('hidden_topics') . '</div>';
$sort = '';

if (isset($_GET['usort'])) {
    $sort = " AND `" . TP . "forum__`.`user_id` = '" . abs(intval($_GET['usort'])) . "'";
    $uri .= '&amp;usort=' . abs(intval($_GET['usort']));
    echo '<div class="bmenu">' . __('filter_on_author') . ' <a href="../../../../../index.php?act=forum&amp;mod=htopics">[x]</a></div>';
}

if (isset($_GET['rsort'])) {
    $sort = " AND `" . TP . "forum__`.`refid` = '" . abs(intval($_GET['rsort'])) . "'";
    $uri .= '&amp;rsort=' . abs(intval($_GET['rsort']));
    echo '<div class="bmenu">' . __('filter_on_section') . ' <a href="../../../../../index.php?act=forum&amp;mod=htopics">[x]</a></div>';
}

if (isset($_POST['deltopic'])) {
    if (App::user()->rights != 9) {
        echo __('access_forbidden');
        exit;
    }
    $req = App::db()->query("SELECT `id` FROM `" . TP . "forum__` WHERE `type` = 't' AND `close` = '1' " . $sort);
    while ($res = $req->fetch()) {
        $req_f = App::db()->query("SELECT * FROM `" . TP . "forum__files` WHERE `topic` = '" . $res['id'] . "'");
        if ($req_f->rowCount()) {
            // Удаляем файлы
            while ($res_f = $req_f->fetch()) {
                unlink(ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'forum' . DIRECTORY_SEPARATOR . $res_f['filename']);
            }
            App::db()->exec("DELETE FROM `" . TP . "forum__files` WHERE `topic` = '" . $res['id'] . "'");
        }
        // Удаляем посты
        App::db()->exec("DELETE FROM `" . TP . "forum__` WHERE `type` = 'm' AND `refid` = '" . $res['id'] . "'");
    }
    // Удаляем темы
    App::db()->exec("DELETE FROM `" . TP . "forum__` WHERE `type` = 't' AND `close` = '1' " . $sort);
    header('Location: ' . $uri . 'htopics/');
} else {
    $total = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `type` = 't' AND `close` = '1' " . $sort)->fetchColumn();
    if ($total > App::user()->settings['page_size']) {
        echo '<div class="topmenu">' . Functions::displayPagination('index.php?act=forum&amp;mod=htopics&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
    }
    $req = App::db()->query("SELECT `" . TP . "forum__`.*, `" . TP . "forum__`.`id` AS `fid`, `" . TP . "forum__`.`user_id` AS `id`, `" . TP . "forum__`.`from` AS `name`, `" . TP . "forum__`.`soft` AS `browser`, `" . TP . "user__`.`rights`, `" . TP . "user__`.`last_visit`, `" . TP . "user__`.`sex`, `" . TP . "user__`.`status`, `" . TP . "user__`.`join_date`
            FROM `" . TP . "forum__` LEFT JOIN `" . TP . "user__` ON `" . TP . "forum__`.`user_id` = `" . TP . "user__`.`id`
            WHERE `" . TP . "forum__`.`type` = 't'
            AND `" . TP . "forum__`.`close` = '1' $sort
            ORDER BY `" . TP . "forum__`.`id` DESC " . App::db()->pagination());
    if ($req->rowCount()) {
        $i = 0;
        while ($res = $req->fetch()) {
            $subcat = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = '" . $res['refid'] . "'")->fetch();
            $cat = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = '" . $subcat['refid'] . "'")->fetch();
            $ttime = '<span class="gray">(' . Functions::displayDate($res['time']) . ')</span>';
            $text = '<a href="../forum/index.php?id=' . $res['fid'] . '"><b>' . $res['text'] . '</b></a>';
            $text .= '<br /><small><a href="../forum/index.php?id=' . $cat['id'] . '">' . $cat['text'] . '</a> / <a href="../forum/index.php?id=' . $subcat['id'] . '">' . $subcat['text'] . '</a></small>';
            $subtext = '<span class="gray">' . __('filter_to') . ':</span> ';
            $subtext .= '<a href="index.php?act=forum&amp;mod=htopics&amp;rsort=' . $res['refid'] . '">' . __('by_section') . '</a> | ';
            $subtext .= '<a href="index.php?act=forum&amp;mod=htopics&amp;usort=' . $res['user_id'] . '">' . __('by_author') . '</a>';
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            echo Functions::displayUser($res, [
                'header' => $ttime,
                'body'   => $text,
                'sub'    => $subtext
            ]);
            echo '</div>';
            ++$i;
        }
        if (App::user()->rights == 9)
            echo '<form action="index.php?act=forum&amp;mod=htopics' . $uri . '" method="POST">' .
                '<div class="rmenu">' .
                '<input type="submit" name="deltopic" value="' . __('delete_all') . '" />' .
                '</div></form>';
    } else {
        echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
    }
    echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
    if ($total > App::user()->settings['page_size']) {
        echo '<div class="topmenu">' . Functions::displayPagination('index.php?act=forum&amp;mod=htopics&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
            '<p><form action="../../../../../index.php?act=forum&amp;mod=htopics" method="post">' .
            '<input type="text" name="page" size="2"/>' .
            '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
            '</form></p>';
    }
}