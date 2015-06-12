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

if (!$id) {
    echo __('error_wrong_data') . ' <a href="' . $url . '">' . __('to_forum') . '</a>';
    exit;
}

//TODO: Переделать с $do на $mod
switch ($do) {
    case 'unset':
        /*
        -----------------------------------------------------------------
        Удаляем фильтр
        -----------------------------------------------------------------
        */
        unset($_SESSION['fsort_id']);
        unset($_SESSION['fsort_users']);
        header("Location: " . $url . "?id=" . $id);
        break;

    case 'set':
        /*
        -----------------------------------------------------------------
        Устанавливаем фильтр по авторам
        -----------------------------------------------------------------
        */
        $users = isset($_POST['users']) ? $_POST['users'] : '';
        if (empty($_POST['users'])) {
            echo '<div class="rmenu"><p>' . __('error_author_select') . '<br /><a href="' . $url . '?act=filter&amp;id=' . $id . '&amp;start=' . App::vars()->start . '">' . __('back') . '</a></p></div>';
            exit;
        }
        $array = [];
        foreach ($users as $val) {
            $array[] = intval($val);
        }
        $_SESSION['fsort_id'] = $id;
        $_SESSION['fsort_users'] = serialize($array);
        header("Location: " . $url . "?id=" . $id);
        break;

    default :
        /*
        -----------------------------------------------------------------
        Показываем список авторов темы, с возможностью выбора
        -----------------------------------------------------------------
        */
        $req = App::db()->query("SELECT *, COUNT(`from`) AS `count` FROM `forum__` WHERE `refid` = " . $id . " GROUP BY `from` ORDER BY `from`");
        $total = $req->rowCount();
        if ($total > 0) {
            echo '<div class="phdr"><a href="' . $url . '?id=' . $id . '&amp;start=' . App::vars()->start . '"><b>' . __('forum') . '</b></a> | ' . __('filter_on_author') . '</div>' .
                '<form action="' . $url . '?act=filter&amp;id=' . $id . '&amp;start=' . App::vars()->start . '&amp;do=set" method="post">';
            $i = 0;
            while ($res = $req->fetch()) {
                echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                echo '<input type="checkbox" name="users[]" value="' . $res['user_id'] . '"/>&#160;' .
                    '<a href="../users/profile.php?user=' . $res['user_id'] . '">' . $res['from'] . '</a> [' . $res['count'] . ']</div>';
                ++$i;
            }
            echo '<div class="gmenu"><input type="submit" value="' . __('filter_to') . '" name="submit" /></div>' .
                '<div class="phdr"><small>' . __('filter_on_author_help') . '</small></div>' .
                '</form>';
        } else {
            echo __('error_wrong_data');
        }
}
echo '<p><a href="' . $url . '?id=' . $id . '&amp;start=' . App::vars()->start . '">' . __('return_to_topic') . '</a></p>';
