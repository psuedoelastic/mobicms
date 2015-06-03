<?php
/*
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

if (App::user()->rights == 9 || (App::user()->rights == 7 && App::user()->rights > Users::$data['rights'])) {
    $items =
        [
            0 => __('rank_0'),
            3 => __('rank_3'),
            4 => __('rank_4'),
            5 => __('rank_5'),
            6 => __('rank_6')
        ];

    if (App::user()->rights == 9 || (App::user()->rights == 7 && Users::$data['id'] == App::user()->id)) {
        $items['7'] = '<i class="icn-shield"></i>' . __('rank_7');
    }

    if (App::user()->rights == 9) {
        $items['9'] = '<i class="icn-shield-red"></i>' . __('rank_9');
    }

    $form = new Mobicms\Form\Form(['action' => App::router()->getUri(4)]);

    $form
        ->title(__('rank'))
        ->element('radio', 'rights',
            [
                'checked' => Users::$data['rights'],
                'items'   => $items
            ]
        )
        ->element('password', 'password',
            [
                'label'    => __('your_password'),
                'required' => true
            ]
        )
        ->divider()
        ->element('submit', 'submit',
            [
                'value' => __('save'),
                'class' => 'btn btn-primary'
            ]
        )
        ->html('<a class="btn btn-link" href="' . App::router()->getUri(3) . '">' . __('back') . '</a>');

    // Валидация данных
    $form->validate('password', 'password');

    if ($form->process() === true) {
        App::db()->exec("UPDATE `" . TP . "user__` SET `rights` = '" . intval($form->output['rights']) . "' WHERE `id` = " . Users::$data['id']);

        if (Users::$data['id'] == App::user()->id) {
            header('Location: ' . App::router()->getUri(3));
            exit;
        }
    }

    App::view()->admin = true;
    App::view()->setRawVar('form', $form->display());
    App::view()->setTemplate('edit_form.php');
} else {
    App::view()->message = __('access_forbidden');
    App::view()->back = App::router()->getUri(3);
    App::view()->contents = App::view()->fetchTemplate('message'); //TODO: Доработать
}
