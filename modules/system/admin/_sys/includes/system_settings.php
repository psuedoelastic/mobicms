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
    ->title(__('system_time'))
    ->element('text', 'timeshift',
        [
            'value'        => App::cfg()->sys->timeshift,
            'class'        => 'small',
            'label_inline' => '<span class="badge badge-green">' . date("H:i", time() + App::cfg()->sys->timeshift * 3600) . '</span> ' . __('time_shift') . ' <span class="note">(+ - 12)</span>',
            'filter'       =>
                [
                    'type' => 'int',
                    'min'  => -12,
                    'max'  => 13
                ]
        ]
    )
    ->title(__('file_upload'))
    ->element('text', 'filesize',
        [
            'value'        => App::cfg()->sys->filesize,
            'label_inline' => __('file_maxsize') . ' kB <span class="note">(100-50000)</span>',
            'description'  => __('filesize_note'),
            'class'        => 'small',
            'filter'       =>
                [
                    'type' => 'int',
                    'min'  => 100,
                    'max'  => 50000
                ]
        ]
    )
    ->title(__('profiling'))
    ->element('checkbox', 'profiling_generation',
        [
            'checked'      => App::cfg()->sys->profiling_generation,
            'label_inline' => __('profiling_generation')
        ]
    )
    ->element('checkbox', 'profiling_memory',
        [
            'checked'      => App::cfg()->sys->profiling_memory,
            'label_inline' => __('profiling_memory')
        ]
    )
    ->title(__('site_details'))
    ->element('text', 'email',
        [
            'value' => App::cfg()->sys->email,
            'label' => __('site_email')
        ]
    )
    ->element('textarea', 'copyright',
        [
            'value' => App::cfg()->sys->copyright,
            'label' => __('site_copyright')
        ]
    )
    ->title(__('seo_attributes'))
    ->element('text', 'home_title',
        [
            'value'       => App::cfg()->sys->home_title,
            'style'       => 'max-width: none',
            'label'       => __('homepage_title'),
            'description' => __('homepage_title_help')
        ]
    )
    ->element('textarea', 'meta_key',
        [
            'value'       => App::cfg()->sys->meta_key,
            'label'       => 'META Keywords',
            'description' => __('keywords_note'),
            'filter'      =>
                [
                    'type' => 'str',
                    'max'  => 250
                ]
        ]
    )
    ->element('textarea', 'meta_desc',
        [
            'value'       => App::cfg()->sys->meta_desc,
            'label'       => 'META Description',
            'description' => __('description_note'),
            'filter'      =>
                [
                    'type' => 'str',
                    'max'  => 250
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
    header('Location: ' . App::router()->getUri() . 'system_settings/?saved');
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');