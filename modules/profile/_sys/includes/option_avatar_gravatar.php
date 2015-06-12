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

$form = new Mobicms\Form\Form(['action' => App::router()->getUri(5)]);

$form
    ->title(__('set_gravatar'))
    ->element('text', 'email',
        [
            'label'       => 'Email',
            'description' => __('gravatar_help'),
            'required'    => true
        ]
    )
    ->divider()
    ->element('submit', 'submit',
        [
            'value' => __('save'),
            'class' => 'btn btn-primary'
        ]
    )
    ->html('<a class="btn btn-link" href="' . App::router()->getUri(4) . '">' . __('back') . '</a>');

if ($form->process() === true) {
    $default = 'http://johncms.com/images/empty.png'; //TODO: Установить изображение по-умолчанию
    $stmt = App::db()->prepare("UPDATE `user__` SET `avatar` = ? WHERE `id` = " . App::user()->id);
    $stmt->execute(['http://www.gravatar.com/avatar/' . md5(strtolower(trim($form->output['email']))) . '?d=' . urlencode($default) . '&s=48']);
    $stmt = null;

    @unlink(FILES_PATH . 'users' . DS . 'avatar' . DS . Users::$data['id'] . '.jpg');
    @unlink(FILES_PATH . 'users' . DS . 'avatar' . DS . Users::$data['id'] . '.gif');

    $form->continueLink = App::router()->getUri(4);
    $form->successMessage = __('avatar_applied');
    $form->confirmation = true;
    App::view()->hideuser = true;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
