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

//TODO: Переделать на получение настроек из таблицы модулей
$settings = isset(App::cfg()->sys->news)
    ? unserialize(App::cfg()->sys->news)
    : [
        'view'     => 1,
        'breaks'   => 1,
        'smilies'  => 1,
        'tags'     => 1,
        'comments' => 1,
        'size'     => 500,
        'quantity' => 3,
        'days'     => 7
    ];

$form = new Mobicms\Form\Form(['action' => App::router()->getUri(2)]);

$form
    ->title(__('news_on_frontpage'))
    ->element('radio', 'view',
        [
            'checked' => $settings['view'],
            'items'   =>
                [
                    '1' => __('heading_and_text'),
                    '2' => __('heading'),
                    '3' => __('text'),
                    '0' => __('dont_display')
                ]
        ]
    )
    ->title('')
    ->element('checkbox', 'breaks',
        [
            'label_inline' => __('line_foldings'),
            'checked'      => $settings['breaks']
        ]
    )
    ->element('checkbox', 'smilies',
        [
            'label_inline' => __('smilies'),
            'checked'      => $settings['smilies']
        ]
    )
    ->element('checkbox', 'tags',
        [
            'label_inline' => __('bbcode'),
            'checked'      => $settings['tags']
        ]
    )
    ->element('checkbox', 'comments',
        [
            'label_inline' => __('comments'),
            'checked'      => $settings['comments']
        ]
    )
    ->title('')
    ->element('text', 'quantity',
        [
            'label_inline' => __('news_count') . ' <span class="note">(1 - 15)</span>',
            'value'        => $settings['quantity'],
            'maxlength'    => '2',
            'class'        => 'small',
            'filter'       =>
                [
                    'type' => 'int',
                    'min'  => 1,
                    'max'  => 15
                ]
        ]
    )
    ->element('text', 'days',
        [
            'label_inline' => __('news_howmanydays_display') . ' <span class="note">(1 - 30)</span>',
            'value'        => $settings['days'],
            'maxlength'    => '2',
            'class'        => 'small',
            'filter'       =>
                [
                    'type' => 'int',
                    'min'  => 1,
                    'max'  => 30
                ]
        ]
    )
    ->element('text', 'size',
        [
            'label_inline' => __('text_size') . ' <span class="note">(100 - 5000)</span>',
            'value'        => $settings['size'],
            'maxlength'    => '4',
            'class'        => 'small',
            'filter'       =>
                [
                    'type' => 'int',
                    'min'  => 100,
                    'max'  => 5000
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
    ->html('<a class="btn btn-link" href="' . App::router()->getUri() . '">' . __('back') . '</a>');

$form->process();

if ($form->isValid && isset($form->input['submit'])) {
    //TODO: Написать сохранение настроек новостей
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
