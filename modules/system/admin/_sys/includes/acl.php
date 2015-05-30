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
    ->title(__('forum'))
    ->element('radio', 'acl_forum',
        [
            'checked' => App::cfg()->sys->acl_forum,
            'items'   =>
                [
                    '2' => __('access_enabled'),
                    '1' => __('access_authorised'),
                    '3' => __('read_only'),
                    '0' => __('access_disabled')
                ]
        ]
    )
    ->title(__('guestbook'))
    ->element('radio', 'acl_guestbook',
        [
            'checked' => App::cfg()->sys->acl_guestbook,
            'items'   =>
                [
                    '2' => __('access_enabled_for_guests'),
                    '1' => __('access_enabled'),
                    '0' => __('access_disabled')
                ]
        ]
    )
    ->title(__('library'))
    ->element('radio', 'acl_library',
        [
            'checked' => App::cfg()->sys->acl_library,
            'items'   =>
                [
                    '2' => __('access_enabled'),
                    '1' => __('access_authorised'),
                    '0' => __('access_disabled')
                ]
        ]
    )
    ->title(__('downloads'))
    ->element('radio', 'acl_downloads',
        [
            'checked' => App::cfg()->sys->acl_downloads,
            'items'   =>
                [
                    '2' => __('access_enabled'),
                    '1' => __('access_authorised'),
                    '0' => __('access_disabled')
                ]
        ]
    )
    ->element('checkbox', 'acl_downloads_comm',
        [
            'label_inline' => __('comments'),
            'checked'      => App::cfg()->sys->acl_downloads_comm
        ]
    )
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
    App::cfg()->sys->write($form->output);
    App::view()->save = true;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');