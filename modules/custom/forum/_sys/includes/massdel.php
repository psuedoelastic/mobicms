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
if (App::user()->rights == 3 || App::user()->rights >= 6) {
    /*
    -----------------------------------------------------------------
    Массовое удаление выбранных постов форума
    -----------------------------------------------------------------
    */
    if (isset($_GET['yes'])) {
        $dc = $_SESSION['dc'];
        $prd = $_SESSION['prd'];

        $stmt = App::db()->prepare("
            UPDATE `" . TP . "forum__` SET
            `close`     = 1,
            `close_who` = ?
            WHERE `id`  = ?
        ");
        foreach ($dc as $delid) {
            $stmt->execute([App::user()->data['nickname'], intval($delid)]);
        }
        $stmt = null;

        echo __('mass_delete_confirm') . '<br/><a href="' . $prd . '">' . __('back') . '</a><br/>';
    } else {
        if (empty($_POST['delch'])) {
            echo '<p>' . __('error_mass_delete') . '<br/><a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . __('back') . '</a></p>';
            exit;
        }
        foreach ($_POST['delch'] as $v) {
            $dc[] = intval($v);
        }
        $_SESSION['dc'] = $dc;
        $_SESSION['prd'] = htmlspecialchars(getenv("HTTP_REFERER"));
        echo '<p>' . __('delete_confirmation') . '<br/><a href="' . App::router()->getUri(1) . '?act=massdel&amp;yes">' . __('delete') . '</a> | ' .
            '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . __('cancel') . '</a></p>';
    }
}
