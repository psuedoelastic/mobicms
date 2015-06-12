<?php

/**
 * @package     mobiCMS
 * @link        http://mobicms.net
 * @copyright   Copyright (C) 2008-2011 mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://mobicms.net/about
 */

defined('MOBICMS') or die('Error: restricted access');
$url = App::router()->getUri(1);

/*
-----------------------------------------------------------------
Открытие ZIP прхива
-----------------------------------------------------------------
*/
$dir_clean = opendir(ROOT_PATH . 'files/download/temp/open_zip');
while ($file = readdir($dir_clean)) {
    if ($file != 'index.php' && $file != '.htaccess' && $file != '.' && $file != '..' && $file != '.svn') {
        $time_file = filemtime(ROOT_PATH . 'files/download/temp/open_zip/' . $file);
        if ($time_file < (time() - 300)) @unlink(ROOT_PATH . 'files/download/temp/open_zip/' . $file);
    }
}
closedir($dir_clean);
$req_down = App::db()->query("SELECT * FROM `download__files` WHERE `id` = '" . App::vars()->id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();
if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name']) || ($res_down['type'] == 3 && App::user()->rights < 6 && App::user()->rights != 4)) {
    echo __('not_found_file') . '<a href="' . $url . '">' . __('download_title') . '</a>';
    exit;
}
if (isset($_GET['more'])) {
    $more = abs(intval($_GET['more']));
    $req_more = App::db()->query("SELECT * FROM `download__more` WHERE `id` = '$more' AND `refid`= '" . App::vars()->id . "' LIMIT 1");
    $res_more = $req_more->fetch();
    if (!$req_more->rowCount() || !is_file($res_down['dir'] . '/' . $res_more['name'])) {
        echo __('not_found_file') . '<a href="' . $url . '">' . __('download_title') . '</a>';
        exit;
    }
    $file_open = $res_down['dir'] . '/' . $res_more['name'];
    $isset_more = '&amp;more=' . $more;
    $title_pages = $res_more['rus_name'];
} else {
    $file_open = $res_down['dir'] . '/' . $res_down['name'];
    $title_pages = $res_down['rus_name'];
    $isset_more = '';
}
$title_pages = htmlspecialchars(mb_substr($title_pages, 0, 20));
$textl = __('open_archive') . ' &raquo; ' . (mb_strlen($res_down['rus_name']) > 20 ? $title_pages . '...' : $title_pages);
require(SYSPATH . 'lib/pclzip.lib.php');
$array = ['cgi', 'pl', 'asp', 'aspx', 'shtml', 'shtm', 'fcgi', 'fpl', 'jsp', 'py', 'htaccess', 'ini', 'php', 'php3', 'php4', 'php5', 'php6', 'phtml', 'phps'];
if (!isset($_GET['file'])) {
    /*
	-----------------------------------------------------------------
	Открываем архив
	-----------------------------------------------------------------
	*/
    $zip = new PclZip($file_open);
    if (($list = $zip->listContent()) == 0) {
        echo __('open_archive_error') . '<p><a href="' . $url . '?act=view&amp;id=' . App::vars()->id . '">' . __('back') . '</a></p>';
        exit;
    }
    $list_size = false;
    $list_content = false;
    $save_list = false;
    for ($i = 0; $i < sizeof($list); $i++) {
        for (reset($list[$i]); $key = key($list[$i]); next($list[$i])) {
            $file_size = str_replace("--size:", "", strstr($list_content, "--size"));
            $list_size .= str_replace($file_size, $file_size . '|', $file_size);
            $list_content = "[$i]--$key:" . $list[$i][$key];
            $zip_file = str_replace("--filename:", "", strstr($list_content, "--filename"));
            $save_list .= str_replace($zip_file, $zip_file . '|', $zip_file);
        }
    }
    $file_size_two = explode('|', $list_size);
    /*
	-----------------------------------------------------------------
	Выводим список файлов
	-----------------------------------------------------------------
	*/
    echo '<div class="phdr"><b>' . __('open_archive') . ':</b> ' . htmlspecialchars($res_down['name']) . '</div>' .
        '<div class="topmenu">' . __('open_archive_faq') . '</div>';
    $preview = explode('|', $save_list);
    $total = count($preview) - 1;
    /*
	-----------------------------------------------------------------
	Навигация
	-----------------------------------------------------------------
	*/
    if ($total > App::user()->settings['page_size'])
        echo '<div class="topmenu">' . Functions::displayPagination($url . '?id=' . App::vars()->id . '&amp;act=open_zip' . $isset_more . '&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
    if ($total > 0) {
        $end = App::vars()->start + App::user()->settings['page_size'];
        if ($end > $total) $end = $total;
        for ($i = App::vars()->start; $i < $end; $i++) {
            $path = $preview[$i];
            $file_name = preg_replace("#.*[\\/]#si", '', $path);
            $dir = preg_replace("#[\\/]?[^\\/]*$#si", '', $path);
            $format = explode('.', $file_name);
            $format_file = strtolower($format[count($format) - 1]);
            echo (($i % 2) ? '<div class="list2">' : '<div class="list1">') .
                '<b>' . ($i + 1) . ')</b> ' . $dir . '/' . htmlspecialchars(mb_convert_encoding($file_name, "UTF-8", "Windows-1251"));
            if ($file_size_two[$i] > 0) echo ' (' . Download::displayFileSize($file_size_two[$i]) . ')';
            if ($format_file)
                echo ' - <a href="' . $url . '?act=open_zip&amp;id=' . App::vars()->id . '&amp;file=' . rawurlencode(mb_convert_encoding($path, "UTF-8", "Windows-1251")) . '&amp;page=' . App::vars()->page . $isset_more . '">' . (in_array($format_file, $array) ? __('open_archive_code') : __('download')) . '</a>';
            echo '</div>';
        }
    } else {
        echo '<div class="rmenu"><p>' . __('list_empty') . '</p></div>';
    }
    echo '<div class="gmenu">' . __('open_archive_size') . ': ' . Download::displayFileSize(array_sum($file_size_two)) . '</div>' .
        '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
    /*
	-----------------------------------------------------------------
	Навигация
	-----------------------------------------------------------------
	*/
    if ($total > App::user()->settings['page_size']) {
        echo '<div class="topmenu">' . Functions::displayPagination($url . '?id=' . App::vars()->id . '&amp;act=open_zip' . $isset_more . '&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
            '<p><form action="' . $url . '" method="get">' .
            '<input type="hidden" value="open_zip" name="act" />' .
            '<input type="hidden" value="' . App::vars()->id . '" name="id" />' .
            (isset($more) ? '<input type="hidden" value="' . $more . '" name="more" />' : '') .
            '<input type="text" name="page" size="2"/><input type="submit" value="' . __('to_page') . ' &gt;&gt;"/></form></p>';
    }
    echo '<p>';
} else {
    /*
	-----------------------------------------------------------------
	Просмотр и скачка файла
	-----------------------------------------------------------------
	*/
    $FileName = rawurldecode(trim($_GET['file']));
    $format = explode('.', $FileName);
    $format_file = strtolower($format[count($format) - 1]);
    if (strpos($FileName, '..') !== false or strpos($FileName, './') !== false) {
        echo __('not_found_file') . '<p><a href="' . $url . '?act=open_zip&amp;id=' . App::vars()->id . $isset_more . '">' . __('back') . '</a></p>';
        exit;
    }
    $FileName = htmlspecialchars(trim($FileName), ENT_QUOTES, 'UTF-8');
    $FileName = strtr($FileName, ['&' => '', '$' => '', '>' => '', '<' => '', '~' => '', '`' => '', '#' => '', '*' => '']);
    $zip = new PclZip($file_open);
    $content = $zip->extract(PCLZIP_OPT_BY_NAME, mb_convert_encoding($FileName, "Windows-1251", "UTF-8"), PCLZIP_OPT_EXTRACT_AS_STRING);
    $content = isset($content[0]['content']) ? $content[0]['content'] : '';
    $FileName = preg_replace("#.*[\\/]#si", "", $FileName);
    if (in_array($format_file, $array)) {
        if (!$content) {
            echo __('not_found_file') . ' <a href="' . $url . '">' . __('download_title') . '</a>';
            exit;
        }
        /*
        -----------------------------------------------------------------
        Просмотр кода файла
        -----------------------------------------------------------------
        */
        $UTF = false;
        $content_two = explode("\r\n", $content);
        echo '<div class="phdr"><b>' . htmlspecialchars($FileName, ENT_QUOTES, 'UTF-8') . '</b></div><div class="list1"><div class="phpcode">';
        $rus_simvol = ['а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'];
        for ($i = 0; $i < 66; $i++) {
            if (strstr($content, $rus_simvol[$i]) !== false) $UTF = 1;
        }
        $php_code = trim($content);
        $php_code = substr($php_code, 0, 2) != "<?" ? "<?php\n" . $php_code . "\n?>" : $php_code;
        echo $UTF ? highlight_string($php_code, true) : highlight_string(iconv('windows-1251', 'utf-8', $php_code), true);
        echo '</div></div><div class="phdr">' . __('total') . ': ' . count($content_two) . '</div>';
    } else {
        /*
        -----------------------------------------------------------------
        Скачка файла
        -----------------------------------------------------------------
        */
        $NewNameFile = strtr(Download::translateFileName(mb_strtolower($FileName)), [' ' => '_', '@' => '', '%' => '']);
        if (file_exists(ROOT_PATH . 'files/download/temp/open_zip/' . $NewNameFile)) {
            header('Location: ' . App::cfg()->sys->homeurl . 'files/download/temp/open_zip/' . $NewNameFile);
            exit;
        }
        $dir = @fopen(ROOT_PATH . 'files/download/temp/open_zip/' . $NewNameFile, "wb");
        if ($dir) {
            if (flock($dir, LOCK_EX)) {
                fwrite($dir, $content);
                flock($dir, LOCK_UN);
            }
            fclose($dir);
            header('Location: ' . App::cfg()->sys->homeurl . 'files/download/temp/open_zip/' . $NewNameFile);
            exit;
        } else  echo __('error_file_save');
    }
    echo '<p><a href="' . $url . '?act=open_zip&amp;id=' . App::vars()->id . '&amp;page=' . App::vars()->page . $isset_more . '">' . __('back') . '</a><br />';
}
echo '<p><a href="' . $url . '?act=view&amp;id=' . App::vars()->id . '">' . __('download_title') . '</a></p>';