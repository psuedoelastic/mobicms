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

$form = new Mobicms\Form\Form(['action' => App::router()->getUri(4)]);

// Подготавливаем список имеющихся языков
$items['#'] = __('select_automatically');
$items = array_merge($items, App::languages()->getLngDescription());

$form
    ->title(__('language'))
    ->element('radio', 'lng',
        [
            'checked' => App::user()->settings['lng'],
            'items'   => $items
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

if ($form->process() === true) {
    foreach ($form->output as $key => $val) {
        App::user()->settings[$key] = $val;
    }

    App::user()->set_data('user_set', App::user()->settings);
    unset($_SESSION['user_set'], $_SESSION['lng']);
    header('Location: ' . $uri . '?saved');
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
