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

$url = App::router()->getUri();

App::view()->userarg =
    [
        'lastvisit' => 1,
        'iphist'    => 1,
        'header'    => '<b>ID:' . Users::$data['id'] . '</b>',
        'footer'    => (Users::$data['id'] != App::user()->id
            ? '<span class="gray">' . __('where') . ':</span> '
            : false)
    ];

// Построение графика репутации
$reputation = !empty(Users::$data['reputation'])
    ? unserialize(Users::$data['reputation'])
    : ['a' => 0, 'b' => 0, 'c' => 0, 'd' => 0, 'e' => 0];
App::view()->reputation = [];
App::view()->reputation_total = array_sum($reputation);
foreach ($reputation as $key => $val) {
    App::view()->reputation[$key] = App::view()->reputation_total
        ? 100 / App::view()->reputation_total * $val
        : 0;
}

App::view()->setTemplate('profile.php');
