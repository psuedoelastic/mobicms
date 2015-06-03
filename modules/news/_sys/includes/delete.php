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

$id = abs(intval(App::request()->getQuery('id', 0)));

$form = new Mobicms\Form\Form(['action' => App::router()->getUri(2) . ($id ? '?id=' . $id : '')]);

if ($id) {
    $stmt = App::db()->query("SELECT * FROM `" . TP . "news` WHERE `id` = " . $id);
    if ($stmt->rowCount()) {
        $form
            ->title(__('article_delete'))
            ->divider()
            ->element('submit', 'submit',
                [
                    'value' => __('delete'),
                    'class' => 'btn btn-primary'
                ]
            )
            ->html('<a class="btn btn-link" href="' . App::router()->getUri() . '">' . __('cancel') . '</a>');
    } else {
        $form
            ->html('<div class="alert alert-danger">' . __('error_wrong_data') . '</div>')
            ->html('<a class="btn btn-link" href="' . App::router()->getUri() . '">' . __('back') . '</a>');
    }
} else {
    $form
        ->html('<div class="alert alert-danger">' . __('error_wrong_data') . '</div>')
        ->html('<a class="btn btn-link" href="' . App::router()->getUri() . '">' . __('back') . '</a>');
}

if ($form->process() === true) {
    App::db()->exec("DELETE FROM `" . TP . "news` WHERE `id` = " . $id);
    //TODO: Добавить удаление комментариев к новости
    $form->confirmation = true;
    $form->continueLink = App::router()->getUri();
    $form->successMessage = __('article_deleted');
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
