<?php

/**
 * @package     mobiCMS
 * @link        http://mobicms.net
 * @copyright   Copyright (C) 2008-2012 mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://mobicms.net/about
 */

defined('_IN_MOBICMS') or die('Error: restricted access');

//TODO: переделать SQL запросы

$url = App::router()->getUri(1);
$mod = App::request()->getQuery('mod');

switch ($mod) {
    case 'my_new_comm':
        /*
        -----------------------------------------------------------------
        Непрочитанные комментарии в личных альбомах
        -----------------------------------------------------------------
        */
        if (!App::user()->id || App::user()->id != $user['id']) {
            echo __('wrong_data');
            exit;
        }
        $title = __('unread_comments');
        $select = "";
        $join = "INNER JOIN `album__comments` ON `album__files`.`id` = `album__comments`.`sub_id`";
        $where = "`album__files`.`user_id` = '" . App::user()->id . "' AND `album__files`.`unread_comments` = 1 GROUP BY `album__files`.`id`";
        $order = "`album__comments`.`time` DESC";
        $url = '&amp;mod=my_new_comm';
        break;

    case 'last_comm':
        /*
        -----------------------------------------------------------------
        Последние комментарии по всем альбомам
        -----------------------------------------------------------------
        */
        $total = mysql_result(mysql_query("SELECT COUNT(DISTINCT `sub_id`) FROM `album__comments` WHERE `time` >" . (time() - 86400)), 0);
        $title = __('new_comments');
        $select = "";
        $join = "INNER JOIN `album__comments` ON `album__files`.`id` = `album__comments`.`sub_id`";
        $where = "`album__comments`.`time` > " . (time() - 86400) . " GROUP BY `album__files`.`id`";
        $order = "`album__comments`.`time` DESC";
        $url = '&amp;mod=last_comm';
        break;

    case 'views':
        /*
        -----------------------------------------------------------------
        ТОП просмотров
        -----------------------------------------------------------------
        */
        $title = __('top_views');
        $select = "";
        $join = "";
        $where = "`album__files`.`views` > '0'" . (App::user()->rights >= 6 ? "" : " AND `album__files`.`access` = '4'");
        $order = "`views` DESC";
        $url = '&amp;mod=views';
        break;

    case 'downloads':
        /*
        -----------------------------------------------------------------
        ТОП скачиваний
        -----------------------------------------------------------------
        */
        $title = __('top_downloads');
        $select = "";
        $join = "";
        $where = "`album__files`.`downlosystem__advt` > 0" . (App::user()->rights >= 6 ? "" : " AND `album__files`.`access` = '4'");
        $order = "`downlosystem__advt` DESC";
        $url = '&amp;mod=downloads';
        break;

    case 'comments':
        /*
        -----------------------------------------------------------------
        ТОП комментариев
        -----------------------------------------------------------------
        */
        $title = __('top_comments');
        $select = "";
        $join = "";
        $where = "`album__files`.`comm_count` > '0'" . (App::user()->rights >= 6 ? "" : " AND `album__files`.`access` = '4'");
        $order = "`comm_count` DESC";
        $url = '&amp;mod=comments';
        break;

    case 'votes':
        /*
        -----------------------------------------------------------------
        ТОП положительных голосов
        -----------------------------------------------------------------
        */
        $title = __('top_votes');
        $select = ", (`vote_plus` - `vote_minus`) AS `rating`";
        $join = "";
        $where = "(`vote_plus` - `vote_minus`) > 2" . (App::user()->rights >= 6 ? "" : " AND `album__files`.`access` = '4'");
        $order = "`rating` DESC";
        $url = '&amp;mod=votes';
        break;

    case 'trash':
        /*
        -----------------------------------------------------------------
        ТОП отрицательных голосов
        -----------------------------------------------------------------
        */
        $title = __('top_trash');
        $select = ", (`vote_plus` - `vote_minus`) AS `rating`";
        $join = "";
        $where = "(`vote_plus` - `vote_minus`) < -2" . (App::user()->rights >= 6 ? "" : " AND `album__files`.`access` = '4'");
        $order = "`rating` ASC";
        $url = '&amp;mod=trash';
        break;

    default:
        /*
        -----------------------------------------------------------------
        Новые изображения
        -----------------------------------------------------------------
        */
        $title = __('new_photo');
        $select = "";
        $join = "";
        $where = "`album__files`.`time` > '" . (time() - 259200) . "'" . (App::user()->rights >= 6 ? "" : " AND `album__files`.`access` > '1'");
        $order = "`album__files`.`time` DESC";
        $url = '';
}

/*
-----------------------------------------------------------------
Показываем список фотографий, отсортированных по рейтингу
-----------------------------------------------------------------
*/
unset($_SESSION['ref']);
echo '<div class="phdr"><a href="' . $url . '"><b>' . __('photo_albums') . '</b></a> | ' . $title . '</div>';

if ($mod == 'my_new_comm') {
    $total = $new_album_comm;
} elseif (!isset($total)) {
    $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `album__files` WHERE $where"), 0);
}

if ($total) {
    if ($total > App::user()->settings['page_size'])
        echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=top' . $url . '&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
    $req = mysql_query("SELECT `album__files`.*, `user__`.`nickname` AS `user_name`, `album__cat`.`name` AS `album_name` $select
        FROM `album__files`
        INNER JOIN `user__` ON `album__files`.`user_id` = `user__`.`id`
        INNER JOIN `album__cat` ON `album__files`.`album_id` = `album__cat`.`id`
        $join
        WHERE $where
        ORDER BY $order
        " . App::db()->pagination()
    );
    for ($i = 0; $res = mysql_fetch_assoc($req); ++$i) {
        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
        if ($res['access'] == 4 || App::user()->rights >= 7) {
            // Если доступ открыт всем, или смотрит Администратор
            echo '<a href="' . $url . '?act=show&amp;al=' . $res['album_id'] . '&amp;img=' . $res['id'] . '&amp;user=' . $res['user_id'] . '&amp;view">' .
                '<img src="' . App::cfg()->sys->homeurl . 'files/users/album/' . $res['user_id'] . '/' . $res['tmb_name'] . '" /></a>';
            if (!empty($res['description']))
                echo '<div class="gray">' . Functions::smilies(htmlspecialchars($res['description'])) . '</div>';
        } elseif ($res['access'] == 3) {
            // Если доступ открыт друзьям
            echo 'Только для друзей';
        } elseif ($res['access'] == 2) {
            // Если доступ по паролю
            echo '<a href="' . $url . '?act=show&amp;al=' . $res['album_id'] . '&amp;img=' . $res['id'] . '&amp;user=' . $res['user_id'] . '">' . Functions::getImage('password.gif') . '</a>';
        }
        echo '<div class="sub">' .
            '<a href="' . $url . '?act=list&amp;user=' . $res['user_id'] . '"><b>' . $res['user_name'] . '</b></a> | <a href="' . $url . '?act=show&amp;al=' . $res['album_id'] . '&amp;user=' . $res['user_id'] . '">' . htmlspecialchars($res['album_name']) . '</a>';
        if ($res['access'] == 4 || App::user()->rights >= 6) {
            echo Album::vote($res) .
                '<div class="gray">' . __('count_views') . ': ' . $res['views'] . ', ' . __('count_downloads') . ': ' . $res['downloads'] . '</div>' .
                '<div class="gray">' . __('date') . ': ' . Functions::displayDate($res['time']) . '</div>' .
                '<a href="' . $url . '?act=comments&amp;img=' . $res['id'] . '">' . __('comments') . '</a> (' . $res['comm_count'] . ')' .
                '<br /><a href="' . $url . '?act=image_download&amp;img=' . $res['id'] . '">' . __('download') . '</a>';
        }
        echo '</div></div>';
    }
} else {
    echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
}
echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
if ($total > App::user()->settings['page_size']) {
    echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=top' . $url . '&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
        '<p><form action="' . $url . '?act=top' . $url . '" method="post">' .
        '<input type="text" name="page" size="2"/>' .
        '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
        '</form></p>';
}