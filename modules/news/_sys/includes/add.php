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

$form = new Mobicms\Form\Form(['action' => App::router()->getUri(2)]);

$form
    ->title(__('article_add'))
    ->element('text', 'title',
        [
            'label'    => __('article_title'),
            'required' => true
        ]
    )
    ->element('textarea', 'text',
        [
            'label'    => __('text'),
            'editor'   => true,
            'required' => true
        ]
    )
    ->element('checkbox', 'comments',
        [
            'label_inline' => __('enable_comments'),
            'checked'      => true
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

if ($form->process() === true) {
    $stmt = App::db()->prepare("
        INSERT INTO `news` SET
        `time`        = ?,
        `author`      = ?,
        `author_id`   = ?,
        `title`       = ?,
        `text`        = ?,
        `comm_enable` = ?
    ");

    $stmt->execute(
        [
            time(),
            App::user()->data['nickname'],
            App::user()->id,
            App::filter($form->output['title'])->specialchars(),
            App::purify($form->output['text']),
            $form->output['comments']
        ]
    );
    $stmt = null;

    App::db()->query("UPDATE `user__` SET `lastpost` = " . time() . " WHERE `id` = " . App::user()->id);

    header('Location: ' . App::router()->getUri());
    exit;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
