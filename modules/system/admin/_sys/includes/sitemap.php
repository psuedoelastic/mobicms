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
    ->title(__('lng_on'))
    ->element('checkbox', 'sitemap_forum',
        [
            'label_inline' => __('sitemap_forum'),
            'checked'      => App::cfg()->sys->sitemap_forum
        ]
    )
    ->element('checkbox', 'sitemap_library',
        [
            'label_inline' => __('sitemap_library'),
            'checked'      => App::cfg()->sys->sitemap_library
        ]
    )
    ->title(__('users'))
    ->element('radio', 'sitemap_users',
        [
            'checked' => App::cfg()->sys->sitemap_users,
            'items'   =>
                [
                    '1' => __('show_all'),
                    '0' => __('show_only_guests')
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

if ($form->process() === true) {
    // Записываем настройки
    App::cfg()->sys->write($form->output);
    App::view()->save = true;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');