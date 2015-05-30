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

$form = new Mobicms\Form\Form(['action' => App::router()->getUri(5)]);

$form
    ->title(__('upload_avatar'))
    ->element('hidden', 'MAX_FILE_SIZE', ['value' => (App::cfg()->sys->filesize * 1024)])
    ->element('file', 'image',
        [
            'label'       => __('select_image'),
            'description' => __('select_avatar_help')
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
    if ($_FILES['image']['size'] > 0) {
        require_once ROOT_PATH . 'system/third-party/class.upload/class.upload.php';
        $handle = new upload($_FILES['image']);
        if ($handle->uploaded) {
            // Обрабатываем фото
            $handle->file_new_name_body = Users::$data['id'];
            $handle->allowed =
                [
                    'image/jpeg',
                    'image/gif',
                    'image/png'
                ];
            $handle->file_max_size = App::cfg()->sys->filesize * 1024;
            $handle->file_overwrite = true;
            $handle->image_resize = true;
            $handle->image_x = 48;
            $handle->image_y = 48;
            $handle->image_convert = 'jpg';
            $handle->process(FILES_PATH . 'users' . DS . 'avatar' . DS);
            if ($handle->processed) {
                unlink(FILES_PATH . 'users' . DS . 'avatar' . DS . Users::$data['id'] . '.gif');

                $stmt = App::db()->prepare("UPDATE `" . TP . "user__` SET `avatar` = ? WHERE `id` = " . App::user()->id);
                $stmt->execute([App::cfg()->sys->homeurl . '/uploads/users/avatar/' . Users::$data['id'] . '.jpg']);
                $stmt = null;

                $form->continueLink = App::router()->getUri(4);
                $form->successMessage = __('avatar_uploaded');
                $form->confirmation = true;
                App::view()->hideuser = true;
            } else {
                $error[] = ($handle->error);
            }
            $handle->clean();
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
