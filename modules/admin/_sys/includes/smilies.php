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
$form->infoMessages = false;

$form
    ->title(__('update_cache'))
    ->divider()
    ->element('submit', 'submit',
        [
            'value' => __('do'),
            'class' => 'btn btn-primary'
        ]
    )
    ->html('<a class="btn btn-link" href="' . App::router()->getUri(1) . '">' . __('back') . '</a>');

if ($form->process() === true) {
    $cache = [];
    $smilies = glob(ROOT_PATH . 'assets' . DS . 'smilies' . DS . '*' . DS . '*.{gif,jpg,png}', GLOB_BRACE);
    foreach ($smilies as $val) {
        $file = basename($val);
        $name = explode(".", $file);
        $parent = basename(dirname($val));
        $image = '<img src="' . App::cfg()->sys->homeurl . 'assets/smilies/' . $parent . '/' . $file . '" alt="" />';
        if ($parent == '_admin') {
            $cache['adm_s'][] = '/:' . preg_quote($name[0]) . ':/';
            $cache['adm_r'][] = $image;
            $cache['adm_s'][] = '/:' . preg_quote(Functions::translit($name[0])) . ':/';
            $cache['adm_r'][] = $image;
        } elseif ($parent == '_simply') {
            $cache['usr_s'][] = '/:' . preg_quote($name[0]) . '/';
            $cache['usr_r'][] = $image;
        } else {
            $cache['usr_s'][] = '/:' . preg_quote($name[0]) . ':/';
            $cache['usr_r'][] = $image;
            $cache['usr_s'][] = '/:' . preg_quote(Functions::translit($name[0])) . ':/';
            $cache['usr_r'][] = $image;
        }
    }

    if (file_put_contents(CACHE_PATH . 'smilies.cache', serialize($cache))) {
        App::view()->save = __('cache_updated');
    } else {
        App::view()->error = __('error_cache_update');
    }
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('smilies.php');