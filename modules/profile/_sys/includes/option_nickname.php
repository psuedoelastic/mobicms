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
$form->title(__('change_nickname'));

if (App::user()->rights >= 7 || (Users::$data['change_time']) < time() - (App::cfg()->sys->usr_change_nickname_period * 86400)) {
    $form
        ->element('text', 'nickname',
            [
                'label'     => __('new_nickname'),
                'maxlength' => 20,
                'required'  => true
            ]
        )
        ->element('text', 'repeat',
            [
                'label'       => __('repeat_nickname'),
                'maxlength'   => 20,
                'description' => __('login_help') . '<br/>' . __('change_nickname_help') . ' ' . App::cfg()->sys->usr_change_nickname_period . ' ' . __('days'),
                'required'    => true
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
        ->validate('nickname', 'lenght', ['min' => 2, 'max' => 20])
        ->validate('nickname', 'nickname')
        ->validate('repeat', 'compare', ['compare_field' => 'nickname'])
        ->validate('password', 'password', ['continue' => false])
        ->validate('nickname', 'nickoccupied', ['valid' => true]);
} else {
    $form
        ->html('<div class="alert">' .
            __('change_nickname_note1') . ' ' . App::cfg()->sys->usr_change_nickname_period . ' ' . __('days') . '<br/><br/>' .
            __('change_nickname_note2') . ' ' . Functions::displayDate(Users::$data['change_time']) . '<br/>' .
            __('change_nickname_note3') . ' ' . Functions::displayDate(Users::$data['change_time'] + (App::cfg()->sys->usr_change_nickname_period * 86400)) .
            '</div>')
        ->html('<a class="btn btn-link" href="' . App::router()->getUri(3) . '">' . __('back') . '</a>');
}

if ($form->process() === true) {
    $stmt = App::db()->prepare("
      UPDATE `" . TP . "user__` SET
      `nickname`    = ?,
      `change_time` = ?
      WHERE `id`    = ?
    ");

    $stmt->execute([$form->output['nickname'], time(), Users::$data['id']]);
    $stmt = null;

    Users::$data['nickname'] = $form->output['nickname'];
    $form->continueLink = App::router()->getUri(3);
    $form->successMessage = __('change_nickname_confirm');
    $form->confirmation = true;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
