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

// Define list of Editors
$editors[0] = __('text_editor_none');
$editors[1] = '<abbr title="SCeditor">' . __('text_editor_wysiwyg') . '</abbr>&#160; <small><a href="#" title="Under Construction">[' . __('settings') . ']</a></small>';
if (App::user()->rights == 9) {
    $editors[2] = '<abbr title="CodeMirror">' . __('text_editor_html') . '</abbr>';
}

$form
    // Set system settings
    ->title(__('system_settings'))
    ->element('text', 'timeshift',
        [
            'value'        => App::user()->settings['timeshift'],
            'label_inline' => '<span class="badge badge-large">' . date("H:i", time() + (App::cfg()->sys->timeshift + App::cfg()->sys->timeshift) * 3600) . '</span> ' . __('settings_clock'),
            'description'  => __('settings_clock_shift') . ' (+ - 12)',
            'class'        => 'small',
            'maxlength'    => 3,
            'filter'       =>
                [
                    'type' => 'int',
                    'min'  => -12,
                    'max'  => 13
                ]
        ]
    )
    ->element('checkbox', 'direct_url',
        [
            'checked'      => App::user()->settings['direct_url'],
            'label_inline' => __('direct_url')
        ]
    )
    ->element('checkbox', 'avatars',
        [
            'checked'      => App::user()->settings['avatars'],
            'label_inline' => __('avatars')
        ]
    )
    ->element('checkbox', 'smilies',
        [
            'checked'      => App::user()->settings['smilies'],
            'label_inline' => __('smilies')
        ]
    )
    // Choose text editor
    ->title(__('text_editor'))
    ->element('radio', 'editor',
        [
            'checked' => App::user()->settings['editor'],
            'items'   => $editors
        ]
    )
    // Set apperance
    ->title(__('apperance'))
    ->element('text', 'page_size',
        [
            'value'        => App::user()->settings['page_size'],
            'label_inline' => __('list_size'),
            'description'  => __('list_size_help') . ' (5-99)',
            'class'        => 'small',
            'maxlength'    => 2,
            'filter'       =>
                [
                    'type' => 'int',
                    'min'  => 5,
                    'max'  => 99
                ]
        ]
    )
    ->element('text', 'field_h',
        [
            'value'        => App::user()->settings['field_h'],
            'label_inline' => __('field_height'),
            'description'  => __('field_height_help') . ' (2-9)',
            'class'        => 'small',
            'maxlength'    => 1,
            'filter'       =>
                [
                    'type' => 'int',
                    'min'  => 2,
                    'max'  => 9
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
    ->html('<a class="btn btn-link" href="' . App::router()->getUri(3) . '">' . __('back') . '</a>');

if ($form->process() === true) {
    foreach ($form->output as $key => $val) {
        App::user()->settings[$key] = $val;
    }

    App::user()->set_data('user_set', App::user()->settings);
    unset($_SESSION['user_set'], $_SESSION['lng']);
    header('Location: ' . App::router()->getUri(4) . '?saved');
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
