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
/*
-----------------------------------------------------------------
Обновление описаний
-----------------------------------------------------------------
*/
if (App::user()->rights == 4 || App::user()->rights >= 6) {
    set_time_limit(99999);
    $dir = glob($down_path . '/about/*.txt');
    foreach ($dir as $val) {
        if (isset($_GET['clean'])) {
            @unlink($val);
        } else {
            $file_id = abs(intval(preg_replace('#' . $down_path . '/about/([0-9]+)\.txt#si', '\1', $val, 1)));
            if ($file_id) {
                $stmt = App::db()->prepare("
                    UPDATE `download__files`
                    SET `about` = ?
                    WHERE `id` = ?
                ");

                $stmt->execute([file_get_contents($val), $file_id]);
                $stmt = null;
            }
        }
    }

    App::db()->query("OPTIMIZE TABLE `download__files`");
    echo '<div class="phdr"><b>' . __('download_scan_about') . '</b></div>';
    if (isset($_GET['clean'])) {
        echo '<div class="rmenu"><p>' . __('scan_about_clean_ok') . '</p></div>';
    } else {
        echo '<div class="gmenu"><p>' . __('scan_about_ok') . '</p></div>' .
            '<div class="rmenu"><a href="' . App::router()->getUri(1) . '?act=scan_about&amp;clean&amp;id=' . App::request()->getQuery('id', '') . '">' . __('scan_about_clean') . '</a></div>';
    }
    echo '<div class="phdr"><a href="' . App::router()->getUri(1) . '?id=' . App::request()->getQuery('id', '') . '">' . __('back') . '</a></div>';
} else {
    header('Location: ' . App::cfg()->sys->homeurl . '404');
}