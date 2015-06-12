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

if (!$al) {
    echo __('error_wrong_data');
    exit;
}
$req = mysql_query("SELECT * FROM `album__cat` WHERE `id` = '$al'");
if (!mysql_num_rows($req)) {
    echo __('error_wrong_data');
    exit;
}

$album = mysql_fetch_assoc($req);
$view = isset($_GET['view']);

/*
-----------------------------------------------------------------
Показываем выбранный альбом с фотографиями
-----------------------------------------------------------------
*/
echo '<div class="phdr"><a href="' . $url . '"><b>' . __('photo_albums') . '</b></a> | <a href="' . $url . '?act=list&amp;user=' . $user['id'] . '">' . __('personal_2') . '</a></div>' .
    '<div class="user"><p>' . Functions::displayUser($user, ['iphide' => 1,]) . '</p></div>' .
    '<div class="phdr">' . __('album') . ': ';
echo $view ? '<a href="' . $url . '?act=show&amp;al=' . $al . '&amp;user=' . $user['id'] . '"><b>' . htmlspecialchars($album['name']) . '</b></a>' : '<b>' . htmlspecialchars($album['name']) . '</b>';
echo '</div>';
if (!empty($album['description'])) {
    echo '<div class="topmenu">' . htmlspecialchars($album['description']) . '</div>';
}

/*
-----------------------------------------------------------------
Проверяем права доступа к альбому
-----------------------------------------------------------------
*/
if ($album['access'] != 2)
    unset($_SESSION['ap']);
if ($album['access'] == 1 && $user['id'] != App::user()->id && App::user()->rights < 6) {
    // Если доступ закрыт
    echo __('access_forbidden') .
        '<div class="phdr"><a href="' . $url . '?act=list&amp;user=' . $user['id'] . '">' . __('album_list') . '</a></div>';
    exit;
} elseif ($album['access'] == 2 && $user['id'] != App::user()->id && App::user()->rights < 6) {
    // Если доступ через пароль
    if (isset($_POST['password'])) {
        if ($album['password'] == trim($_POST['password']))
            $_SESSION['ap'] = $album['password'];
        else
            echo __('error_wrong_password');
    }
    if (!isset($_SESSION['ap']) || $_SESSION['ap'] != $album['password']) {
        echo '<form action="' . $url . '?act=show&amp;al=' . $al . '&amp;user=' . $user['id'] . '" method="post"><div class="menu"><p>' .
            __('album_password') . '<br />' .
            '<input type="text" name="password"/></p>' .
            '<p><input type="submit" name="submit" value="' . __('login') . '"/></p>' .
            '</div></form>' .
            '<div class="phdr"><a href="' . $url . '?act=list&amp;user=' . $user['id'] . '">' . __('album_list') . '</a></div>';
        exit;
    }
}

/*
-----------------------------------------------------------------
Просмотр альбома и фотографий
-----------------------------------------------------------------
*/
if ($view) {
    App::user()->settings['page_size'] = 1;
    App::vars()->start = isset($_REQUEST['page']) ? App::vars()->page - 1 : (mysql_result(mysql_query("SELECT COUNT(*) FROM `album__files` WHERE `album_id` = '$al' AND `id` > '$img'"), 0));
    // Обрабатываем ссылку для возврата
    if (empty($_SESSION['ref']))
        $_SESSION['ref'] = htmlspecialchars($_SERVER['HTTP_REFERER']);
} else {
    unset($_SESSION['ref']);
}
$total = mysql_result(mysql_query("SELECT COUNT(*) FROM `album__files` WHERE `album_id` = '$al'"), 0);
if ($total > App::user()->settings['page_size'])
    echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=show&amp;al=' . $al . '&amp;user=' . $user['id'] . '&amp;' . ($view ? 'view&amp;' : ''), App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
if ($total) {
    $req = mysql_query("SELECT * FROM `album__files` WHERE `user_id` = '" . $user['id'] . "' AND `album_id` = '$al' ORDER BY `id` DESC " . App::db()->pagination());
    for ($i = 0; $res = mysql_fetch_assoc($req); ++$i) {
        echo($i % 2 ? '<div class="list2">' : '<div class="list1">');
        if ($view) {
            /*
            -----------------------------------------------------------------
            Предпросмотр отдельного изображения
            -----------------------------------------------------------------
            */
            if ($user['id'] == App::user()->id && isset($_GET['profile'])) {
                copy(
                    ALBUMPATH . $user['id'] . DIRECTORY_SEPARATOR . $res['tmb_name'],
                    ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR . App::user()->id . '_small.jpg'
                );
                copy(
                    ALBUMPATH . $user['id'] . DIRECTORY_SEPARATOR . $res['img_name'],
                    ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR . App::user()->id . '.jpg'
                );
                echo '<span class="green"><b>' . __('photo_profile_ok') . '</b></span><br />';
            }
            echo '<a href="' . $_SESSION['ref'] . '"><img src="' . App::cfg()->sys->homeurl . 'assets/misc/album_image.php?u=' . $user['id'] . '&amp;f=' . $res['img_name'] . '" /></a>';
            // Счетчик просмотров
            if (!mysql_result(mysql_query("SELECT COUNT(*) FROM `album__views` WHERE `user_id` = '" . App::user()->id . "' AND `file_id` = '" . $res['id'] . "'"), 0)) {
                mysql_query("INSERT INTO `album__views` SET `user_id` = '" . App::user()->id . "', `file_id` = '" . $res['id'] . "', `time` = '" . time() . "'");
                $views = mysql_result(mysql_query("SELECT COUNT(*) FROM `album__views` WHERE `file_id` = '" . $res['id'] . "'"), 0);
                mysql_query("UPDATE `album__files` SET `views` = '$views' WHERE `id` = '" . $res['id'] . "'");
            }
        } else {
            /*
            -----------------------------------------------------------------
            Предпросмотр изображения в списке
            -----------------------------------------------------------------
            */
            echo '<a href="' . $url . '?act=show&amp;al=' . $al . '&amp;img=' . $res['id'] . '&amp;user=' . $user['id'] . '&amp;view"><img src="' . App::cfg()->sys->homeurl . 'files/users/album/' . $user['id'] . '/' . $res['tmb_name'] . '" /></a>';
        }
        if (!empty($res['description']))
            echo '<div class="gray">' . Functions::smilies(htmlspecialchars($res['description'])) . '</div>';
        echo '<div class="sub">';
        if ($user['id'] == App::user()->id || App::user()->rights >= 6) {
            echo implode(' | ', [
                '<a href="' . $url . '?act=image_edit&amp;img=' . $res['id'] . '&amp;user=' . $user['id'] . '">' . __('edit') . '</a>',
                '<a href="' . $url . '?act=image_move&amp;img=' . $res['id'] . '&amp;user=' . $user['id'] . '">' . __('move') . '</a>',
                '<a href="' . $url . '?act=image_delete&amp;img=' . $res['id'] . '&amp;user=' . $user['id'] . '">' . __('delete') . '</a>'
            ]);
            if ($user['id'] == App::user()->id && $view)
                echo ' | <a href="' . $url . '?act=show&amp;al=' . $al . '&amp;user=' . $user['id'] . '&amp;view&amp;img=' . $res['id'] . '&amp;profile">' . __('photo_profile') . '</a>';
        }
        echo Album::vote($res) .
            '<div class="gray">' . __('count_views') . ': ' . $res['views'] . ', ' . __('count_downloads') . ': ' . $res['downloads'] . '</div>' .
            '<div class="gray">' . __('date') . ': ' . Functions::displayDate($res['time']) . '</div>' .
            '<a href="' . $url . '?act=comments&amp;user=' . $user['id'] . '&amp;img=' . $res['id'] . '">' . __('comments') . '</a> (' . $res['comm_count'] . ')<br />' .
            '<a href="' . $url . '?act=image_download&amp;img=' . $res['id'] . '">' . __('download') . '</a>' .
            '</div></div>';
    }
} else {
    echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
}
if ($user['id'] == App::user()->id || App::user()->rights >= 7) {
    echo '<div class="gmenu">' .
        '<form action="' . $url . '?act=image_upload&amp;al=' . $al . '&amp;user=' . $user['id'] . '" method="post">' .
        '<p><input type="submit" value="' . __('image_add') . '"/></p>' .
        '</form></div>';
}
echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
if ($total > App::user()->settings['page_size']) {
    echo '<div class="topmenu">' . Functions::displayPagination($url . '?act=show&amp;al=' . $al . '&amp;user=' . $user['id'] . '&amp;' . ($view ? 'view&amp;' : ''), App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
        '<p><form action="' . $url . '?act=show&amp;al=' . $al . '&amp;user=' . $user['id'] . ($view ? '&amp;view' : '') . '" method="post">' .
        '<input type="text" name="page" size="2"/>' .
        '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
        '</form></p>';
}
echo '<p><a href="' . $url . '?act=list&amp;user=' . $user['id'] . '">' . __('album_list') . '</a></p>';