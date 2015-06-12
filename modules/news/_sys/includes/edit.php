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
    $stmt = App::db()->query("SELECT * FROM `news` WHERE `id` = " . $id);
    if ($stmt->rowCount()) {
        $result = $stmt->fetch();

        $form
            ->title(__('article_edit'))
            ->element('text', 'title',
                [
                    'label'    => __('article_title'),
                    'value'    => $result['title'],
                    'required' => true
                ]
            )
            ->element('textarea', 'text',
                [
                    'label'    => __('text'),
                    'value'    => $result['text'],
                    'editor'   => true,
                    'required' => true
                ]
            )
            ->element('checkbox', 'comments',
                [
                    'label_inline' => __('enable_comments'),
                    'checked'      => $result['comm_enable']
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

        $form
            ->validate('title', 'lenght', ['min' => 3, 'max' => 100])
            ->validate('text', 'lenght', ['min' => 3]);
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
    $stmt = App::db()->prepare("
        UPDATE `news` SET
        `title`       = ?,
        `text`        = ?,
        `comm_enable` = ?
        WHERE `id`    = ?
    ");

    $stmt->execute(
        [
            App::filter($form->output['title'])->specialchars(),
            App::purify($form->output['text']),
            $form->output['comments'],
            $id
        ]
    );
    $stmt = null;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
