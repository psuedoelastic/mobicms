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

use Mobicms\Form\Validate;

defined('MOBICMS') or die('Error: restricted access');

$form = new Mobicms\Form\Form(['action' => App::router()->getUri()]);

$form
    ->element('text', 'login',
        [
            'label'    => __('login_caption'),
            'class'    => 'relative largetext',
            'required' => true
        ]
    )
    ->element('password', 'password',
        [
            'label'    => __('password'),
            'class'    => 'relative largetext',
            'required' => true
        ]
    )
    ->element('checkbox', 'remember',
        [
            'checked'      => true,
            'label_inline' => __('remember')
        ]
    )
    ->divider(12)
    ->element('submit', 'submit',
        [
            'value' => __('login'),
            'class' => 'btn btn-primary btn-lg btn-block'
        ]
    )
    ->html('<br/><a class="btn btn-default" href="#">' . __('forgotten_password') . '</a>')
    ->validate('login', 'lenght', ['min' => 2, 'max' => 20])
    ->validate('password', 'lenght', ['min' => 3])
    ->validate('login', 'login', ['valid' => true])
    ->validate('password', 'password', ['valid' => true]);

$form->process();

if ($form->isSubmitted && ($user = Validate::getUserData()) !== false && $user['login_try'] > 2) {
    // Обрабатываем CAPTCHA
    $captcha = true;
    if (isset($_POST['captcha'])) {
        $code = mb_strtolower(trim($_POST['captcha']));
        if (isset($_SESSION['captcha'])
            && mb_strlen($code) > 2
            && mb_strlen($code) < 6
            && $code == mb_strtolower($_SESSION['captcha'])
        ) {
            $captcha = false;
        } else {
            App::view()->error = __('error_wrong_captcha');
        }
    }

    if ($captcha) {
        // Показываем форму CAPTCHA
        App::view()->data = $form->output;
        App::view()->form_token = $_SESSION['form_token'];
        App::view()->setTemplate('login_captcha.php');
        exit;
    }
}

// Авторизуем пользователя
if ($form->isValid && ($user = Validate::getUserData()) !== false) {
    if (empty($user['token'])) {
        $user['token'] = Functions::generateToken();
    }

    $stmt = App::db()->prepare("
      UPDATE `user__` SET
      `login_try` = 0,
      `token` = ?
      WHERE `id` = ?
    ");

    $stmt->execute(
        [
            $user['token'],
            $user['id']
        ]
    );
    $stmt = null;

    if (isset($_POST['remember'])) {
        setcookie('user_id', $user['id'], time() + 3600 * 24 * 31, '/');
        setcookie('token', $user['token'], time() + 3600 * 24 * 31, '/');
    }
    $_SESSION['token'] = $user['token'];
    $_SESSION['user_id'] = $user['id'];

    header('Location: ' . App::cfg()->sys->homeurl);
    exit;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('login.php');