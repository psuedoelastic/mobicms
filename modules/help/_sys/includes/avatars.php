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

$catalog = [];
foreach (glob(ROOT_PATH . 'assets' . DS . 'avatars' . DS . '*', GLOB_ONLYDIR) as $val) {
    $dir = basename($val);
    $catalog[$dir] = __($dir);
}
asort($catalog);

$uri = App::router()->getUri(2);
$query = App::router()->getQuery();
App::view()->pagesize = 40;

if (App::user()->id && isset($query[1], $query[2], $catalog[$query[2]]) && $query[1] == 'set') {
    // Устанавливаем аватар в анкету
    $form = new Mobicms\Form\Form(['action' => App::router()->getUri(4) . $query[3]]);
    $image = App::cfg()->sys->homeurl . 'assets/avatars/' . urlencode($query[2]) . '/' . urlencode($query[3]);

    $form
        ->title(__('set_avatar'))
        ->html('<br/><div class="avatars-list" style="float: left; margin-right: 12px"><img src="' . $image . '" alt=""/></div><br/>' . __('set_avatar_warning'))
        ->divider()
        ->element('submit', 'submit',
            [
                'value' => __('save'),
                'class' => 'btn btn-primary'
            ]
        )
        ->html('<a class="btn btn-link" href="' . App::router()->getUri(2) . 'list/' . \App::router()->getQuery(2) . '">' . __('back') . '</a>');

    if ($form->process() === true) {
        $stmt = App::db()->prepare("UPDATE `user__` SET `avatar` = ? WHERE `id` = " . App::user()->id);
        $stmt->execute([$image]);
        $stmt = null;

        @unlink(FILES_PATH . 'users' . DS . 'avatar' . DS . Users::$data['id'] . '.jpg');
        @unlink(FILES_PATH . 'users' . DS . 'avatar' . DS . Users::$data['id'] . '.gif');

        $form->continueLink = App::cfg()->sys->homeurl . 'profile/' . App::user()->id . '/option/avatar/';
        $form->successMessage = __('avatar_applied');
        $form->confirmation = true;
        App::view()->hideuser = true;
    }

    App::view()->setRawVar('form', $form->display());
    App::view()->setTemplate('avatars_set.php');
} elseif (isset($query[1], $query[2], $catalog[$query[2]]) && $query[1] == 'list') {
    // Показываем список аватаров в выбранной категории
    $avatars = glob(ROOT_PATH . 'assets' . DS . 'avatars' . DS . $query[2] . DS . '*.{gif,jpg,png}', GLOB_BRACE);

    App::view()->total = count($avatars);
    App::view()->start = App::vars()->page * App::view()->pagesize - App::view()->pagesize;
    $end = App::vars()->page * App::view()->pagesize;
    if ($end > App::view()->total) {
        $end = App::view()->total;
    }

    if (App::view()->total) {
        $uri = App::router()->getUri(2);
        App::view()->list = [];
        for ($i = App::view()->start; $i < $end; $i++) {
            App::view()->list[$i] =
                [
                    'image' => App::cfg()->sys->homeurl . 'assets/avatars/' . urlencode($query[2]) . '/' . basename($avatars[$i]),
                    'link'  => (App::user()->id ? $uri . 'set/' . urlencode($query[2]) . '/' . urlencode(basename($avatars[$i])) : '#')
                ];
        }
    }

    App::view()->cat = $query[2];
    App::view()->setTemplate('avatars_list.php');
} else {
    // Показываем каталог аватаров (список категорий)
    App::view()->list = [];
    foreach ($catalog as $key => $val) {
        App::view()->list[] =
            [
                'link'  => $uri . 'list/' . urlencode($key) . '/',
                'name'  => $val,
                'count' => count(glob(ROOT_PATH . 'assets' . DS . 'avatars' . DS . $key . DS . '*.{gif,jpg,png}', GLOB_BRACE))
            ];
    }

    App::view()->setTemplate('avatars_index.php');
}
