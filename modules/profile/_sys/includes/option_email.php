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

$form->title(__('change_email'));

if (!empty(Users::$data['email'])) {
    $form->element('text', 'oldemail',
        [
            'label'    => __('old_email'),
            'value'    => Users::$data['email'],
            'readonly' => true
        ]
    );
}

$form
    ->element('text', 'email',
        [
            'label'     => __('new_email'),
            'maxlength' => 50
        ]
    )
    ->element('text', 'repeatemail',
        [
            'label'       => __('repeat_email'),
            'maxlength'   => 50,
            'description' => __('description_email')
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
    ->html('<a class="btn btn-link" href="' . App::router()->getUri(3) . '">' . __('back') . '</a>')
    ->validate('email', 'lenght', ['min' => 5, 'max' => 50, 'empty' => true])
    ->validate('email', 'email', ['empty' => true])
    ->validate('repeatemail', 'compare', ['compare_field' => 'email'])
    ->validate('password', 'password', ['continue' => false])
    ->validate('email', 'emailoccupied', ['valid' => true]);

if ($form->process() === true) {
    $stmt = App::db()->prepare("
        UPDATE `" . TP . "user__`
        SET
        `email`    = ?
        WHERE `id` = ?
        ");

    $stmt->execute([$form->output['email'], Users::$data['id']]);
    $stmt = null;

    $form->continueLink = App::router()->getUri(3);
    $form->confirmation = true;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
