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

$stat = [
    // Счетчик модераторов форума
    'total_moders'     =>
        [
            'cache_time' => '600',
            'sql'        => "SELECT COUNT(*) FROM `user__` WHERE `rights` = '3'"
        ],

    // Счетчик кураторов форума
    'total_curators'     =>
        [
            'cache_time' => '570',
            'sql'        => "SELECT COUNT(*) FROM `forum__curators`"
        ],

    // Счетчик категорий
    'total_cat'     =>
        [
            'cache_time' => '6000',
            'sql'        => "SELECT COUNT(*) FROM `forum__` WHERE `type` = 'f'"
        ],

    // Счетчик разделов
    'total_sub'     =>
        [
            'cache_time' => '5000',
            'sql'        => "SELECT COUNT(*) FROM `forum__` WHERE `type` = 'r'"
        ],

    // Счетчик тем
    'total_thm'     =>
        [
            'cache_time' => '350',
            'sql'        => "SELECT COUNT(*) FROM `forum__` WHERE `type` = 't' AND `close` != '1' AND `edit` != '1'"
        ],

    // Счетчик закрытых тем
    'total_thm_closed'     =>
        [
            'cache_time' => '380',
            'sql'        => "SELECT COUNT(*) FROM `forum__` WHERE `type` = 't' AND `edit` = '1'"
        ],

    // Счетчик удаленных тем
    'total_thm_del' =>
        [
            'cache_time' => '400',
            'sql'        => "SELECT COUNT(*) FROM `forum__` WHERE `type` = 't' AND `close` = '1'"
        ],

    // Счетчик сообщений
    'total_msg'     =>
        [
            'cache_time' => '250',
            'sql'        => "SELECT COUNT(*) FROM `forum__` WHERE `type` = 'm'"
        ],

    // Счетчик удаленных сообщений
    'total_msg_del' =>
        [
            'cache_time' => '270',
            'sql'        => "SELECT COUNT(*) FROM `forum__` WHERE `type` = 'm' AND `close` = '1'"
        ],

    // Счетчик файлов
    'total_files'   =>
        [
            'cache_time' => '500',
            'sql'        => "SELECT COUNT(*) FROM `forum__files`"
        ],

    // Счетчик опросов
    'total_votes'   =>
        [
            'cache_time' => '300',
            'sql'        => "SELECT COUNT(*) FROM `forum__vote` WHERE `type` = '1'"
        ],
];

$cacheFile = CACHE_PATH . 'forum-stat.cache';
$cacheData = is_file($cacheFile) ? unserialize(file_get_contents($cacheFile)) : [];
$write = false;

foreach ($stat as $key => $val) {
    if (!isset($cacheData[$key]) || $cacheData[$key]['time'] < time() - $val['cache_time']) {
        $write = true;
        $cacheData[$key] =
            [
                'count' => App::db()->query($val['sql'])->fetchColumn(),
                'time'  => time()
            ];
    }

    App::view()->$key = $cacheData[$key]['count'];
}

if ($write === true
    && file_put_contents($cacheFile, serialize($cacheData)) === false
) {
    throw new RuntimeException('Can not write Forum-stat cache file');
}

App::view()->uri = App::router()->getUri(2);
App::view()->setTemplate('admin_stat.php');
