<?php
/**
 * mobiCMS Content Management System (http://mobicms.net)
 *
 * For copyright and license information, please see the LICENSE.txt
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://mobicms.net mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 *
 * @module      Downloads
 * @author      FlySelf <flyself@mail.ru>
 * @author      Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version     DRAFT 2015-02-01
 */

defined('MOBICMS') or die('Error: restricted access');
$url = App::router()->getUri();

App::autoload()->import('Download', __DIR__ . DS . '_sys' . DS . 'classes' . DS . 'download.php');

$textl = __('download_title');
$down_path = FILES_PATH . 'downloads'; //TODO: переделать на константы
$screens_path = FILES_PATH . 'downloads/screen'; //TODO: переделать на константы
$files_path = FILES_PATH . 'downloads/files'; //TODO: переделать на константы

$id = abs(intval(App::request()->getQuery('id', 0)));
$act = App::request()->getQuery('act', false);

/*
-----------------------------------------------------------------
Настройки
-----------------------------------------------------------------
*/
//TODO: Переделать на получение настроек из таблицы модулей
$set_down =
    ['mod'           => 1,
     'theme_screen'  => 1,
     'top'           => 25,
     'icon_java'     => 1,
     'video_screen'  => 1,
     'screen_resize' => 1
    ];
if ($set_down['video_screen'] && !extension_loaded('ffmpeg')) $set_down['video_screen'] = 0;

/*
-----------------------------------------------------------------
Ограничиваем доступ к Загрузкам
-----------------------------------------------------------------
*/
$error = '';
if ((!isset(App::cfg()->sys->acl_downloads) || !App::cfg()->sys->acl_downloads) && App::user()->rights < 7) {
    $error = __('download_closed');
} elseif (isset(App::cfg()->sys->acl_downloads) && App::cfg()->sys->acl_downloads == 1 && !App::user()->id) {
    $error = __('access_guest_forbidden');
}
if ($error) {
    echo $error;
    exit;
}
$old = time() - 259200;

/*
-----------------------------------------------------------------
Список разрешений для выгрузки
-----------------------------------------------------------------
*/
$defaultExt = ['mp4',
    'rar',
    'zip',
    'pdf',
    'nth',
    'txt',
    'tar',
    'gz',
    'jpg',
    'jpeg',
    'gif',
    'png',
    'bmp',
    '3gp',
    'mp3',
    'mpg',
    'thm',
    'jad',
    'jar',
    'cab',
    'sis',
    'sisx',
    'exe',
    'msi',
    'apk',
    'djvu',
    'fb2'
];
/*
-----------------------------------------------------------------
Переключаем режимы работы
-----------------------------------------------------------------
*/
$actions = [
    'add_cat'         => '/category/',
    'edit_cat'        => '/category/',
    'delete_cat'      => '/category/',
    'mod_files'       => '/outputFiles/',
    'new_files'       => '/outputFiles/',
    'top_files'       => '/outputFiles/',
    'user_files'      => '/outputFiles/',
    'comments'        => '/comments/',
    'review_comments' => '/comments/',
    'edit_file'       => '/fileControl/',
    'delete_file'     => '/fileControl/',
    'edit_about'      => '/fileControl/',
    'edit_screen'     => '/fileControl/',
    'files_more'      => '/fileControl/',
    'jad_file'        => '/fileControl/',
    'mp3tags'         => '/fileControl/',
    'load_file'       => '/fileControl/',
    'open_zip'        => '/fileControl/',
    'txt_in_jar'      => '/fileControl/',
    'txt_in_zip'      => '/fileControl/',
    'view'            => '/fileControl/',
    'transfer_file'   => '/fileControl/',
    'custom_size'     => '/fileControl/',
    'down_file'       => '/upload/',
    'import'          => '/upload/',
    'scan_about'      => '/',
    'scan_dir'        => '/',
    'search'          => '/',
    'top_users'       => '/',
    'recount'         => '/',
    'bookmark'        => '/',
    'redirect'        => '/'
];

if (isset($actions[$act]) && is_file(__DIR__ . DS . '_sys' . DS . 'includes' . $actions[$act] . $act . '.php')) {
    require_once(__DIR__ . DS . '_sys' . DS . 'includes' . $actions[$act] . $act . '.php');
} else {
    if (!isset(App::cfg()->sys->acl_downloads) || !App::cfg()->sys->acl_downloads)
        echo '<div class="rmenu"><b>' . __('download_closed') . '</b></div>';
    /*
    -----------------------------------------------------------------
    Получаем список файлов и папок
    -----------------------------------------------------------------
    */
    $notice = false;
    if ($id) {
        $cat = App::db()->query("SELECT * FROM `download__category` WHERE `id` = " . $id);
        $res_down_cat = $cat->fetch();
        if (!$cat->rowCount() || !is_dir($res_down_cat['dir'])) {
            echo __('not_found_dir') . ' <a href="' . $url . '">' . __('download_title') . '</a>';
            exit;
        }
        $title_pages = htmlspecialchars(mb_substr($res_down_cat['rus_name'], 0, 30));
        $textl = mb_strlen($res_down_cat['rus_name']) > 30 ? $title_pages . '...' : $title_pages;
        $navigation = Download::navigation(['dir' => $res_down_cat['dir'], 'refid' => $res_down_cat['refid'], 'name' => $res_down_cat['rus_name']]);
        $total_new = App::db()->query("SELECT COUNT(*) FROM `download__files` WHERE `type` = '2'  AND `time` > $old AND `dir` LIKE '" . ($res_down_cat['dir']) . "%'")->fetchColumn();
        if ($total_new)
            $notice = '<a href="' . $url . '?act=new_files&amp;id=' . $id . '">' . __('new_files') . '</a> (' . $total_new . ')<br />';
    } else {
        $navigation = '<b>' . __('download_title') . '</b></div>' .
            '<div class="topmenu"><a href="' . $url . '?act=search">' . __('search') . '</a> | ' .
            '<a href="' . $url . '?act=top_files&amp;id=0">' . __('top_files') . '</a> | ' .
            '<a href="' . $url . '?act=top_users">' . __('top_users') . '</a>';
        $total_new = App::db()->query("SELECT COUNT(*) FROM `download__files` WHERE `type` = '2'  AND `time` > $old")->fetchColumn();
        if ($total_new) {
            $notice = '<a href="' . $url . '?act=new_files&amp;id=' . $id . '">' . __('new_files') . '</a> (' . $total_new . ')<br />';
        }
    }
    if (App::user()->rights == 4 || App::user()->rights >= 6) {
        $mod_files = App::db()->query("SELECT COUNT(*) FROM `download__files` WHERE `type` = '3'")->fetchColumn();
        if ($mod_files > 0) {
            $notice .= '<a href="' . $url . '?act=mod_files">' . __('mod_files') . '</a> ' . $mod_files;
        }
    }
    /*
    -----------------------------------------------------------------
    Уведомления
    -----------------------------------------------------------------
    */
    if ($notice) echo '<p>' . $notice . '</p>';
    /*
    -----------------------------------------------------------------
    Навигация
    -----------------------------------------------------------------
    */
    echo '<div class="phdr">' . $navigation . '</div>';
    /*
    -----------------------------------------------------------------
    Выводим список папок и файлов
    -----------------------------------------------------------------
    */
    $total_cat = App::db()->query("SELECT COUNT(*) FROM `download__category` WHERE `refid` = '" . $id . "'")->fetchColumn();
    $total_files = App::db()->query("SELECT COUNT(*) FROM `download__files` WHERE `refid` = '" . $id . "' AND `type` = 2")->fetchColumn();
    $sum_total = $total_files + $total_cat;
    if ($sum_total) {
        if ($total_cat > 0) {
            /*
             -----------------------------------------------------------------
             Выводи папки
             -----------------------------------------------------------------
             */
            if ($total_files) echo '<div class="phdr"><b>' . __('list_category') . '</b></div>';
            $req_down = App::db()->query("SELECT * FROM `download__category` WHERE `refid` = '" . $id . "' ORDER BY `sort` ASC ");
            $i = 0;
            while ($res_down = $req_down->fetch()) {
                echo (($i++ % 2) ? '<div class="list2">' : '<div class="list1">') .
                    App::image('folder.png', [], true) . '&#160;' .
                    '<a href="' . $url . '?id=' . $res_down['id'] . '">' . htmlspecialchars($res_down['rus_name']) . '</a> (' . $res_down['total'] . ')';
                if ($res_down['field'])
                    echo '<div><small>' . __('extensions') . ': <span class="green"><b>' . $res_down['text'] . '</b></span></small></div>';
                if (App::user()->rights == 4 || App::user()->rights >= 6 || !empty($res_down['desc'])) {
                    $menu = [
                        '<a href="' . $url . '?act=edit_cat&amp;id=' . $res_down['id'] . '&amp;up">' . __('up') . '</a>',
                        '<a href="' . $url . '?act=edit_cat&amp;id=' . $res_down['id'] . '&amp;down">' . __('down') . '</a>',
                        '<a href="' . $url . '?act=edit_cat&amp;id=' . $res_down['id'] . '">' . __('edit') . '</a>',
                        '<a href="' . $url . '?act=delete_cat&amp;id=' . $res_down['id'] . '">' . __('delete') . '</a>'
                    ];
                    echo '<div class="sub">' .
                        (!empty($res_down['desc']) ? '<div class="gray">' . htmlspecialchars($res_down['desc']) . '</div>' : '') .
                        (App::user()->rights == 4 || App::user()->rights >= 6 ? implode(' | ', $menu) : '') .
                        '</div>';
                }
                echo '</div>';
            }
        }
        if ($total_files > 0) {
            /*
             -----------------------------------------------------------------
             Выводи файлы
             -----------------------------------------------------------------
             */
            if ($total_cat) echo '<div class="phdr"><b>' . __('list_files') . '</b></div>';
            if ($total_files > 1) {
                /*
               -----------------------------------------------------------------
               Сортировка файлов
               -----------------------------------------------------------------
               */
                if (!isset($_SESSION['sort_down'])) $_SESSION['sort_down'] = 0;
                if (!isset($_SESSION['sort_down2'])) $_SESSION['sort_down2'] = 0;
                if (isset($_POST['sort_down']))
                    $_SESSION['sort_down'] = $_POST['sort_down'] ? 1 : 0;
                if (isset($_POST['sort_down2']))
                    $_SESSION['sort_down2'] = $_POST['sort_down2'] ? 1 : 0;
                $sql_sort = isset($_SESSION['sort_down']) && $_SESSION['sort_down'] ? ', `name`' : ', `time`';
                $sql_sort .= isset($_SESSION['sort_down2']) && $_SESSION['sort_down2'] ? ' ASC' : ' DESC';
                echo '<form action="' . $url . '?id=' . $id . '" method="post"><div class="topmenu">' .
                    '<b>' . __('download_sort') . ': </b>' .
                    '<select name="sort_down" style="font-size:x-small">' .
                    '<option value="0"' . (!$_SESSION['sort_down'] ? ' selected="selected"' : '') . '>' . __('download_sort1') . '</option>' .
                    '<option value="1"' . ($_SESSION['sort_down'] ? ' selected="selected"' : '') . '>' . __('download_sort2') . '</option></select> &amp; ' .
                    '<select name="sort_down2" style="font-size:x-small">' .
                    '<option value="0"' . (!$_SESSION['sort_down2'] ? ' selected="selected"' : '') . '>' . __('download_sort3') . '</option>' .
                    '<option value="1"' . ($_SESSION['sort_down2'] ? ' selected="selected"' : '') . '>' . __('download_sort4') . '</option></select>' .
                    '<input type="submit" value="&gt;&gt;" style="font-size:x-small"/></div></form>';
            } else
                $sql_sort = '';
            /*
              -----------------------------------------------------------------
              Постраничная навигация
              -----------------------------------------------------------------
              */
            if ($total_files > App::user()->settings['page_size'])
                echo '<div class="topmenu">' . Functions::displayPagination($url . '?id=' . $id . '&amp;', App::vars()->start, $total_files, App::user()->settings['page_size']) . '</div>';
            /*
              -----------------------------------------------------------------
              Выводи данные
              -----------------------------------------------------------------
              */
            $req_down = App::db()->query("SELECT * FROM `download__files` WHERE `refid` = '" . $id . "' AND `type` < 3 ORDER BY `type` ASC $sql_sort " . App::db()->pagination());
            $i = 0;
            while ($res_down = $req_down->fetch()) {
                echo (($i++ % 2) ? '<div class="list2">' : '<div class="list1">') . Download::displayFile($res_down) . '</div>';
            }
        }
    } else {
        echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
    }
    echo '<div class="phdr">';
    if ($total_cat || !$total_files) echo __('total_dir') . ': ' . $total_cat;
    if ($total_cat && $total_files) echo '&nbsp;|&nbsp;';
    if ($total_files) echo __('total_files') . ': ' . $total_files;
    echo '</div>';
    /*
    -----------------------------------------------------------------
    Постраничная навигация
    -----------------------------------------------------------------
    */
    if ($total_files > App::user()->settings['page_size']) {
        echo '<div class="topmenu">' . Functions::displayPagination($url . '?id=' . $id . '&amp;', App::vars()->start, $total_files, App::user()->settings['page_size']) . '</div>' .
            '<p><form action="' . $url . '" method="get">' .
            '<input type="hidden" name="id" value="' . $id . '"/>' .
            '<input type="text" name="page" size="2"/><input type="submit" value="' . __('to_page') . ' &gt;&gt;"/></form></p>';
    }
    if (App::user()->rights == 4 || App::user()->rights >= 6) {
        /*
        -----------------------------------------------------------------
        Выводим ссылки на модерские функции
        -----------------------------------------------------------------
        */
        echo '<p><div class="func"><form action="' . $url . '?act=redirect" method="post"><select name="admin_act">' .
            '<option value="add_cat">' . __('download_add_cat') . '</option>';
        if ($id) {
            $del_cat = App::db()->query("SELECT COUNT(*) FROM `download__category` WHERE `refid` = '" . $id . "'")->fetchColumn();
            if (!$del_cat) {
                echo '<option value="delete_cat">' . __('download_del_cat') . '</option>';
            }
            echo '<option value="edit_cat">' . __('download_edit_cat') . '</option>' .
                '<option value="import">' . __('download_import') . '</option>' .
                '<option value="down_file">' . __('download_upload_file') . '</option>';
        }
        echo '<option value="scan_dir">' . __('download_scan_dir') . '</option>' .
            '<option value="clean">' . __('download_clean') . '</option>' .
            '<option value="scan_about">' . __('download_scan_about') . '</option>' .
            '<option value="recount">' . __('download_recount') . '</option>' .
            '<input type="hidden" name="admin_id" value="' . $id . '"/>' .
            '</select><input type="submit" value="' . __('do') . '"/></form></div></p>';
    } else if (isset($res_down_cat['field']) && $res_down_cat['field'] && App::user()->id && $id)
        echo '<p><div class="func"><a href="' . $url . '?act=down_file&amp;id=' . $id . '">' . __('download_upload_file') . '</a></div></p>';
    /*
    -----------------------------------------------------------------
    Нижнее меню навигации
    -----------------------------------------------------------------
    */
    echo '<p>';
    if ($id) {
        echo '<a href="' . $url . '">' . __('download_title') . '</a>';
    } else {
        echo ((isset(App::cfg()->sys->acl_downloads_comm) && App::cfg()->sys->acl_downloads_comm) || App::user()->rights >= 7 ? '<a href="' . $url . '?act=review_comments">' . __('review_comments') . '</a><br />' : '') .
            '<a href="' . $url . '?act=bookmark">' . __('download_bookmark') . '</a>';
    }
    echo '</p>';
}