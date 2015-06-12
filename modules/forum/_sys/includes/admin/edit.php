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

$id = abs(intval(App::request()->getQuery('id', 0)));

/**
 * Build form
 */
$form = new Mobicms\Form\Form(['action' => App::router()->getUri(3) . ($id ? '?id=' . $id : '')]);

if ($id) {
    // Check category availability
    $category = Forum::checkCategory($id);

    if ($category === false) {
        $form
            ->html('<div class="alert alert-danger">' . __('error_wrong_data') . '</div>')
            ->html('<a class="btn btn-default" href="' . App::router()->getUri(2) . '">' . __('forum_management') . '</a>');
        App::view()->setRawVar('form', $form->display());
        App::view()->setTemplate('admin_form.php');
        exit;
    }

    // Show category name
    $form->html('<div class="alert alert-info"><h4>' . $category['text'] . '</h4><p>' . $category['soft'] . '</p></div>');
}

$form
    ->title(($id ? __('add_subsection') : __('add_section')))
    ->element('text', 'name',
        [
            'label'       => __('title'),
            'description' => __('min_2_max_30'),
            'required'    => true
        ]
    )
    ->element('textarea', 'description',
        [
            'label'       => __('description'),
            'description' => __('max_1000'),
            'editor'      => true
        ]
    )
    ->divider()
    ->element('submit', 'submit',
        [
            'value' => __('save'),
            'class' => 'btn btn-primary'
        ]
    )
    ->html('<a class="btn btn-link" href="' . App::router()->getUri(2) . 'sections/' . ($id ? '?id=' . $id : '') . '">' . __('back') . '</a>')
    // Form validation
    ->validate('name', 'lenght', ['min' => 2, 'max' => 30])
    ->validate('description', 'lenght', ['max' => 1000]);

/**
 * Form processing
 */
if ($form->process() === true) {
    // Query the last item in the list
    $req = App::db()->query("
      SELECT `realid`
      FROM `forum__`
      WHERE " . ($id ? "`refid` = " . $id . " AND `type` = 'r'" : "`type` = 'f'") . "
      ORDER BY `realid` DESC
      LIMIT 1
    ")->fetch();

    // Calculate the sort order
    if ($req !== false) {
        $sort = $req['realid'] + 1;
    } else {
        $sort = 1;
    }

    $stmt = App::db()->prepare("
      INSERT INTO `forum__` SET
      `refid`    = ?,
      `type`     = ?,
      `text`     = ?,
      `soft`     = ?,
      `realid`   = ?,
      `edit`     = '',
      `curators` = ''
    ");

    $stmt->execute(
        [
            ($id ? $id : 0),
            ($id ? 'r' : 'f'),
            App::filter($form->output['name'])->sanitizeString(),
            App::purify($form->output['description']),
            $sort
        ]
    );
    $stmt = null;

    header('Location: ' . App::router()->getUri(2) . 'sections/' . ($id ? '?id=' . $id : ''));
    exit;
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('admin_form.php');