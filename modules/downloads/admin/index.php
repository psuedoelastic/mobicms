<?php

/**
 * @package     mobiCMS
 * @link        http://mobicms.net
 * @copyright   Copyright (C) 2008-2012 mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://mobicms.net/about
 */

defined('MOBICMS') or die('Error: restricted access');

// Проверяем права доступа
if (App::user()->rights < 7) {
    echo __('access_forbidden');
    exit;
}

//TODO: Переделать на получение настроек из таблицы модулей
if (!isset(Mobi::$SYSTEM_SET['download'])) {
    // Задаем настройки по умолчанию
    $settings = [
        'mod'           => 1,
        'theme_screen'  => 1,
        'top'           => 25,
        'icon_java'     => 1,
        'video_screen'  => 1,
        'screen_resize' => 1
    ];
    $data = App::db()->quote(serialize($settings));
    App::db()->exec("INSERT INTO `cms_settings` SET `key` = 'download', `val` = " . $data);
} else {
    // Получаем имеющиеся настройки
    //TODO: Переделать на получение настроек из таблицы модулей
    $settings = unserialize(Mobi::$SYSTEM_SET['download']);
}

$form = new Form(App::router()->getUri(2));

$form
    ->fieldset(__('functions_download'))
    ->element('checkbox', 'mod',
        [
            'checked'      => $settings['mod'],
            'label_inline' => __('set_files_mod')
        ]
    )
    ->element('checkbox', 'theme_screen',
        [
            'checked'      => $settings['theme_screen'],
            'label_inline' => __('set_auto_screen')
        ]
    )
    ->element('checkbox', 'video_screen',
        [
            'checked'      => $settings['video_screen'],
            'label_inline' => __('set_auto_screen_video')
        ]
    )
    ->element('checkbox', 'icon_java',
        [
            'checked'      => $settings['icon_java'],
            'label_inline' => __('set_java_icons')
        ]
    )
    ->element('checkbox', 'screen_resize',
        [
            'checked'      => $settings['screen_resize'],
            'label_inline' => __('set_screen_resize')
        ]
    )
    ->element('text', 'top',
        [
            'value'        => $settings['top'],
            'label_inline' => __('set_top_files'),
            'class'        => 'small',
            'filter'       => [
                'type' => 'int',
                'min'  => 25,
                'max'  => 100
            ]
        ]
    )
    ->divider()
    ->element('submit', 'submit', [
        'value' => __('save'),
        'class' => 'btn btn-primary btn-large'])
    ->html('<a class="btn" href="' . App::cfg()->sys->homeurl . 'admin/' . '">' . __('back') . '</a>');

if ($form->process() === true) {
    // Записываем настройки в базу
    $data = App::db()->quote(serialize($form->output));
    App::db()->exec("UPDATE `cms_settings` SET `val` = " . $data . " WHERE `key` = 'download'");
}

App::view()->form = $form;
App::view()->setTemplate('admin.php');
