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

$uri = App::router()->getUri(2);

$req = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `id` = " . $id);
if ($req->rowCount()) {
    $res = $req->fetch();
    if ($res['type'] == 'f' || $res['type'] == 'r') {
        if (isset($_POST['submit'])) {
            // Принимаем данные
            $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
            $desc = isset($_POST['desc']) ? htmlspecialchars($_POST['desc']) : '';
            $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
            // проверяем на ошибки
            $error = [];
            if ($res['type'] == 'r' && !$category)
                $error[] = __('error_category_select');
            elseif ($res['type'] == 'r' && !App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `id` = '$category' AND `type` = 'f'")->fetchColumn())
                $error[] = __('error_category_select');
            if (!$name)
                $error[] = __('error_empty_title');
            if ($name && (mb_strlen($name) < 2 || mb_strlen($name) > 30))
                $error[] = __('title') . ': ' . __('error_wrong_lenght');
            if ($desc && mb_strlen($desc) < 2)
                $error[] = __('error_description_lenght');
            if (!$error) {
                // Записываем в базу
                $stmt = App::db()->prepare("
                            UPDATE `" . TP . "forum__` SET
                            `text`     = :text,
                            `soft`     = :soft
                            WHERE `id` = :id
                        ");

                $stmt->bindParam(':text', $name);
                $stmt->bindParam(':soft', $desc);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                $stmt = null;

                if ($res['type'] == 'r' && $category != $res['refid']) {
                    // Вычисляем сортировку
                    $res_s = App::db()->query("SELECT `realid` FROM `" . TP . "forum__` WHERE `refid` = '$category' AND `type` = 'r' ORDER BY `realid` DESC LIMIT 1")->fetch();
                    $sort = $res_s['realid'] + 1;
                    // Меняем категорию
                    App::db()->exec("UPDATE `" . TP . "forum__` SET `refid` = '$category', `realid` = '$sort' WHERE `id` = " . $id);
                    // Меняем категорию для прикрепленных файлов
                    App::db()->exec("UPDATE `" . TP . "forum__files` SET `cat` = '$category' WHERE `cat` = '" . $res['refid'] . "'");
                }
                header('Location: ' . $uri . 'cat/' . ($res['type'] == 'r' ? '?id=' . $res['refid'] : ''));
            } else {
                // Выводим сообщение об ошибках
                echo $error;
            }
        } else {
            // Форма ввода
            echo '<div class="phdr"><b>' . ($res['type'] == 'r' ? __('section_edit') : __('category_edit')) . '</b></div>' .
                '<form action="' . $uri . 'edit/?id=' . $id . '" method="post">' .
                '<div class="gmenu">' .
                '<p><h3>' . __('title') . '</h3>' .
                '<input type="text" name="name" value="' . $res['text'] . '"/>' .
                '<br /><small>' . __('minmax_2_30') . '</small></p>' .
                '<p><h3>' . __('description') . '</h3>' .
                '<textarea name="desc" rows="' . App::user()->settings['field_h'] . '">' . str_replace('<br />', "\r\n", $res['soft']) . '</textarea>' .
                '<br /><small>' . __('not_mandatory_field') . '<br />' . __('minmax_2_500') . '</small></p>';
            if ($res['type'] == 'r') {
                echo '<p><h3>' . __('category') . '</h3><select name="category" size="1">';
                $req_c = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `type` = 'f' ORDER BY `realid` ASC");
                while ($res_c = $req_c->fetch()) {
                    echo '<option value="' . $res_c['id'] . '"' . ($res_c['id'] == $res['refid'] ? ' selected="selected"' : '') . '>' . $res_c['text'] . '</option>';
                }
                echo '</select></p>';
            }
            echo '<p><input type="submit" value="' . __('save') . '" name="submit" />' .
                '</p></div></form>' .
                '<div class="phdr"><a href="' . $uri . 'cat/' . ($res['type'] == 'r' ? '?id=' . $res['refid'] : '') . '">' . __('back') . '</a></div>';
        }
    } else {
        header('Location: ' . $uri . 'cat/');
    }
} else {
    header('Location: ' . $uri . 'cat/');
}
