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

$themes = App::cfg()->sys->getThemesList();

if (trim(App::request()->getQuery('act', '')) == 'set' && isset($themes[App::request()->getQuery('mod')])) {
    $theme = $themes[App::request()->getQuery('mod')];
    $description = '<br/><dl class="description">' .
        '<dt class="wide"><img src="' . $themes[App::request()->getQuery('mod')]['thumbinal'] . '" alt=""/></dt>' .
        '<dd>' .
        '<div class="header">' . $theme['name'] . '</div>' .
        (!empty($theme['author']) ? '<strong>' . __('author') . '</strong>: ' . htmlspecialchars($theme['author']) : '') .
        (!empty($theme['author_url']) ? '<br/><strong>' . __('site') . '</strong>: ' . htmlspecialchars($theme['author_url']) : '') .
        (!empty($theme['author_email']) ? '<br/><strong>Email</strong>: ' . htmlspecialchars($theme['author_email']) : '') .
        (!empty($theme['description']) ? '<br/><strong>' . __('description') . '</strong>: ' . htmlspecialchars($theme['description']) : '') .
        '</dd></dl>';

    $form = new Mobicms\Form\Form(['action' => App::router()->getUri(4)]);
    $form
        ->title(__('set_theme'))
        ->html($description)
        ->divider()
        ->element('submit', 'submit',
            [
                'value' => __('set'),
                'class' => 'btn btn-primary'
            ]
        )
        ->html('<a class="btn btn-link" href="' . App::router()->getUri(4) . '">' . __('back') . '</a>');

    if ($form->process() === true) {
//        $stmt = App::db()->prepare("UPDATE `" . TP . "user__` SET `avatar` = ? WHERE `id` = " . App::user()->id);
//        $stmt->execute([$image]);
//        $stmt = null;
//
//        @unlink(FILES_PATH . 'users' . DS . 'avatar' . DS . Users::$data['id'] . '.jpg');
//        @unlink(FILES_PATH . 'users' . DS . 'avatar' . DS . Users::$data['id'] . '.gif');
//
//        $form->continueLink = App::cfg()->sys->homeurl . 'profile/' . App::user()->id . '/option/avatar/';
//        $form->successMessage = __('avatar_applied');
//        $form->confirmation = true;
//        App::view()->hideuser = true;
    }

    App::view()->setRawVar('form', $form->display());
    App::view()->setTemplate('option_theme_set.php');
} else {
    App::view()->tpl_list = $themes;
    App::view()->setTemplate('option_theme.php');
}

