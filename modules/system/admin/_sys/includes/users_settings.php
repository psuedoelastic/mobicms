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

$form = new Mobicms\Form\Form(['action' => App::router()->getUri(2)]);

$form
    ->title(__('registration'))
    ->element('checkbox', 'usr_reg_allow',
        [
            'label_inline' => __('reg_allow'),
            'checked'      => App::cfg()->sys->usr_reg_allow
        ]
    )
    ->element('checkbox', 'usr_reg_moderation',
        [
            'label_inline' => __('reg_moderation'),
            'checked'      => App::cfg()->sys->usr_reg_moderation
        ]
    )
    ->element('checkbox', 'usr_reg_email',
        [
            'label_inline' => __('reg_email'),
            'checked'      => App::cfg()->sys->usr_reg_email
        ]
    )
    ->element('checkbox', 'usr_reg_quarantine',
        [
            'label_inline' => __('reg_quarantine'),
            'checked'      => App::cfg()->sys->usr_reg_quarantine
        ]
    );

if (App::user()->rights == 9) {
    $form
        ->title(__('for_users'))
        ->element('checkbox', 'usr_change_sex',
            [
                'label_inline' => __('change_sex'),
                'checked'      => App::cfg()->sys->usr_change_sex
            ]
        )
        ->element('checkbox', 'usr_change_status',
            [
                'label_inline' => __('change_status'),
                'checked'      => App::cfg()->sys->usr_change_status
            ]
        )
        ->element('checkbox', 'usr_upload_avatars',
            [
                'label_inline' => __('upload_avatars'),
                'checked'      => App::cfg()->sys->usr_upload_avatars
            ]
        )
        ->element('checkbox', 'usr_gravatar',
            [
                'label_inline' => __('use_gravatar'),
                'checked'      => App::cfg()->sys->usr_gravatar
            ]
        )
        ->element('checkbox', 'usr_nickname_digits_only',
            [
                'label_inline' => __('digits_only'),
                'checked'      => App::cfg()->sys->usr_nickname_digits_only
            ]
        )
        ->element('checkbox', 'usr_change_nickname',
            [
                'label_inline' => __('change_nickname_allow'),
                'checked'      => App::cfg()->sys->usr_change_nickname
            ]
        )
        ->element('text', 'usr_change_nickname_period',
            [
                'label_inline' => __('how_many_days') . ' <span class="note">(0-30)</span>',
                'value'        => App::cfg()->sys->usr_change_nickname_period,
                'class'        => 'mini',
                'filter'       =>
                    [
                        'type' => 'int',
                        'min'  => 0,
                        'max'  => 30
                    ]
            ]
        )
        ->title(__('for_guests'))
        ->element('checkbox', 'usr_view_online',
            [
                'label_inline' => __('view_online'),
                'checked'      => App::cfg()->sys->usr_view_online
            ]
        )
        ->element('checkbox', 'usr_view_userlist',
            [
                'label_inline' => __('view_userlist'),
                'checked'      => App::cfg()->sys->usr_view_userlist
            ]
        )
        ->element('checkbox', 'usr_view_profiles',
            [
                'label_inline' => __('view_profiles'),
                'checked'      => App::cfg()->sys->usr_view_profiles
            ]
        )
        ->title(__('antiflood'))
        ->element('radio', 'usr_flood_mode',
            [
                'checked' => App::cfg()->sys->usr_flood_mode,
                'items'   =>
                    [
                        '3' => __('day'),
                        '4' => __('night'),
                        '2' => __('autoswitch'),
                        '1' => __('adaptive')
                    ]
            ]
        )
        ->element('text', 'usr_flood_day',
            [
                'value'        => App::cfg()->sys->usr_flood_day,
                'class'        => 'small',
                'label_inline' => __('sec') . ', ' . __('day') . ' <span class="note">(3-300)</span>',
                'filter'       =>
                    [
                        'type' => 'int',
                        'min'  => 3,
                        'max'  => 300
                    ]
            ]
        )
        ->element('text', 'usr_flood_night',
            [
                'value'        => App::cfg()->sys->usr_flood_night,
                'class'        => 'small',
                'label_inline' => __('sec') . ', ' . __('night') . ' <span class="note">(3-300)</span>',
                'filter'       =>
                    [
                        'type' => 'int',
                        'min'  => 3,
                        'max'  => 300
                    ]
            ]
        )
        ->title(__('language_default'))
        ->element('radio', 'lng',
            [
                'checked'     => App::cfg()->sys->lng,
                'description' => __('select_language_help'),
                'items'       => App::languages()->getLngDescription()
            ]
        )
        ->element('checkbox', 'lng_switch',
            [
                'checked'      => App::cfg()->sys->lng_switch,
                'label_inline' => __('allow_choose'),
                'description'  => __('allow_choose_help')
            ]
        );
}

$form
    ->divider()
    ->element('submit', 'submit',
        [
            'value' => __('save'),
            'class' => 'btn btn-primary'
        ]
    )
    ->html('<a class="btn btn-link" href="' . App::router()->getUri() . '">' . __('back') . '</a>');

if ($form->process() === true) {
    // Записываем настройки
    unset($_SESSION['lng']);
    App::cfg()->sys->write($form->output);
    header('Location: ' . App::router()->getUri() . 'users_settings/?saved');
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');