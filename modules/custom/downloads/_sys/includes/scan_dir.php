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
$id = abs(intval(App::request()->getQuery('id', 0)));

/*
-----------------------------------------------------------------
Обновление файлов
-----------------------------------------------------------------
*/
if (App::user()->rights == 4 || App::user()->rights >= 6) {
    set_time_limit(99999);
    $do = isset($_GET['do']) ? trim($_GET['do']) : '';
    $mod = isset($_GET['mod']) ? intval($_GET['mod']) : '';
    switch ($do) {
        case 'clean':
            /*
               -----------------------------------------------------------------
               Удаляем отсутствующие файлы
               -----------------------------------------------------------------
               */
            $query = App::db()->query("SELECT `id`, `dir`, `name`, `type` FROM `" . TP . "download__files`");
            while ($result = $query->fetch()) {
                if (!file_exists($result['dir'] . '/' . $result['name'])) {
                    $req = App::db()->query("SELECT `id` FROM `" . TP . "download__more` WHERE `refid` = '" . $result['id'] . "'");
                    while ($res = $req->fetch()) {
                        @unlink($result['dir'] . '/' . $res['name']);
                    }
                    App::db()->exec("DELETE FROM `" . TP . "download__bookmark` WHERE `file_id`='" . $result['id'] . "'");
                    App::db()->exec("DELETE FROM `" . TP . "download__more` WHERE `refid` = '" . $result['id'] . "'");
                    App::db()->exec("DELETE FROM `" . TP . "download__comments` WHERE `sub_id`='" . $result['id'] . "'");
                    App::db()->exec("DELETE FROM `" . TP . "download__files` WHERE `id` = '" . $result['id'] . "' LIMIT 1");
                }
            }

            $query = App::db()->query("SELECT `id`, `dir`, `name` FROM `" . TP . "download__category`");
            while ($result = $query->fetch()) {
                if (!file_exists($result['dir'])) {
                    $arrayClean = [];
                    $req = App::db()->query("SELECT `id` FROM `" . TP . "download__files` WHERE `refid` = '" . $result['id'] . "'");
                    while ($res = $req->fetch()) {
                        $arrayClean = $res['id'];
                    }
                    $idClean = implode(',', $arrayClean);
                    App::db()->exec("DELETE FROM `" . TP . "download__bookmark` WHERE `file_id` IN (" . $idClean . ")");
                    App::db()->exec("DELETE FROM `" . TP . "download__comments` WHERE `sub_id` IN (" . $idClean . ")");
                    App::db()->exec("DELETE FROM `" . TP . "download__more` WHERE `refid` IN (" . $idClean . ")");
                    App::db()->exec("DELETE FROM `" . TP . "download__files` WHERE `refid` = '" . $result['id'] . "'");
                    App::db()->exec("DELETE FROM `" . TP . "download__category` WHERE `id` = '" . $result['id'] . "'");
                }
            }

            $req_down = App::db()->query("SELECT `dir`, `name`, `id` FROM `" . TP . "download__category`");
            while ($res_down = $req_down->fetch()) {
                $dir_files = App::db()->query("SELECT COUNT(*) FROM `" . TP . "download__files` WHERE `type` = '2' AND `dir` LIKE '" . ($res_down['dir']) . "%'")->fetchColumn();
                App::db()->exec("UPDATE `" . TP . "download__category` SET `total` = '$dir_files' WHERE `id` = '" . $res_down['id'] . "'");
            }

            App::db()->query("OPTIMIZE TABLE `" . TP . "download__bookmark`, `" . TP . "download__files`, `" . TP . "download__comments`,`" . TP . "download__more`");

            echo '<div class="phdr"><b>' . __('scan_dir_clean') . '</b></div>' .
                '<div class="rmenu"><p>' . __('scan_dir_clean_ok') . '</p></div>' .
                '<div class="phdr"><a href="' . $url . '?id=' . $id . '">' . __('back') . '</a></div>';
            break;

        default:
            /*
               -----------------------------------------------------------------
               Обновление файлов
               -----------------------------------------------------------------
               */
            if ($id) {
                $cat = App::db()->query("SELECT `dir`, `name`, `rus_name` FROM `" . TP . "download__category` WHERE	`id` = '" . $id . "' LIMIT 1");
                $res_down_cat = $cat->fetch();
                $scan_dir = $res_down_cat['dir'];
                if (!$cat->rowCount() || !is_dir($scan_dir)) {
                    echo __('not_found_dir') . ' <a href="' . $url . '">' . __('download_title') . '</a>';
                    exit;
                }
            } else {
                $scan_dir = $files_path;
            }
            echo '<div class="phdr"><b>' . __('download_scan_dir') . '</b>' . ($id ? ': ' . htmlspecialchars($res_down_cat['rus_name']) : '') . '</div>';
            if (isset($_GET['yes'])) {
                /*
                    -----------------------------------------------------------------
                    Сканирование папок
                    -----------------------------------------------------------------
                    */
                $array_dowm = [];
                $array_id = [];
                $array_more = [];

                $query = App::db()->query("SELECT `dir`, `name`, `id` FROM `" . TP . "download__files`");
                while ($result = $query->fetch()) {
                    $array_dowm[] = $result['dir'] . '/' . $result['name'];
                    $array_id[$result['dir'] . '/' . $result['name']] = $result['id'];
                }

                $queryCat = App::db()->query("SELECT `dir`, `id` FROM `" . TP . "download__category`");
                while ($resultCat = $queryCat->fetch()) {
                    $array_dowm[] = $resultCat['dir'];
                    $array_id[$resultCat['dir']] = $resultCat['id'];
                }

                $query_more = App::db()->query("SELECT `name` FROM `" . TP . "download__more`");
                while ($result_more = $query_more->fetch()) {
                    $array_more[] = $result_more['name'];
                }

                $array_scan = [];
                function scan_dir($dir = '')
                {
                    static $array_scan;
                    global $mod;
                    $arr_dir = glob($dir . '/*');

                    foreach ($arr_dir as $val) {
                        if (is_dir($val)) {
                            $array_scan[] = $val;
                            if (!$mod)
                                scan_dir($val);
                        } else {
                            $file_name = basename($val);
                            if ($file_name != '.' && $file_name != '..' && $file_name != 'index.php' && $file_name != '.htaccess' && $file_name != '.svn')
                                $array_scan[] = $val;
                        }
                    }

                    return $array_scan;
                }

                $i = 0;
                $i_two = 0;
                $i_three = 0;
                $arr_scan_dir = scan_dir($scan_dir);
                if ($arr_scan_dir) {
                    $stmt_c = App::db()->prepare("
                        INSERT INTO `" . TP . "download__category`
                        (`refid`, `dir`, `sort`, `name`, `field`, `rus_name`, `text`, `desc`)
                        VALUES (?, ?, ?, ?, 0, ?, '', '')
                    ");

                    $stmt_m = App::db()->prepare("
                        INSERT INTO `" . TP . "download__more`
                        (`refid`, `time`, `name`, `rus_name`, `size`)
                        VALUES (?, ?, ?, ?, ?)
                    ");

                    $stmt_f = App::db()->prepare("
                        INSERT INTO `" . TP . "download__files`
                        (`refid`, `dir`, `time`, `name`, `text`, `rus_name`, `type`, `user_id`)
                        VALUES (?, ?, ?, ?, 'Download', ?, 2, ?)
                    ");

                    foreach ($arr_scan_dir as $val) {
                        if (!in_array($val, $array_dowm)) {
                            if (is_dir($val)) {
                                $name = basename($val);
                                $dir = dirname($val);
                                $refid = isset($array_id[$dir]) ? (int)$array_id[$dir] : 0;
                                $sort = isset($sort) ? ($sort + 1) : time();

                                $stmt_c->execute([
                                    $refid,
                                    $dir . "/" . $name,
                                    $sort,
                                    $name,
                                    $name
                                ]);

                                $array_id[$dir . "/" . $name] = App::db()->lastInsertId();

                                ++$i;
                            } else {
                                $name = basename($val);
                                if (preg_match("/^file([0-9]+)_/", $name)) {
                                    if (!in_array($name, $array_more)) {
                                        $refid = (int)str_replace('file', '', $name);
                                        $name_link = htmlspecialchars(mb_substr(str_replace('file' . $refid . '_', __('download') . ' ', $name), 0, 200));
                                        $size = filesize($val);

                                        $stmt_m->execute([
                                            $refid,
                                            time(),
                                            $name,
                                            $name_link,
                                            $size
                                        ]);

                                        ++$i_two;
                                    }
                                } else {
                                    $isFile = App::vars()->start ? is_file($val) : true;
                                    if ($isFile) {
                                        $dir = dirname($val);
                                        $refid = (int)$array_id[$dir];

                                        $stmt_f->execute([
                                            $refid,
                                            $dir,
                                            time(),
                                            $name,
                                            $name,
                                            App::user()->id
                                        ]);

                                        if (App::vars()->start) {
                                            $fileId = App::db()->lastInsertId();
                                            $screenFile = false;
                                            if (is_file($val . '.jpg')) $screenFile = $val . '.jpg';
                                            elseif (is_file($val . '.gif')) $screenFile = $val . '.gif';
                                            elseif (is_file($val . '.png')) $screenFile = $val . '.png';
                                            if ($screenFile) {
                                                $is_dir = mkdir($screens_path . '/' . $fileId, 0777);
                                                if ($is_dir == true) @chmod($screens_path . '/' . $fileId, 0777);
                                                @copy($screenFile, $screens_path . '/' . $fileId . '/' . str_replace($val, $fileId, $screenFile));
                                                unlink($screenFile);
                                            }
                                            if (is_file($val . '.txt')) {
                                                @copy($val . '.txt', $down_path . '/about/' . $fileId . '.txt');
                                                unlink($val . '.txt');
                                            }
                                        }
                                        ++$i_three;
                                    }
                                }
                            }
                        }
                    }

                    $stmt_c = null;
                    $stmt_m = null;
                    $stmt_f = null;
                }
                if ($id) {
                    $dir_files = App::db()->query("SELECT COUNT(*) FROM `" . TP . "download__files` WHERE `type` = '2' AND `dir` LIKE '" . ($res_down_cat['dir'] . '/' . $res_down_cat['name']) . "%'")->fetchColumn();
                    App::db()->exec("UPDATE `" . TP . "download__files` SET `total` = '$dir_files' WHERE `id` = '" . $id . "'");
                } else {
                    $req_down = App::db()->query("SELECT `dir`, `name`, `id` FROM `" . TP . "download__files` WHERE `type` = 1");
                    while ($res_down = $req_down->fetch()) {
                        $dir_files = App::db()->query("SELECT COUNT(*) FROM `" . TP . "download__files` WHERE `type` = '2' AND `dir` LIKE '" . ($res_down['dir'] . '/' . $res_down['name']) . "%'")->fetchColumn();
                        App::db()->exec("UPDATE `" . TP . "download__files` SET `total` = '$dir_files' WHERE `id` = '" . $res_down['id'] . "'");
                    }
                }
                echo '<div class="menu"><b>' . __('scan_dir_add') . ':</b><br />' .
                    __('scan_dir_add_cat') . ': ' . $i . '<br />' .
                    __('scan_dir_add_files') . ': ' . $i_three . '<br />' .
                    __('scan_dir_add_files_more') . ': ' . $i_two . '</div>';
                if (App::vars()->start) echo '<div class="gmenu"><a href="' . $url . '?act=scan_about&amp;id=' . $id . '">' . __('download_scan_about') . '</div>';
                echo '<div class="rmenu">' .
                    '<a href="' . $url . '?act=scan_dir&amp;do=clean&amp;id=' . $id . '">' . __('scan_dir_clean') . '</a><br />' .
                    '<a href="' . $url . '?act=recount&amp;do=clean&amp;id=' . $id . '">' . __('download_recount') . '</a></div>';
            } else {
                /*
                    -----------------------------------------------------------------
                    Выбор режима обновление
                    -----------------------------------------------------------------
                    */
                echo '<div class="menu"><b><a href="' . $url . '?act=scan_dir&amp;yes&amp;id=' . $id . '">' . ($id ? __('download_scan_dir2') : __('download_scan_dir4')) . '</a></b>' .
                    ($id ? '<br /><a href="' . $url . '?act=scan_dir&amp;yes&amp;id=' . $id . '&amp;mod=1">' . __('download_scan_dir3') . '</a>' : '') . '</div>';
                if ($id){
                    echo '<div class="rmenu"><a href="' . $url . '?act=scan_dir&amp;yes">' . __('download_scan_dir4') . '</a></div>';
                }
                echo '<div class="phdr"><b>' . __('scan_dir_v2') . '</b> beta</div>' .
                    '<div class="topmenu">' . __('scan_dir_about') . '</div><div class="menu">' .
                    '<a href="' . $url . '?act=scan_dir&amp;yes&amp;id=' . $id . '&amp;start=1"><b>' . ($id ? __('download_scan_dir2') : __('download_scan_dir4')) . '</b></a> ' .
                    ($id ? '<br /><a href="' . $url . '?act=scan_dir&amp;yes&amp;id=' . $id . '&amp;mod=1&amp;start=1">' . __('download_scan_dir3') . '</a>' : '') .
                    '<div class="sub"><small>' . __('scan_dir_v2_faq') . '</small></div>' .
                    '</div><div class="rmenu">';
                if ($id)
                    echo ' <a href="' . $url . '?act=scan_dir&amp;yes&amp;start=1">' . __('download_scan_dir4') . '</a><br />';
                echo '<a href="' . $url . '?act=scan_dir&amp;do=clean&amp;id=' . $id . '">' . __('scan_dir_clean') . '</a></div>';
            }
            echo '<div class="phdr"><a href="' . $url . '?id=' . $id . '">' . __('back') . '</a></div>';
    }
} else {
    header('Location: ' . App::cfg()->sys->homeurl . '404');
}