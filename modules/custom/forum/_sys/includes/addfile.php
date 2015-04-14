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

if (!$id || !App::user()->id) {
    echo __('error_wrong_data');
    exit;
}
// Проверяем, тот ли юзер заливает файл
$req = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = " . $id);
$res = $req->fetch();
if ($res['user_id'] != App::user()->id) {
    echo __('error_wrong_data');
    exit;
}

$req1 = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__files` WHERE `post` = " . $id);
if ($req1->fetchColumn() > 0) {
    echo __('error_file_uploaded');
    exit;
}

// Вычисляем страницу для перехода
$page = ceil(App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">=" : "<=") . " '" . $res['id'] . "'")->fetchColumn() / App::user()->settings['page_size']);

switch ($res['type']) {
    case 'm':
        if (isset($_POST['submit'])) {
            /*
            -----------------------------------------------------------------
            Проверка, был ли выгружен файл и с какого браузера
            -----------------------------------------------------------------
            */
            $do_file = false;
            if ($_FILES['fail']['size'] > 0) {
                // Проверка загрузки с обычного браузера
                $do_file = true;
                $fname = strtolower($_FILES['fail']['name']);
                $fsize = $_FILES['fail']['size'];
            }
            /*
            -----------------------------------------------------------------
            Обработка файла (если есть), проверка на ошибки
            -----------------------------------------------------------------
            */
            if ($do_file) {
                // Список допустимых расширений файлов.
                $al_ext = array_merge($ext_win, $ext_java, $ext_sis, $ext_doc, $ext_pic, $ext_arch, $ext_video, $ext_audio, $ext_other);
                $ext = explode(".", $fname);
                $error = [];
                // Проверка на допустимый размер файла
                if ($fsize > 1024 * App::cfg()->sys->filesize)
                    $error[] = __('error_file_size') . ' ' . App::cfg()->sys->filesize . 'kb.';
                // Проверка файла на наличие только одного расширения
                if (count($ext) != 2)
                    $error[] = __('error_file_name');
                // Проверка допустимых расширений файлов
                if (!in_array($ext[1], $al_ext))
                    $error[] = __('error_file_ext') . ':<br />' . implode(', ', $al_ext);
                // Проверка на длину имени
                if (strlen($fname) > 30)
                    $error[] = __('error_file_name_size');
                // Проверка на запрещенные символы
                if (preg_match("/[^\da-z_\-.]+/", $fname))
                    $error[] = __('error_file_symbols');
                // Проверка наличия файла с таким же именем
                if (file_exists(ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'forum' . DIRECTORY_SEPARATOR . $fname)) {
                    $fname = time() . $fname;
                }
                // Окончательная обработка
                if (!$error && $do_file) {
                    // Для обычного браузера
                    if ((move_uploaded_file($_FILES["fail"]["tmp_name"], ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'forum' . DIRECTORY_SEPARATOR . $fname)) == true) {
                        @chmod("$fname", 0777);
                        @chmod(ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'forum' . DIRECTORY_SEPARATOR . $fname, 0777);
                        echo __('file_uploaded') . '<br/>';
                    } else {
                        $error[] = __('error_upload_error');
                    }
                }
                if (!$error) {
                    // Определяем тип файла
                    $ext = strtolower($ext[1]);
                    if (in_array($ext, $ext_win)) {
                        $type = 1;
                    } elseif (in_array($ext, $ext_java)) {
                        $type = 2;
                    } elseif (in_array($ext, $ext_sis)) {
                        $type = 3;
                    } elseif (in_array($ext, $ext_doc)) {
                        $type = 4;
                    } elseif (in_array($ext, $ext_pic)) {
                        $type = 5;
                    } elseif (in_array($ext, $ext_arch)) {
                        $type = 6;
                    } elseif (in_array($ext, $ext_video)) {
                        $type = 7;
                    } elseif (in_array($ext, $ext_audio)) {
                        $type = 8;
                    } else {
                        $type = 9;
                    }

                    // Определяем ID субкатегории и категории
                    $req2 = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = '" . $res['refid'] . "'");
                    $res2 = $req2->fetch();
                    $req3 = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = '" . $res2['refid'] . "'");
                    $res3 = $req3->fetch();

                    // Заносим данные в базу
                    $stmt = App::db()->prepare("
                        INSERT INTO `" . TP . "forum__files`
                        (`cat`, `subcat`, `topic`, `post`, `time`, `filename`, `filetype`)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");

                    $stmt->execute([
                        $res3['refid'],
                        $res2['refid'],
                        $res['refid'],
                        $id,
                        $res['time'],
                        $fname,
                        $type
                    ]);
                    $stmt = null;
                } else {
                    echo $error . ' <a href="' . $url . '?act=addfile&amp;id=' . $id . '">' . __('repeat') . '</a>';
                }
            } else {
                echo __('error_upload_error') . '<br />';
            }
            echo '<br/><a href="' . $url . '?id=' . $res['refid'] . '&amp;page=' . $page . '">' . __('continue') . '</a><br/>';
        } else {
            /*
            -----------------------------------------------------------------
            Форма выбора файла для выгрузки
            -----------------------------------------------------------------
            */
            echo '<div class="phdr"><b>' . __('add_file') . '</b></div>' .
                '<div class="gmenu"><form action="' . $url . '?act=addfile&amp;id=' . $id . '" method="post" enctype="multipart/form-data"><p>';
            if (stristr(App::network()->getUserAgent(), 'Opera/8.01')) {
                echo '<input name="fail1" value =""/>&#160;<br/><a href="op:fileselect">' . __('select_file') . '</a>';
            } else {
                echo '<input type="file" name="fail"/>';
            }
            echo '</p><p>' .
                '<input type="submit" name="submit" value="' . __('upload') . '"/>' .
                '</p><p><a href="' . $url . '?id=' . $res['refid'] . '&amp;page=' . $page . '">' . __('cancel') . '</a></p></form></div>' .
                '<div class="phdr">' . __('max_size') . ': ' . App::cfg()->sys->filesize . 'kb.</div>';
        }
        break;

    default:
        echo __('error_wrong_data');
}
