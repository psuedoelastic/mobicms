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

$form = new Mobicms\Form\Form(['action' => App::router()->getUri(2)]);

if (App::cfg()->sys->usr_reg_allow) {
    $form
        ->title('TMP')
        ->element('text', 'nickname',
            [
                'label'       => __('choose_nickname'),
                'description' => __('login_help'),
                'required'    => true
            ]
        );

    if (App::cfg()->sys->usr_reg_email) {
        $form->element('text', 'email',
            [
                'label'       => __('your_email'),
                'description' => __('description_email'),
                'required'    => true
            ]
        );
    }

    $form
        ->element('password', 'newpass',
            [
                'label'    => __('password'),
                'required' => true
            ]
        )
        ->element('password', 'newconf',
            [
                'label'       => __('repeat_password'),
                'description' => __('password_help'),
                'required'    => true
            ]
        )
        ->element('radio', 'sex',
            [
                'label'   => __('sex'),
                'checked' => 'm',
                'items'   =>
                    [
                        'm' => '<i class="male lg fw"></i>' . __('sex_m'),
                        'w' => '<i class="female lg fw"></i>' . __('sex_w')
                    ]
            ]
        )
        ->divider(8)
        ->captcha()
        ->element('text', 'captcha',
            [
                'label_inline' => __('captcha'),
                'class'        => 'small',
                'maxlenght'    => 5,
                'reset_value'  => '',
            ]
        )
        ->divider()
        ->element('submit', 'submit',
            [
                'value' => __('registration'),
                'class' => 'btn btn-primary'
            ]
        )
        ->html('<a class="btn btn-link" href="' . App::cfg()->sys->homeurl . 'login/">' . __('cancel') . '</a>')
        ->validate('captcha', 'captcha');

    if (App::cfg()->sys->usr_reg_email) {
        $form
            ->validate('email', 'lenght', ['min' => 5, 'max' => 50])
            ->validate('email', 'email');
    }

    $form
        ->validate('nickname', 'lenght', ['min' => 2, 'max' => 20])
        ->validate('nickname', 'nickname')
        ->validate('newpass', 'lenght', ['continue' => false, 'min' => 3])
        ->validate('newconf', 'compare', ['compare_field' => 'newpass', 'error' => __('error_passwords_not_match')])
        ->validate('nickname', 'nickoccupied', ['valid' => true]);

    if ($form->process() === true) {
        $token = Functions::generateToken();
        $stmt = App::db()->prepare("
          INSERT INTO `" . TP . "user__` SET
          `nickname`      = ?,
          `password`      = ?,
          `token`         = ?,
          `email`         = ?,
          `rights`        = 0,
          `level`         = ?,
          `sex`           = ?,
          `join_date`     = ?,
          `last_visit`    = ?,
          `about`         = ?,
          `notifications` = ?,
          `reputation`  = ?
        ");

        $stmt->execute(
            [
                $form->output['nickname'],
                password_hash($form->output['newpass'], PASSWORD_DEFAULT),
                $token,
                (App::cfg()->sys->usr_reg_email ? $form->output['email'] : ''),
                (App::cfg()->sys->usr_reg_allow && !App::cfg()->sys->usr_reg_moderation && !App::cfg()->sys->usr_reg_email ? 1 : 0),
                $form->output['sex'],
                time(),
                time(),
                '',
                '',
                ''
            ]
        );
        //TODO: Добавить подтверждение по Email
        //TODO: Добавить отправку Welcome Message
        //TODO: Добавить страницу с приветствием
        // Запускаем пользователя на сайт
        $userid = App::db()->lastInsertId();
        setcookie('user_id', $userid, time() + 3600 * 24 * 31, '/');
        setcookie('token', $token, time() + 3600 * 24 * 31, '/');
        $_SESSION['user_id'] = $userid;
        $_SESSION['token'] = $token;

        $stmt = null;
        header('Location: ' . App::cfg()->sys->homeurl);
        exit;
    }
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('registration.php');