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

if (($set_forum = App::user()->getData('set_forum')) === false) {
    $set_forum = [
        'farea'    => 0,
        'upfp'     => 0,
        'preview'  => 1,
        'postclip' => 1,
        'postcut'  => 2
    ];
}

$uri = App::router()->getUri(2);

echo '<div class="phdr"><a href="' . $uri . '"><b>' . __('forum_management') . '</b></a> | ' . __('hidden_posts') . '</div>';
$sort = '';
$uri = '';
if (isset($_GET['tsort'])) {
    $sort = " AND `forum__`.`refid` = '" . abs(intval($_GET['tsort'])) . "'";
    $uri = '&amp;tsort=' . abs(intval($_GET['tsort']));
    echo '<div class="bmenu">' . __('filter_on_theme') . ' <a href="../../../../../index.php?act=forum&amp;mod=hposts">[x]</a></div>';
} elseif (isset($_GET['usort'])) {
    $sort = " AND `forum__`.`user_id` = '" . abs(intval($_GET['usort'])) . "'";
    $uri = '&amp;usort=' . abs(intval($_GET['usort']));
    echo '<div class="bmenu">' . __('filter_on_author') . ' <a href="../../../../../index.php?act=forum&amp;mod=hposts">[x]</a></div>';
}
if (isset($_POST['delpost'])) {
    if (App::user()->rights != 9) {
        echo __('access_forbidden');
        exit;
    }
    $req = App::db()->query("SELECT `id` FROM `forum__` WHERE `type` = 'm' AND `close` = '1' " . $sort);
    while ($res = $req->fetch()) {
        $req_f = App::db()->query("SELECT * FROM `forum__files` WHERE `post` = '" . $res['id'] . "' LIMIT 1");
        if ($req_f->rowCount()) {
            $res_f = $req_f->fetch();
            // Удаляем файлы
            unlink(ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'forum' . DIRECTORY_SEPARATOR . $res_f['filename']);
            App::db()->exec("DELETE FROM `forum__files` WHERE `post` = '" . $res['id'] . "' LIMIT 1");
        }
    }
    // Удаляем посты
    App::db()->exec("DELETE FROM `forum__` WHERE `type` = 'm' AND `close` = '1' " . $sort);
    header('Location: ' . $uri . 'hposts/');
} else {
    $total = App::db()->query("SELECT COUNT(*) FROM `forum__` WHERE `type` = 'm' AND `close` = '1' " . $sort)->fetchColumn();
    if ($total > App::user()->settings['page_size']) {
        echo '<div class="topmenu">' . Functions::displayPagination('index.php?act=forum&amp;mod=hposts&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
    }
    $req = App::db()->query("SELECT `forum__`.*, `forum__`.`id` AS `fid`, `forum__`.`user_id` AS `id`, `forum__`.`from` AS `name`, `forum__`.`soft` AS `browser`, `user__`.`rights`, `user__`.`last_visit`, `user__`.`sex`, `user__`.`status`, `user__`.`join_date`
            FROM `forum__` LEFT JOIN `user__` ON `forum__`.`user_id` = `user__`.`id`
            WHERE `forum__`.`type` = 'm'
            AND `forum__`.`close` = '1' $sort
            ORDER BY `forum__`.`id` DESC " . App::db()->pagination());
    if ($req->rowCount()) {
        $i = 0;
        while ($res = $req->fetch()) {
            $res['ip'] = ip2long($res['ip']);
            $posttime = ' <span class="gray">(' . Functions::displayDate($res['time']) . ')</span>';
            $page = ceil(App::db()->query("SELECT COUNT(*) FROM `forum__` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">=" : "<=") . " '" . $res['fid'] . "'")->fetchColumn() / App::user()->settings['page_size']);
            $text = mb_substr($res['text'], 0, 500);
            $text = htmlspecialchars($text);
            $text = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $text);
            $theme = App::db()->query("SELECT `id`, `text` FROM `forum__` WHERE `id` = '" . $res['refid'] . "'")->fetch();
            $text = '<b>' . $theme['text'] . '</b> <a href="../forum/index.php?id=' . $theme['id'] . '&amp;page=' . $page . '">&gt;&gt;</a><br />' . $text;
            $subtext = '<span class="gray">' . __('filter_to') . ':</span> ';
            $subtext .= '<a href="index.php?act=forum&amp;mod=hposts&amp;tsort=' . $theme['id'] . '">' . __('by_theme') . '</a> | ';
            $subtext .= '<a href="index.php?act=forum&amp;mod=hposts&amp;usort=' . $res['user_id'] . '">' . __('by_author') . '</a>';
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            echo Functions::displayUser($res, [
                'header' => $posttime,
                'body'   => $text,
                'sub'    => $subtext
            ]);
            echo '</div>';
            ++$i;
        }
        if (App::user()->rights == 9)
            echo '<form action="index.php?act=forum&amp;mod=hposts' . $uri . '" method="POST"><div class="rmenu"><input type="submit" name="delpost" value="' . __('delete_all') . '" /></div></form>';
    } else {
        echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
    }
    echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
    if ($total > App::user()->settings['page_size']) {
        echo '<div class="topmenu">' . Functions::displayPagination('index.php?act=forum&amp;mod=hposts&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
            '<p><form action="../../../../../index.php?act=forum&amp;mod=hposts" method="post">' .
            '<input type="text" name="page" size="2"/>' .
            '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
            '</form></p>';
    }
}