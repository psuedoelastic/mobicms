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

$id = abs(intval(App::request()->getQuery('id', 0)));

if (!$id) {
    echo __('error_wrong_data') . ' <a href="index.php?act=forum">' . __('forum_management') . '</a>';
    exit;
}
$req = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = " . $id . " AND (`type` = 'f' OR `type` = 'r')");
if ($req->rowCount()) {
    $res = $req->fetch();
    echo '<div class="phdr"><b>' . ($res['type'] == 'r' ? __('delete_section') : __('delete_catrgory')) . ':</b> ' . $res['text'] . '</div>';
    // Проверяем, есть ли подчиненная информация
    $total = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `refid` = " . $id . " AND (`type` = 'f' OR `type` = 'r' OR `type` = 't')")->fetchColumn();
    if ($total) {
        if ($res['type'] == 'f') {
            // Удаление категории с подчиненными данными
            if (isset($_POST['submit'])) {
                $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
                if (!$category || $category == $id) {
                    echo __('error_wrong_data');
                    exit;
                }
                $check = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `id` = '$category' AND `type` = 'f'")->fetchColumn();
                if (!$check) {
                    echo __('error_wrong_data');
                    exit;
                }
                // Вычисляем правила сортировки и перемещаем разделы
                $sort = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `refid` = '$category' AND `type` ='r' ORDER BY `realid` DESC")->fetch();
                $sortnum = !empty($sort['realid']) && $sort['realid'] > 0 ? $sort['realid'] + 1 : 1;
                $req_c = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `refid` = " . $id . " AND `type` = 'r'");
                while ($res_c = $req_c->fetch()) {
                    App::db()->exec("UPDATE `" . TP . "forum__` SET `refid` = '" . $category . "', `realid` = '$sortnum' WHERE `id` = '" . $res_c['id'] . "'");
                    ++$sortnum;
                }
                // Перемещаем файлы в выбранную категорию
                App::db()->exec("UPDATE `" . TP . "forum__files` SET `cat` = '" . $category . "' WHERE `cat` = '" . $res['refid'] . "'");
                App::db()->exec("DELETE FROM `" . TP . "forum__` WHERE `id` = " . $id);
                echo '<div class="rmenu"><p><h3>' . __('category_deleted') . '</h3>' . __('contents_moved_to') . ' <a href="../forum/index.php?id=' . $category . '">' . __('selected_category') . '</a></p></div>';
            } else {
                echo '<form action="index.php?act=forum&amp;mod=del&amp;id=' . $id . '" method="POST">' .
                    '<div class="rmenu"><p>' . __('contents_move_warning') . '</p>' .
                    '<p><h3>' . __('select_category') . '</h3><select name="category" size="1">';
                $req_c = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `type` = 'f' AND `id` != " . $id . " ORDER BY `realid` ASC");
                while ($res_c = $req_c->fetch()) {
                    echo '<option value="' . $res_c['id'] . '">' . $res_c['text'] . '</option>';
                }
                echo '</select><br /><small>' . __('contents_move_description') . '</small></p>' .
                    '<p><input type="submit" name="submit" value="' . __('move') . '" /></p></div>';
                if (App::user()->rights == 9) {
                    // Для супервайзоров запрос на полное удаление
                    echo '<div class="rmenu"><p><h3>' . __('delete_full') . '</h3>' . __('delete_full_note') . ' <a href="index.php?act=forum&amp;mod=cat&amp;id=' . $id . '">' . __('child_section') . '</a></p>' .
                        '</div>';
                }
                echo '</form>';
            }
        } else {
            // Удаление раздела с подчиненными данными
            if (isset($_POST['submit'])) {
                // Предварительные проверки
                $subcat = isset($_POST['subcat']) ? intval($_POST['subcat']) : 0;
                if (!$subcat || $subcat == $id) {
                    echo __('error_wrong_data') . ' <a href="index.php?act=forum">' . __('forum_management') . '</a>';
                    exit;
                }
                $check = App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `id` = '$subcat' AND `type` = 'r'")->fetchColumn();
                if (!$check) {
                    echo __('error_wrong_data') . ' <a href="index.php?act=forum">' . __('forum_management') . '</a>';
                    exit;
                }
                App::db()->exec("UPDATE `" . TP . "forum__` SET `refid` = '$subcat' WHERE `refid` = " . $id);
                App::db()->exec("UPDATE `" . TP . "forum__files` SET `subcat` = '$subcat' WHERE `subcat` = " . $id);
                App::db()->exec("DELETE FROM `" . TP . "forum__` WHERE `id` = " . $id);
                echo '<div class="rmenu"><p><h3>' . __('section_deleted') . '</h3>' . __('themes_moved_to') . ' <a href="../forum/index.php?id=' . $subcat . '">' . __('selected_section') . '</a>.' .
                    '</p></div>';
            } elseif (isset($_POST['delete'])) {
                if (App::user()->rights != 9) {
                    echo __('access_forbidden');
                    exit;
                }
                // Удаляем файлы
                $req_f = App::db()->query("SELECT * FROM `" . TP . "forum__files` WHERE `subcat` = " . $id);
                while ($res_f = $req_f->fetch()) {
                    unlink(ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'forum' . DIRECTORY_SEPARATOR . $res_f['filename']);
                }
                App::db()->exec("DELETE FROM `" . TP . "forum__files` WHERE `subcat` = " . $id);
                // Удаляем посты, голосования и метки прочтений
                $req_t = App::db()->query("SELECT `id` FROM `" . TP . "forum__` WHERE `refid` = " . $id . " AND `type` = 't'");
                while ($res_t = $req_t->fetch()) {
                    App::db()->exec("DELETE FROM `" . TP . "forum__` WHERE `refid` = '" . $res_t['id'] . "'");
                    App::db()->exec("DELETE FROM `" . TP . "forum__vote` WHERE `topic` = '" . $res_t['id'] . "'");
                    App::db()->exec("DELETE FROM `" . TP . "forum__vote_users` WHERE `topic` = '" . $res_t['id'] . "'");
                    App::db()->exec("DELETE FROM `" . TP . "forum__rdm` WHERE `topic_id` = '" . $res_t['id'] . "'");
                }
                // Удаляем темы
                App::db()->exec("DELETE FROM `" . TP . "forum__` WHERE `refid` = " . $id);
                // Удаляем раздел
                App::db()->exec("DELETE FROM `" . TP . "forum__` WHERE `id` = " . $id);
                // Оптимизируем таблицы
                App::db()->query("OPTIMIZE TABLE `" . TP . "forum__files` , `" . TP . "forum__rdm` , `" . TP . "forum__` , `" . TP . "forum__vote` , `" . TP . "forum__vote_users`");
                echo '<div class="rmenu"><p>' . __('section_themes_deleted') . '<br />' .
                    '<a href="index.php?act=forum&amp;mod=cat&amp;id=' . $res['refid'] . '">' . __('to_category') . '</a></p></div>';
            } else {
                echo '<form action="index.php?act=forum&amp;mod=del&amp;id=' . $id . '" method="POST"><div class="rmenu">' .
                    '<p>' . __('section_move_warning') . '</p>' . '<p><h3>' . __('select_section') . '</h3>';
                $cat = isset($_GET['cat']) ? abs(intval($_GET['cat'])) : 0;
                $ref = $cat ? $cat : $res['refid'];
                $req_r = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `refid` = '$ref' AND `id` != " . $id . " AND `type` = 'r' ORDER BY `realid` ASC");
                while ($res_r = $req_r->fetch()) {
                    echo '<input type="radio" name="subcat" value="' . $res_r['id'] . '" />&#160;' . $res_r['text'] . '<br />';
                }
                echo '</p><p><h3>' . __('another_category') . '</h3><ul>';
                $req_c = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `type` = 'f' AND `id` != '$ref' ORDER BY `realid` ASC");
                while ($res_c = $req_c->fetch()) {
                    echo '<li><a href="index.php?act=forum&amp;mod=del&amp;id=' . $id . '&amp;cat=' . $res_c['id'] . '">' . $res_c['text'] . '</a></li>';
                }
                echo '</ul><small>' . __('section_move_description') . '</small></p>' .
                    '<p><input type="submit" name="submit" value="' . __('move') . '" /></p></div>';
                if (App::user()->rights == 9) {
                    // Для супервайзоров запрос на полное удаление
                    echo '<div class="rmenu"><p><h3>' . __('delete_full') . '</h3>' . __('delete_full_warning');
                    echo '</p><p><input type="submit" name="delete" value="' . __('delete') . '" /></p></div>';
                }
                echo '</form>';
            }
        }
    } else {
        // Удаление пустого раздела, или категории
        if (isset($_POST['submit'])) {
            App::db()->exec("DELETE FROM `" . TP . "forum__` WHERE `id` = " . $id);
            echo '<div class="rmenu"><p>' . ($res['type'] == 'r' ? __('section_deleted') : __('category_deleted')) . '</p></div>';
        } else {
            echo '<div class="rmenu"><p>' . __('delete_confirmation') . '</p>' .
                '<p><form action="index.php?act=forum&amp;mod=del&amp;id=' . $id . '" method="POST">' .
                '<input type="submit" name="submit" value="' . __('delete') . '" />' .
                '</form></p></div>';
        }
    }
    echo '<div class="phdr"><a href="index.php?act=forum&amp;mod=cat">' . __('back') . '</a></div>';
} else {
    header('Location: index.php?act=forum&mod=cat');
}