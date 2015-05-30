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

$form = new Mobicms\Form\Form(['action' => App::router()->getUri(4)]);

$form->title(__('profile_edit'));

if (App::cfg()->sys->usr_change_status) {
    $form
        ->html('<div class="form-group">')
        ->element('text', 'status',
            [
                'label'       => __('status'),
                'value'       => Users::$data['status'],
                'description' => __('status_lenght')
            ]
        )
        ->html('</div>');
}

$form
    ->element('text', 'imname',
        [
            'label'       => __('name'),
            'value'       => Users::$data['imname'],
            'description' => __('description_name')
        ]
    );

if (App::cfg()->sys->usr_change_sex || App::user()->rights >= 7) {
    $form
        ->element('radio', 'sex',
            [
                'label'   => __('sex'),
                'checked' => Users::$data['sex'],
                'items'   =>
                    [
                        'm' => __('sex_m'),
                        'w' => __('sex_w')
                    ]
            ]
        );
}

$form
    ->element('text', 'day',
        [
            'label' => __('birthday'),
            'value' => date("d", strtotime(Users::$data['birth'])),
            'class' => 'mini'
        ]
    )
    ->element('text', 'month',
        [
            'value' => date("m", strtotime(Users::$data['birth'])),
            'class' => 'mini'
        ]
    )
    ->element('text', 'year',
        [
            'value'       => date("Y", strtotime(Users::$data['birth'])),
            'class'       => 'small',
            'description' => __('description_birth')
        ]
    )
    ->element('text', 'live',
        [
            'label'       => __('live'),
            'value'       => Users::$data['live'],
            'description' => __('description_live')
        ]
    )
    ->element('textarea', 'about',
        [
            'label'       => __('about'),
            'value'       => Users::$data['about'],
            'editor'      => true,
            'description' => __('description_about')
        ]
    )
    ->element('text', 'tel',
        [
            'label'       => __('phone_number'),
            'value'       => Users::$data['tel'],
            'description' => __('description_phone_number')
        ]
    )
    ->element('text', 'siteurl',
        [
            'label'       => __('site'),
            'value'       => Users::$data['siteurl'],
            'description' => __('description_siteurl')
        ]
    );

if (!empty(Users::$data['email'])) {
    $form
        ->element('text', 'email',
            [
                'label'    => 'Email',
                'value'    => Users::$data['email'],
                'readonly' => true
            ]
        )
        ->element('checkbox', 'mailvis',
            [
                'label_inline' => __('show_in_profile'),
                'checked'      => Users::$data['mailvis'],
                'description'  => __('description_email') . '<br/><a href="' . App::router()->getUri(3) . 'email/">' . __('change_email') . '</a>'
            ]
        );
}

$form
    ->element('text', 'skype',
        [
            'label'       => 'Skype',
            'value'       => Users::$data['skype'],
            'description' => __('description_skype')
        ]
    )
    ->element('text', 'icq',
        [
            'label'       => 'ICQ',
            'value'       => Users::$data['icq'],
            'description' => __('description_icq')
        ]
    )
    ->divider()
    ->element('submit', 'submit',
        [
            'value' => __('save'),
            'class' => 'btn btn-primary'
        ]
    )
    ->html('<a class="btn btn-link" href="' . App::router()->getUri(3) . '">' . __('back') . '</a>')
    ->validate('status', 'lenght', ['min' => 3, 'max' => 50, 'empty' => true])
    ->validate('imname', 'lenght', ['max' => 50])
    ->validate('live', 'lenght', ['max' => 100])
    ->validate('about', 'lenght', ['max' => 5000])
    ->validate('tel', 'lenght', ['max' => 100])
    ->validate('siteurl', 'lenght', ['max' => 100])
    ->validate('skype', 'lenght', ['max' => 50])
    ->validate('icq', 'numeric', ['min' => 10000, 'empty' => true]);

if ($form->process() === true) {
    foreach ($form->output as $key => $val) {
        Users::$data[$key] = $val;
    }

    // Принимаем и обрабатываем дату рожденья
    if (empty($form->output['day'])
        && empty($form->output['month'])
        && empty($form->output['year'])
    ) {
        // Удаляем дату рожденья
        Users::$data['birth'] = '00-00-0000';
    } else {
        Users::$data['birth'] = intval($form->output['year']) . '-' . intval($form->output['month']) . '-' . intval($form->output['day']);
    }

    //TODO: Добавить валидацию даты

    $stmt = App::db()->prepare("
        UPDATE `" . TP . "user__`
        SET
        `status`   = ?,
        `sex`      = ?,
        `imname`   = ?,
        `birth`    = ?,
        `live`     = ?,
        `about`    = ?,
        `tel`      = ?,
        `siteurl`  = ?,
        `mailvis`  = ?,
        `icq`      = ?,
        `skype`    = ?
        WHERE `id` = ?
        ");

    $stmt->execute(
        [
            App::filter(Users::$data['status'])->specialchars(),
            Users::$data['sex'],
            App::filter(Users::$data['imname']),
            Users::$data['birth'],
            App::filter(Users::$data['live']),
            App::purify(Users::$data['about']),
            App::filter(Users::$data['tel']),
            App::filter(Users::$data['siteurl']),
            Users::$data['mailvis'],
            App::filter(Users::$data['icq']),
            App::filter(Users::$data['skype']),
            Users::$data['id']
        ]
    );
    $stmt = null;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
