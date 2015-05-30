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

//TODO: Доработать валидацию IP адресов
defined('MOBICMS') or die('Error: restricted access');

$uri = App::router()->getUri() . 'firewall/';
$mod = App::request()->getQuery('mod') == 'white' ? 'white' : 'black';
$color = $mod == 'white' ? 'green' : 'red';
$ref = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : $uri;

function update_cache()
{
    $file = CACHE_PATH . 'ip_list.dat';
    $req = App::db()->query("SELECT * FROM `" . TP . "system__firewall`");
    if ($req->rowCount()) {
        $in = fopen($file, "w+");
        flock($in, LOCK_EX);
        ftruncate($in, 0);
        foreach ($req as $res) {
            $mode = $res['mode'] == 'white' ? 2 : 1;
            fwrite($in, pack('ddS', $res['ip'], $res['ip_upto'], $mode));
        }
        fclose($in);
    } else {
        unlink($file);
    }
}

switch (trim(App::request()->getQuery('act', ''))) {
    case 'add':
        /*
        -----------------------------------------------------------------
        Добавление IP в список
        -----------------------------------------------------------------
        */
        echo '<div class="phdr"><a href="' . $uri . '?mod=' . $mod . '"><b>' . __('ip_accesslist') . '</b></a> | ' . __('add_ip') . '</div>' .
            ($mod == 'black'
                ? '<div class="rmenu"><p><h3>' . __('black_list') . '</h3></p></div>'
                : '<div class="gmenu"><p><h3>' . __('white_list') . '</h3></p></div>'
            );
        if (isset($_POST['submit']) || isset($_POST['confirm'])) {
            $error = [];
            $ip1 = 0;
            $ip2 = 0;
            $get_ip = isset($_POST['ip']) ? trim($_POST['ip']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';

            // Если адрес не введен, выдаем ошибку
            if (empty($get_ip)) {
                $error[] = __('add_ip_address_empty');
            }

            if (empty($error)) {
                if (strstr($get_ip, '-')) {
                    // Обрабатываем диапазон адресов
                    $array = explode('-', $get_ip);
                    $ip1 = trim($array[0]);
                    if (!Validate::ip($ip1)) {
                        $error[] = __('add_ip_firstaddress_error');
                    }
                    $ip2 = trim($array[1]);
                    if (!Validate::ip($ip2)) {
                        $error[] = __('add_ip_secondaddress_error');
                    }
                } elseif (strstr($get_ip, '*')) {
                    // Обрабатываем адреса с маской
                    $ipt1 = [];
                    $ipt2 = [];
                    $array = explode('.', $get_ip);
                    for ($i = 0; $i < 4; $i++) {
                        if (!isset($array[$i]) || $array[$i] == '*') {
                            $ipt1[$i] = '0';
                            $ipt2[$i] = '255';
                        } elseif (is_numeric($array[$i]) && $array[$i] >= 0 && $array[$i] <= 255) {
                            $ipt1[$i] = $array[$i];
                            $ipt2[$i] = $array[$i];
                        } else {
                            $error = __('add_ip_address_error');
                        }
                        $ip1 = $ipt1[0] . '.' . $ipt1[1] . '.' . $ipt1[2] . '.' . $ipt1[3];
                        $ip2 = $ipt2[0] . '.' . $ipt2[1] . '.' . $ipt2[2] . '.' . $ipt2[3];
                    }
                } else {
                    // Обрабатываем одиночный адрес
                    if (!Validate::ip($get_ip)) {
                        $error = __('add_ip_address_error');
                    } else {
                        $ip1 = $get_ip;
                        $ip2 = $get_ip;
                    }
                }
                $ip1 = sprintf("%u", ip2long($ip1));
                $ip2 = sprintf("%u", ip2long($ip2));
                if ($ip1 > $ip2) {
                    $tmp = $ip2;
                    $ip2 = $ip1;
                    $ip1 = $tmp;
                }
            }

            if (!$error) {
                // Проверка на конфликты адресов
                $req = App::db()->query("SELECT * FROM `" . TP . "system__firewall` WHERE ('$ip1' BETWEEN `ip` AND `ip_upto`) OR ('$ip2' BETWEEN `ip` AND `ip_upto`) OR (`ip` > '$ip1' AND `ip_upto` < '$ip2')");
                $total = $req->rowCount();
                if ($total) {
                    echo __('add_ip_address_conflict');
                    for ($i = 0; $res = $req->fetch(); ++$i) {
                        echo ($i % 2 ? '<div class="list2">' : '<div class="list1">') .
                            ($get_ip = $res['ip'] == $res['ip_upto'] ? long2ip($res['ip']) : long2ip($res['ip']) . ' - ' . long2ip($res['ip_upto'])) .
                            '</div>';
                    }
                    echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>';
                    echo '<p><a href="' . $uri . '">' . __('back') . '</a><br /><a href="' . App::router()->getUri() . '">' . __('admin_panel') . '</a></p>';
                    exit;
                }

                // Проверяем, не попадает ли IP администратора в диапазон
                if ($mod == 'black' && (App::network()->getIp() >= $ip1 && App::network()->getIp() <= $ip2) || App::network()->getIpViaProxy() && (App::network()->getIpViaProxy() >= $ip1 && App::network()->getIpViaProxy() <= $ip2)) {
                    $error = __('add_ip_myaddress_conflict');
                }
            }

            if (empty($error)) {
                if (isset($_POST['confirm'])) {
                    // Добавляем IP в базу данных
                    App::db()->exec("INSERT INTO `" . TP . "system__firewall` SET
                        `ip` = $ip1,
                        `ip_upto` = $ip2,
                        `mode` = '" . $mod . "',
                        `timestamp` = " . time() . ",
                        `user_id` = " . App::user()->id . ",
                        `description` = " . App::db()->quote(base64_decode($description))
                    );
                    update_cache();
                    header('Location: ' . $uri . '?mod=' . $mod);
                    exit;
                } else {
                    // Выводим окно подтверждения
                    echo '<form action="' . $uri . '?act=add" method="post"><div class="menu">' .
                        '<input type="hidden" value="' . $mod . '" name="mod" />' .
                        '<input type="hidden" value="' . long2ip($ip1) . ($ip1 == $ip2 ? '' : '-' . long2ip($ip2)) . '" name="ip" />' .
                        '<input type="hidden" value="' . base64_encode($description) . '" name="description" />' .
                        '<p><h3>' . __('ip_address') . ': ' .
                        '<span class="' . $color . '">' . long2ip($ip1) . ($ip1 == $ip2 ? '' : '&#160;-&#160;' . long2ip($ip2)) . '</span></h3>' .
                        ($mod == 'black' ? __('add_ip_confirmation_black') : __('add_ip_confirmation_white')) .
                        '</p><p><input type="submit" name="confirm" value="' . __('save') . '"/></p>' .
                        '</div></form>';
                }
            }

            // Показываем ошибки, если есть
            if (!empty($error)) {
                echo $error . ' <a href="' . $uri . '?act=add' . ($mod == 'black' ? '&amp;mod=black' : '') . '">' . __('back') . '</a>';
            }
        } else {
            /*
            -----------------------------------------------------------------
            Форма ввода IP адреса для Бана
            -----------------------------------------------------------------
            */
            echo '<form action="' . $uri . '?act=add" method="post">' .
                '<div class="menu"><p><h3>' . __('ip_address') . ':</h3>' .
                '<input type="hidden" value="' . htmlspecialchars($mod) . '" name="mod" />' .
                '<input type="text" name="ip"/></p>' .
                '<p><h3>' . __('description') . '</h3>' .
                '<textarea rows="' . App::user()->settings['field_h'] . '" name="description"></textarea>' .
                '<br /><small>&nbsp;' . __('not_mandatory_field') . '</small></p>' .
                '<p><input type="submit" name="submit" value="' . __('add') . '"/></p></div>' .
                '</form>';
        }
        // Нижний блок с подсказками
        echo '<div class="phdr"><a href="' . $uri . ($mod == 'black' ? '' : '?mod=white') . '">' . __('back') . '</a></div>' .
            '<div class="topmenu"><p>' .
            ($mod == 'black'
                ? '<strong>' . mb_strtoupper(__('black_list')) . ':</strong> ' . __('black_list_help')
                : '<strong>' . mb_strtoupper(__('white_list')) . ':</strong> ' . __('white_list_help')
            ) .
            '</p>' . (isset($_POST['submit']) ? '' : '<p>' . __('add_ip_help') . '</p>') .
            '</div>' .
            '<p><a href="' . App::router()->getUri() . '">' . __('admin_panel') . '</a></p>';
        break;

    case 'clear':
        /*
        -----------------------------------------------------------------
        Очищаем все адреса выбранного списка
        -----------------------------------------------------------------
        */
        echo '<div class="phdr"><a href="' . $uri . '?mod=' . $mod . '"><b>' . __('ip_accesslist') . '</b></a> | ' . __('clear_list') . '</div>' .
            ($mod == 'black'
                ? '<div class="rmenu"><p><h3>' . __('black_list') . '</h3></p></div>'
                : '<div class="gmenu"><p><h3>' . __('white_list') . '</h3></p></div>'
            );
        if (isset($_POST['submit'])) {
            App::db()->exec("DELETE FROM `" . TP . "system__firewall` WHERE `mode` = '" . $mod . "'");
            App::db()->query("OPTIMIZE TABLE `" . TP . "system__firewall`");
            update_cache();
            header('Location: ' . $uri . '?mod=' . $mod);
        } else {
            echo '<form action="' . $uri . '?act=clear&amp;mod=' . $mod . '" method="post">' .
                '<div class="rmenu"><p>' . __('clear_list_warning') . '</p>' .
                '<p><input type="submit" name="submit" value="' . __('clear') . ' "/></p>' .
                '</div></form>';
        }
        echo '<div class="phdr"><a href="' . $ref . '">' . __('back') . '</a></div>';
        break;

    case 'del':
        /*
        -----------------------------------------------------------------
        Удаляем выбранные адреса IP
        -----------------------------------------------------------------
        */
        $del = isset($_POST['del']) && is_array($_POST['del']) ? $_POST['del'] : [];
        echo '<div class="phdr"><a href="' . $uri . '?mod=' . $mod . '"><b>' . __('ip_accesslist') . '</b></a> | ' . __('delete_ip') . '</div>' .
            ($mod == 'black'
                ? '<div class="rmenu"><p><h3>' . __('black_list') . '</h3></p></div>'
                : '<div class="gmenu"><p><h3>' . __('white_list') . '</h3></p></div>'
            );
        if (!empty($del)) {
            if (isset($_POST['submit'])) {
                foreach ($del as $val) {
                    if (is_numeric($val)) {
                        mysql_query("DELETE FROM `" . TP . "system__firewall` WHERE `ip` = " . $val);
                    }
                }
                App::db()->query("OPTIMIZE TABLE `" . TP . "system__firewall`");
                update_cache();
                header('Location: ' . $uri . '?mod=' . $mod);
            } else {
                echo '<form action="' . $uri . '?act=del&amp;mod=' . $mod . '" method="post">';
                foreach ($del as $val) {
                    echo '<input type="hidden" value="' . $val . '" name="del[]" />';
                }
                echo '<div class="rmenu"><p>' . __('delete_ip_warning') . '</p>' .
                    '<p><input type="submit" name="submit" value="' . __('delete') . ' "/></p>' .
                    '</div></form>';
            }
        } else {
            echo __('error_not_selected');
        }
        echo '<div class="phdr"><a href="' . $ref . '">' . __('back') . '</a></div>';
        break;

    default:
        /*
        -----------------------------------------------------------------
        Главное меню модуля
        -----------------------------------------------------------------
        */
        $menu =
            [
                ($mod != 'white' ? '<strong>' . __('black_list') . '</strong>' : '<a href="' . $uri . '">' . __('black_list') . '</a>'),
                ($mod == 'white' ? '<strong>' . __('white_list') . '</strong>' : '<a href="' . $uri . '?mod=white">' . __('white_list') . '</a>')
            ];
        echo '<div class="phdr"><a href="' . App::router()->getUri() . '"><b>' . __('admin_panel') . '</b></a> | ' . __('firewall') . '</div>' .
            '<div class="topmenu">' . Functions::displayMenu($menu) . '</div>';

        $total = App::db()->query("SELECT COUNT(*) FROM `" . TP . "system__firewall` WHERE `mode` = '" . $mod . "'")->fetchColumn();

        if ($total > App::user()->settings['page_size']) {
            echo '<div class="topmenu">' . Functions::displayPagination($uri . '?', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>';
        }

        // Обновляем кэш IP адресов
        if (isset($_GET['update_cache'])) {
            update_cache();
            echo '<div class="gmenu">' . __('cache_updated') . '</div>';
        }

        // Выводим список IP
        echo '<form action="' . $uri . '?act=add&amp;mod=' . $mod . '" method="post">' .
            '<div class="' . ($mod == 'white' ? 'gmenu' : 'rmenu') . '"><input type="submit" name="delete" value="' . __('add') . '"/></div></form>';
        if ($total) {
            echo '<form action="' . $uri . '?act=del&amp;mod=' . $mod . '" method="post">';
            $req = App::db()->query("SELECT `" . TP . "system__firewall`.*, `" . TP . "user__`.`nickname`
                FROM `" . TP . "system__firewall` LEFT JOIN `" . TP . "user__` ON `" . TP . "system__firewall`.`user_id` = `" . TP . "user__`.`id`
                WHERE `" . TP . "system__firewall`.`mode` = '" . $mod . "'
                ORDER BY `" . TP . "system__firewall`.`timestamp` DESC
                " . App::db()->pagination()
            );
            for ($i = 0; $res = $req->fetch(); ++$i) {
                echo ($i % 2 ? '<div class="list2">' : '<div class="list1">') .
                    '<input type="checkbox" name="del[]" value="' . $res['ip'] . '"/>&#160;' .
                    '<strong>IP: <span class="' . $color . '">' . long2ip($res['ip']) . ($res['ip'] != $res['ip_upto'] ? ' - ' . long2ip($res['ip_upto']) : '') . '</span></strong>' .
                    (empty($res['description']) ? '' : '<div class="sub">' . htmlspecialchars($res['description'], 1) . '</div>') .
                    '<div class="sub"><span class="gray">' .
                    __('date') . ':&#160;' . Functions::displayDate($res['timestamp']) .
                    '<br />' . __('who_added') . ':&#160;' . $res['nickname'] .
                    '</span></div></div>';
            }
            echo '<div class="rmenu"><input type="submit" name="delete" value="' . __('delete') . ' "/></div></form>';
        } else {
            echo '<div class="menu"><p>' . __('list_empty') . '</p></div>';
        }

        // Нижний блок с подсказками
        echo '<div class="phdr">' . __('total') . ': ' . $total . '</div>' .
            '<div class="topmenu"><small><p>' .
            ($mod == 'white'
                ? '<strong>' . mb_strtoupper(__('white_list')) . ':</strong> ' . __('white_list_help')
                : '<strong>' . mb_strtoupper(__('black_list')) . ':</strong> ' . __('black_list_help')
            ) . '</p></small></div>';

        // Постраничная навигация
        if ($total > App::user()->settings['page_size']) {
            echo '<div class="topmenu">' . Functions::displayPagination($uri . '?', App::vars()->start, $total, App::user()->settings['page_size']) . '</div>' .
                '<p><form action="' . $uri . '" method="post">' .
                '<input type="text" name="page" size="2"/>' .
                '<input type="submit" value="' . __('to_page') . ' &gt;&gt;"/>' .
                '</form></p>';
        }

        // Ссылки внизу
        echo '<p>' . ($total ? '<a href="' . $uri . '?act=clear&amp;mod=' . $mod . '">' . __('clear_list') . '</a><br />' : '') .
            '<a href="' . $uri . '?mod=' . $mod . '&amp;update_cache">' . __('update_cache') . '</a><br/>' .
            '<a href="' . App::router()->getUri() . '">' . __('admin_panel') . '</a></p>';
}