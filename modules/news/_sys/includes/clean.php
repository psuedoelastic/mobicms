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
    ->title(__('clear_news'))
    ->element('radio', 'clear',
        [
            'checked' => 1,
            'items'   =>
                [
                    1 => __('clear_month'),
                    2 => __('clear_week'),
                    3 => __('clear_all'),
                ]
        ]
    )
    ->divider()
    ->element('submit', 'submit',
        [
            'value' => __('delete'),
            'class' => 'btn btn-primary'
        ]
    )
    ->html('<a class="btn btn-link" href="' . App::router()->getUri() . '">' . __('cancel') . '</a>');

if ($form->process() === true) {
    switch ($form->output['clear']) {
        case 2:
            // Чистим старше 1 недели
            App::db()->exec("DELETE FROM `" . TP . "news` WHERE `time` <= " . (time() - 604800));
            App::db()->query("OPTIMIZE TABLE `" . TP . "news`");
            break;

        case 3:
            // Удаляем все новости
            App::db()->query("TRUNCATE TABLE `" . TP . "news`");
            break;

        default:
            // Чистим старше 1 месяца
            App::db()->exec("DELETE FROM `" . TP . "news` WHERE `time` <= " . (time() - 2592000));
            App::db()->query("OPTIMIZE TABLE `" . TP . "news`");
    }

    //TODO: Добавить удаление комментариев к новости
    $form->confirmation = true;
    $form->continueLink = App::router()->getUri();
    $form->successMessage = __('clear_success');
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
