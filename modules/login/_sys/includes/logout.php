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

$form = new Mobicms\Form\Form(['action' => App::router()->getUri(1)]);

$form
    ->title(__('exit_warning'))
    ->element('checkbox', 'clear',
        [
            'label_inline' => __('clear_authorisation')
        ]
    )
    ->divider(12)
    ->element('submit', 'submit',
        [
            'value' => '   ' . __('exit') . '   ',
            'class' => 'btn btn-primary btn-lg btn-block'
        ]
    )
    ->html('<br/><a class="btn btn-default btn-lg btn-block" href="' . App::cfg()->sys->homeurl . 'profile/' . App::user()->id . '/">' . __('back') . '</a>');

if ($form->process() === true) {
    App::user()->destroy($form->output['clear']);
    header('Location: ' . App::cfg()->sys->homeurl);
    exit;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('login.php');