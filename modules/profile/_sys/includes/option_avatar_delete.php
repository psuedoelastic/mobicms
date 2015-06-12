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
    ->title(__('delete_avatar'))
    ->html('<p>' . __('delete_avatar_warning') . '</p>')
    ->divider()
    ->element('submit', 'submit',
        [
            'value' => __('delete'),
            'class' => 'btn btn-primary'
        ]
    )
    ->html('<a class="btn btn-link" href="' . App::router()->getUri(4) . '">' . __('cancel') . '</a>');

if ($form->process() === true) {
    App::db()->exec("UPDATE `user__` SET `avatar` = '' WHERE `id` = " . App::user()->id);
    @unlink(FILES_PATH . 'users' . DS . 'avatar' . DS . Users::$data['id'] . '.jpg');
    @unlink(FILES_PATH . 'users' . DS . 'avatar' . DS . Users::$data['id'] . '.gif');

    $form->continueLink = App::router()->getUri(4);
    $form->successMessage = __('avatar_deleted');
    $form->confirmation = true;
    App::view()->hideuser = true;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
