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

$form
    ->title(__('change_password'))
    ->element('password', 'oldpass',
        [
            'label'    => (Users::$data['id'] == App::user()->id ? __('old_password') : __('your_password')),
            'required' => true
        ]
    )
    ->element('password', 'newpass',
        [
            'label'       => __('new_password'),
            'description' => __('password_change_help'),
            'required'    => true
        ]
    )
    ->element('password', 'newconf',
        [
            'label'    => __('repeat_password'),
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
    ->validate('oldpass', 'lenght', ['continue' => false, 'min' => 3])
    ->validate('oldpass', 'password', ['continue' => false])
    ->validate('newpass', 'lenght', ['continue' => false, 'min' => 3])
    ->validate('newconf', 'compare', ['compare_field' => 'newpass', 'error' => __('error_passwords_not_match')]);

if ($form->process() === true) {
    $token = Functions::generateToken();
    $stmt = App::db()->prepare("
      UPDATE `" . TP . "user__` SET
      `password` = ?,
      `token`    = ?
      WHERE `id` = ?
    ");

    $stmt->execute(
        [
            password_hash($form->output['newpass'], PASSWORD_DEFAULT),
            $token,
            Users::$data['id']
        ]
    );
    $stmt = null;

    if (App::user()->id == Users::$data['id']) {
        setcookie('token', $token, time() + 3600 * 24 * 31, '/');
        $_SESSION['token'] = $token;
    }

    $form->continueLink = App::router()->getUri(3);
    $form->successMessage = __('change_password_confirm');
    $form->confirmation = true;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
