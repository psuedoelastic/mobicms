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

echo '<p>' . Counters::forumMessagesNew(1) . '</p>' .
    '<div class="phdr"><a href="' . $url . '"><b>' . __('forum') . '</b></a> | ' . __('search') . '</div>';

/*
-----------------------------------------------------------------
Функция подсветки результатов запроса
-----------------------------------------------------------------
*/
function ReplaceKeywords($search, $text)
{
    $search = str_replace('*', '', $search);

    return mb_strlen($search) < 3 ? $text : preg_replace('|(' . preg_quote($search, '/') . ')|siu', '<span style="background-color: #FFFF33">$1</span>', $text);
}

switch (App::request()->getQuery('act', '')) {
    case 'reset':
        /*
        -----------------------------------------------------------------
        Очищаем историю личных поисковых запросов
        -----------------------------------------------------------------
        */
        if (App::user()->id) {
            if (isset($_POST['submit'])) {
                App::user()->set_data('forum_search');
                header('Location: ' . $url);
            } else {
                echo '<form action="' . $url . '?act=reset" method="post">' .
                    '<div class="rmenu">' .
                    '<p>' . __('search_history_reset') . '</p>' .
                    '<p><input type="submit" name="submit" value="' . __('clear') . '" /></p>' .
                    '<p><a href="' . $url . '">' . __('cancel') . '</a></p>' .
                    '</div>' .
                    '</form>';
            }
        }
        break;

    default:
        /*
        -----------------------------------------------------------------
        Принимаем данные, выводим форму поиска
        -----------------------------------------------------------------
        */
        $search_post = isset($_POST['search']) ? trim($_POST['search']) : false;
        $search_get = isset($_GET['search']) ? rawurldecode(trim($_GET['search'])) : false;
        $search = $search_post ? $search_post : $search_get;
        //$search = preg_replace("/[^\w\x7F-\xFF\s]/", " ", $search);
        $search_t = isset($_REQUEST['t']);
        $to_history = false;
        echo '<div class="gmenu">' .
            '<form action="' . $url . '" method="post"><p>' .
            '<input type="text" value="' . ($search ? htmlspecialchars($search) : '') . '" name="search" />' .
            '<input type="submit" value="' . __('search') . '" name="submit" /><br />' .
            '<input name="t" type="checkbox" value="1" ' . ($search_t ? 'checked="checked"' : '') . ' />&nbsp;' . __('search_topic_name') .
            '</p></form>' .
            '</div>';

        /*
        -----------------------------------------------------------------
        Проверям на ошибки
        -----------------------------------------------------------------
        */
        $error = $search && mb_strlen($search) < 4 || mb_strlen($search) > 64 ? true : false;

        if ($search && !$error) {
            /*
            -----------------------------------------------------------------
            Выводим результаты запроса
            -----------------------------------------------------------------
            */
            $array = explode(' ', $search);
            $count = count($array);
            if ($search_t) {
                // Поиск в названиях тем
                $stmt = App::db()->prepare("
                    SELECT COUNT(*) FROM `forum__`
                    WHERE MATCH (`text`) AGAINST (? IN BOOLEAN MODE)
                    AND `type` = 't'" . (App::user()->rights >= 7 ? "" : " AND `close` != '1'
                "));

                $stmt->execute([$search]);
                $total = $stmt->fetchColumn();
                $stmt = null;
            } else {
                // Поиск только в тексте
                $stmt = App::db()->prepare("
                    SELECT COUNT(*) FROM `forum__`, `forum__` AS `forum2`
                    WHERE MATCH (`forum__`.`text`) AGAINST (? IN BOOLEAN MODE)
                    AND `forum__`.`type` = 'm'
					AND `forum2`.`id` = `forum__`.`refid`
					" . (App::user()->rights >= 7 ? "" : "AND `forum2`.`close` != '1' AND `forum__`.`close` != '1'
				"));

                $stmt->execute([$search]);
                $total = $stmt->fetchColumn();
                $stmt = null;
            }
            echo '<div class="phdr">' . __('search_results') . '</div>';
            if ($total > App::user()->settings['page_size'])
                echo '<div class="topmenu">' . Functions::displayPagination($url . '?' . ($search_t ? 't=1&amp;' : '') . 'search=' . urlencode($search) . '&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
            if ($total) {
                $to_history = true;
                if ($search_t) {
                    // Поиск в названиях тем
                    $stmt = App::db()->prepare("
                        SELECT *, MATCH (`text`) AGAINST (:query IN BOOLEAN MODE) AS `rel`
                        FROM `forum__`
                        WHERE MATCH (`text`) AGAINST (:query IN BOOLEAN MODE)
                        AND `type` = '" . ($search_t ? 't' : 'm') . "'
						" . (App::user()->rights >= 7 ? "" : "AND `close` != '1'") . "
                        ORDER BY `rel` DESC
                        " . App::db()->pagination()
                    );

                    $stmt->bindParam(':query', $search);
                    $stmt->execute();
                } else {
                    // Поиск только в тексте
                    $stmt = App::db()->prepare("
                        SELECT `forum__`.*, `forum2`.`id` as `id2`, `forum2`.`text` as `text2`,
						MATCH (`forum__`.`text`) AGAINST (:query IN BOOLEAN MODE) as `rel`
                        FROM `forum__`, `forum__` as `forum2`
                        WHERE MATCH (`forum__`.`text`) AGAINST (:query IN BOOLEAN MODE)
                        AND `forum__`.`type` = 'm'
						AND `forum2`.`id` = `forum__`.`refid`
						" . (App::user()->rights >= 7 ? "" : "AND `forum2`.`close` != '1' AND `forum__`.`close` != '1'") . "
                        ORDER BY `rel` DESC
                        " . App::db()->pagination()
                    );

                    $stmt->bindParam(':query', $search);
                    $stmt->execute();
                }
                $i = 0;
                while ($res = $stmt->fetch()) {
                    echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                    if ($search_t) {
                        // Поиск в названиях тем
                        $req_p = App::db()->query("SELECT `text` FROM `forum__` WHERE `refid` = " . $res['id'] .
                            (App::user()->rights >= 7 ? "" : "AND `close` != '1'") . " ORDER BY `id` ASC LIMIT 1");
                        $res_p = $req_p->fetch();
                        $res['text2'] = $res_p['text'];
                    }
                    $text = $search_t ? $res['text2'] : $res['text'];
                    foreach ($array as $srch) if (($pos = mb_strpos(mb_strtolower($res['text']), mb_strtolower(str_replace('*', '', $srch)))) !== false) break;
                    if (!isset($pos) || $pos < 100) $pos = 100;
                    $text = htmlspecialchars(mb_substr($text, ($pos - 100), 400));
                    $text = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $text);
                    if ($search_t) {
                        foreach ($array as $val) {
                            $res['text'] = ReplaceKeywords($val, $res['text']);
                        }
                    } else {
                        foreach ($array as $val) {
                            $text = ReplaceKeywords($val, $text);
                        }
                    }
                    //TODO: Переделать ссылку
                    echo '<b>' . ($search_t ? $res['text'] : $res['text2']) . '</b><br />' .
                        '<a href="../users/profile.php?user=' . $res['user_id'] . '">' . $res['from'] . '</a> ' .
                        ' <span class="gray">(' . Functions::displayDate($res['time']) . ')</span><br/>' . $text;
                    if (mb_strlen($res['text']) > 500)
                        echo '...<a href="' . $url . '?act=post&amp;id=' . $res['id'] . '">' . __('read_all') . ' &gt;&gt;</a>';
                    echo '<br /><a href="' . $url . '?id=' . ($search_t ? $res['id'] : $res['id2']) . '">' . __('to_topic') . '</a>' . ($search_t ? ''
                            : ' | <a href="' . $url . '?act=post&amp;id=' . $res['id'] . '">' . __('to_post') . '</a>');
                    echo '</div>';
                    ++$i;
                }
                $stmt = null;
            } else {
                echo '<div class="rmenu"><p>' . __('search_results_empty') . '</p></div>';
            }
            echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
        } else {
            if ($error) {
                echo __('error_wrong_lenght');
            }
            echo '<div class="phdr"><small>' . __('search_help') . '</small></div>';
        }

        /*
        -----------------------------------------------------------------
        Обрабатываем и показываем историю личных поисковых запросов
        -----------------------------------------------------------------
        */
        if (App::user()->id) {
            $search_val = mb_strtolower($search);
            if (($history = App::user()->getData('forum_search')) === false) {
                $history = [];
            }
            // Записываем данные в историю
            if ($to_history && !in_array($search_val, $history)) {
                if (count($history) > 20) {
                    array_shift($history);
                }
                $history[] = $search_val;
                App::user()->set_data('forum_search', $history);
            }
            // Показываем историю поиска
            if (!empty($history)) {
                sort($history);
                $history_list = [];
                foreach ($history as $val) {
                    $history_list[] = '<a href="' . $url . '?search=' . urlencode($val) . '">' . htmlspecialchars($val) . '</a>';
                }
                echo '<div class="topmenu">' .
                    '<b>' . __('search_history') . '</b> <span class="red"><a href="' . $url . '?act=reset">[x]</a></span><br />' .
                    Functions::displayMenu($history_list) .
                    '</div>';
            }
        }

        // Постраничная навигация
        if (isset($total) && $total > App::user()->settings['page_size']) {
            echo '<div class="topmenu">' . Functions::displayPagination($url . '?' . ($search_t ? 't=1&amp;' : '') . 'search=' . urlencode($search) . '&amp;', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
                '<p><form action="' . $url . '?' . ($search_t ? 't=1&amp;' : '') . 'search=' . urlencode($search) . '" method="post">' .
                '<input type="text" name="page" size="2"/>' .
                '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
                '</form></p>';
        }

        echo '<p>' . ($search ? '<a href="' . $url . '">' . __('search_new') . '</a><br />' : '') . '<a href="' . $url . '">' . __('forum') . '</a></p>';
}
