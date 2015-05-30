<?php
/**
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
define('ROOT_DIR', '.');

$form = new Mobicms\Form\Form(['action' => App::router()->getUri(2)]);
$form->infoMessages = false;

$form
    ->title(__('antispy'))
    ->element('radio', 'mode',
        [
            'checked' => 1,
            'items'   =>
                [
                    '1' => __('antispy_dist_scan'),
                    '2' => __('antispy_snapshot_scan'),
                    '3' => __('antispy_snapshot_create')
                ]
        ]
    )
    ->divider()
    ->element('submit', 'submit',
        [
            'value' => __('do'),
            'class' => 'btn btn-primary'
        ]
    )
    ->html('<a class="btn btn-link" href="' . App::router()->getUri() . '">' . __('back') . '</a>');

if ($form->process() === true) {
    require_once dirname(__DIR__) . '/classes/Scanner.php';
    $scanner = new Scanner;

    switch ($form->output['mode']) {
        case 1:
            // Сканируем на соответствие дистрибутиву
            $scanner->scan();
            if (count($scanner->modifiedFiles) || count($scanner->missingFiles) || count($scanner->newFiles)) {
                App::view()->modifiedFiles = $scanner->modifiedFiles;
                App::view()->missingFiles = $scanner->missingFiles;
                App::view()->extraFiles = $scanner->newFiles;
                App::view()->errormsg = __('antispy_dist_inconsistency');
            } else {
                App::view()->ok = __('antispy_dist_scan_good');
            }
            break;

        case 2:
            // Сканируем на соответствие ранее созданному снимку
            $scanner->scan(true);
            if (count($scanner->whiteList) == 0) {
                App::view()->errormsg = __('antispy_no_snapshot');
            } else {
                if (count($scanner->modifiedFiles) || count($scanner->missingFiles) || count($scanner->newFiles)) {
                    App::view()->modifiedFiles = $scanner->modifiedFiles;
                    App::view()->missingFiles = $scanner->missingFiles;
                    App::view()->extraFiles = $scanner->newFiles;
                    App::view()->errormsg = __('antispy_snp_inconsistency');
                } else {
                    App::view()->ok = __('antispy_snapshot_scan_ok');
                }
            }
            break;

        case 3:
            // Создаем снимок файлов
            $scanner->snap();
            App::view()->ok = __('antispy_snapshot_create_ok');
            break;
    }
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('scanner.php');