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

$reputation = !empty(Users::$data['reputation'])
    ? unserialize(Users::$data['reputation'])
    : ['a' => 0, 'b' => 0, 'c' => 0, 'd' => 0, 'e' => 0];

if (App::user()->id && App::user()->id != Users::$data['id']) {
    $checked = '5';
    $update = false;

    // Поиск имеющегося голосования
    $req = App::db()->query("
        SELECT *
        FROM `" . TP . "user__reputation`
        WHERE `from` = " . App::user()->id . "
        AND `to` = " . Users::$data['id'] . "
        LIMIT 1
        ");

    if ($req->rowCount()) {
        $res = $req->fetch();
        $checked = $res['value'];
        $update = true;
    }

    $form = new Mobicms\Form\Form(['action' => App::router()->getUri(3)]);

    $form
        ->title(__('vote'))
        ->html('<span class="description">' . __('reputation_help') . '</span>')
        ->element('radio', 'vote',
            [
                'checked' => $checked,
                'items'   =>
                    [
                        '2'  => __('reputation_excellent'),
                        '1'  => __('reputation_good'),
                        '0'  => __('reputation_neutrally'),
                        '-1' => __('reputation_bad'),
                        '-2' => __('reputation_very_bad')
                    ]
            ]
        )
        ->divider()
        ->element('submit', 'submit',
            [
                'value' => __('save'),
                'class' => 'btn btn-primary'
            ]
        )
        ->html('<a class="btn btn-link" href="' . App::router()->getUri(2) . '">' . __('back') . '</a>');

    if ($form->process() === true) {
        if ($update) {
            // Если есть, то обновляем данные
            App::db()->query("
                UPDATE `" . TP . "user__reputation` SET
                `value` = " . $form->output['vote'] . "
                WHERE `from` = " . App::user()->id . "
                AND `to` = " . Users::$data['id']
            );
        } else {
            // Если нет, то вставляем новую запись
            App::db()->query("
                INSERT INTO `" . TP . "user__reputation`
                SET
                `value` = " . $form->output['vote'] . ",
                `from` = " . App::user()->id . ",
                `to` = " . Users::$data['id']
            );
        }

        // Обновляем кэш пользователя
        $reputation = App::db()->query("
            SELECT
            COUNT(IF(`value` =  2, 1, NULL)) AS `a`,
            COUNT(IF(`value` =  1, 1, NULL)) AS `b`,
            COUNT(IF(`value` =  0, 1, NULL)) AS `c`,
            COUNT(IF(`value` = -1, 1, NULL)) AS `d`,
            COUNT(IF(`value` = -2, 1, NULL)) AS `e`
            FROM `user__reputation`
            WHERE `to` = " . Users::$data['id']
        )->fetch();

        $stmt = App::db()->prepare("UPDATE `" . TP . "user__` SET `reputation` = ? WHERE `id` = ?");
        $stmt->execute([serialize($reputation), Users::$data['id']]);
        $stmt = null;
    }

    App::view()->setRawVar('form', $form->display());
}

App::view()->counters = $reputation;
App::view()->reputation = [];
App::view()->reputation_total = array_sum($reputation);
foreach ($reputation as $key => $val) {
    App::view()->reputation[$key] = App::view()->reputation_total
        ? 100 / App::view()->reputation_total * $val
        : 0;
}

App::view()->setTemplate('reputation.php');
