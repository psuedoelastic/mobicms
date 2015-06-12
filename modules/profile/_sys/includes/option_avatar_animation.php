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
//TODO: Доработать!
$form = new Mobicms\Form\Form(['action' => App::router()->getUri(5)]);

$form
    ->title(__('upload_avatar'))
    ->element('hidden', 'MAX_FILE_SIZE', ['value' => (10 * 1024)])
    ->element('file', 'animation',
        [
            'label'       => __('animation'),
            'description' => __('select_animation_help')
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
    $error = [];
    if ($_FILES['animation']['size'] > 0) {
        // Проверка на допустимый вес файла
        if ($_FILES['animation']['size'] > 20480) {
            $error[] = __('error_avatar_filesize');
        }

        $param = getimagesize($_FILES['animation']['tmp_name']);

        // Проверка на допустимый тип файла
        if ($param == false || $param['mime'] != 'image/gif') {
            $error[] = __('error_avatar_filetype');
        }

        // Проверка на допустимый размер изображения
        if ($param != false && ($param[0] != 48 || $param[1] != 48)) {
            $error[] = __('error_avatar_size');
        }

        if (empty($error)) {
            if ((move_uploaded_file($_FILES['animation']['tmp_name'],
                    FILES_PATH . 'users' . DS . 'avatar' . DS . Users::$data['id'] . '.gif')) == true
            ) {
                unlink(FILES_PATH . 'users' . DS . 'avatar' . DS . Users::$data['id'] . '.jpg');

                $stmt = App::db()->prepare("UPDATE `user__` SET `avatar` = ? WHERE `id` = " . App::user()->id);
                $stmt->execute([App::cfg()->sys->homeurl . '/uploads/users/avatar/' . Users::$data['id'] . '.gif']);
                $stmt = null;

                $form->continueLink = App::router()->getUri(4);
                $form->successMessage = __('avatar_uploaded');
                $form->confirmation = true;
                App::view()->hideuser = true;
            } else {
                $error[] = __('error_avatar_upload');
            }
        } else {
            echo $error . ' <a href="' . App::router()->getUri(3) . '?act=avatar_upload&amp;user=' . App::view()->user['id'] . '">' . __('back') . '</a>'; //TODO: Разобраться со ссылкой
        }
    } else {
        // Если не выбран файл
        $error[] = __('error_file_not_selected');
    }

    if (!empty($error)) {
        App::view()->error = implode('<br/>', $error);
    }
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('edit_form.php');
