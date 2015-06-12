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

global $user;

/*
-----------------------------------------------------------------
Проверяем права доступа
-----------------------------------------------------------------
*/
if ($user['id'] != App::user()->id) {
    echo __('access_forbidden');
    exit;
}

$mod = App::request()->getQuery('mod', false);

App::view()->menu = [
    (!$mod ? '<b>' . __('common_settings') . '</b>' : '<a href="profile.php?act=settings">' . __('common_settings') . '</a>'),
    ($mod == 'forum' ? '<b>' . __('forum') . '</b>' : '<a href="profile.php?act=settings&amp;mod=forum">' . __('forum') . '</a>'),
];

/*
-----------------------------------------------------------------
Пользовательские настройки
-----------------------------------------------------------------
*/
switch ($mod) {
    case 'forum':
        /*
        -----------------------------------------------------------------
        Настройки Форума
        -----------------------------------------------------------------
        */
        echo '<div class="phdr"><b>' . __('settings') . '</b> | ' . __('forum') . '</div>' .
            '<div class="topmenu">' . Functions::displayMenu($menu) . '</div>';
        if (($set_forum = App::user()->getData('set_forum')) === false) {
            $set_forum = [
                'farea'    => 0,
                'upfp'     => 0,
                'preview'  => 1,
                'postclip' => 1,
                'postcut'  => 2
            ];
        }
        if (isset($_POST['submit'])) {
            $set_forum['farea'] = isset($_POST['farea']);
            $set_forum['upfp'] = isset($_POST['upfp']);
            $set_forum['preview'] = isset($_POST['preview']);
            $set_forum['postclip'] = isset($_POST['postclip']) ? intval($_POST['postclip']) : 1;
            $set_forum['postcut'] = isset($_POST['postcut']) ? intval($_POST['postcut']) : 1;
            if ($set_forum['postclip'] < 0 || $set_forum['postclip'] > 2)
                $set_forum['postclip'] = 1;
            if ($set_forum['postcut'] < 0 || $set_forum['postcut'] > 3)
                $set_forum['postcut'] = 1;
            App::user()->set_data('set_forum', $set_forum);
            echo '<div class="gmenu">' . __('settings_saved') . '</div>';
        }
        if (isset($_GET['reset']) || empty($set_forum)) {
            App::user()->set_data('set_forum');
            $set_forum = [
                'farea'    => 0,
                'upfp'     => 0,
                'preview'  => 1,
                'postclip' => 1,
                'postcut'  => 2
            ];
            echo '<div class="rmenu">' . __('settings_default') . '</div>';
        }
        echo '<form action="profile.php?act=settings&amp;mod=forum" method="post">' .
            '<div class="menu"><p><h3>' . __('main_settings') . '</h3>' .
            '<input name="upfp" type="checkbox" value="1" ' . ($set_forum['upfp'] ? 'checked="checked"' : '') . ' />&#160;' . __('sorting_return') . '<br/>' .
            '<input name="farea" type="checkbox" value="1" ' . ($set_forum['farea'] ? 'checked="checked"' : '') . ' />&#160;' . __('field_on') . '<br/>' .
            '<input name="preview" type="checkbox" value="1" ' . ($set_forum['preview'] ? 'checked="checked"' : '') . ' />&#160;' . __('preview') . '<br/>' .
            '</p><p><h3>' . __('clip_first_post') . '</h3>' .
            '<input type="radio" value="2" name="postclip" ' . ($set_forum['postclip'] == 2 ? 'checked="checked"' : '') . '/>&#160;' . __('always') . '<br />' .
            '<input type="radio" value="1" name="postclip" ' . ($set_forum['postclip'] == 1 ? 'checked="checked"' : '') . '/>&#160;' . __('in_not_read') . '<br />' .
            '<input type="radio" value="0" name="postclip" ' . (!$set_forum['postclip'] ? 'checked="checked"' : '') . '/>&#160;' . __('never') .
            '</p><p><h3>' . __('scrap_of_posts') . '</h3>' .
            '<input type="radio" value="1" name="postcut" ' . ($set_forum['postcut'] == 1 ? 'checked="checked"' : '') . '/>&#160;' . __('500_symbols') . '<br />' .
            '<input type="radio" value="2" name="postcut" ' . ($set_forum['postcut'] == 2 ? 'checked="checked"' : '') . '/>&#160;' . __('1000_symbols') . '<br />' .
            '<input type="radio" value="3" name="postcut" ' . ($set_forum['postcut'] == 3 ? 'checked="checked"' : '') . '/>&#160;' . __('3000_symbols') . '<br />' .
            '<input type="radio" value="0" name="postcut" ' . (!$set_forum['postcut'] ? 'checked="checked"' : '') . '/>&#160;' . __('not_to_cut_off') . '<br />' .
            '</p><p><input type="submit" name="submit" value="' . __('save') . '"/></p></div></form>' .
            '<div class="phdr"><a href="profile.php?act=settings&amp;mod=forum&amp;reset">' . __('reset_settings') . '</a></div>' .
            '<p><a href="' . App::router()->getUri(1) . '">' . __('to_forum') . '</a></p>';
        break;

    default:
}
