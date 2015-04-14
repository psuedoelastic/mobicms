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

if (!$id) {
    echo __('error_wrong_data');
    exit;
}

$url = App::router()->getUri(1);

$s = isset($_GET['s']) ? intval($_GET['s']) : false;

// Запрос сообщения
$req = App::db()->query("SELECT `" . TP . "forum__`.*, `" . TP . "user__`.`sex`, `" . TP . "user__`.`rights`, `" . TP . "user__`.`last_visit`, `" . TP . "user__`.`status`, `" . TP . "user__`.`join_date`
FROM `" . TP . "forum__` LEFT JOIN `" . TP . "user__` ON `" . TP . "forum__`.`user_id` = `" . TP . "user__`.`id`
WHERE `" . TP . "forum__`.`type` = 'm' AND `" . TP . "forum__`.`id` = " . $id . (App::user()->rights >= 7 ? "" : " AND `" . TP . "forum__`.`close` != '1'") . " LIMIT 1");
$res = $req->fetch();

// Запрос темы
$them = App::db()->query("SELECT * FROM `" . TP . "forum__` WHERE `type` = 't' AND `id` = '" . $res['refid'] . "'")->fetch();
echo '<div class="phdr"><b>' . __('topic') . ':</b> ' . $them['text'] . '</div><div class="menu">';
// Значок пола
if ($res['sex'])
    echo Functions::getImage('usr_' . ($res['sex'] == 'm' ? 'm' : 'w') . ($res['join_date'] > time() - 86400 ? '_new' : '') . '.png', '', 'align="middle"') . '&#160;';
else
    echo Functions::getIcon('delete.png', '', '', 'align="middle"') . '&#160;';
// Ник юзера и ссылка на его анкету
if (App::user()->id && App::user()->id != $res['user_id']) {
    echo '<a href="../users/profile.php?user=' . $res['user_id'] . '&amp;fid=' . $res['id'] . '"><b>' . $res['from'] . '</b></a> ';
    echo '<a href="' . $url . '?act=say&amp;id=' . $res['id'] . '&amp;start=' . App::vars()->start . '"> [о]</a> <a href="' . $url . '?act=say&amp;id=' . $res['id'] . '&amp;start=' . App::vars()->start . '&amp;cyt"> [ц]</a>';
} else {
    echo '<b>' . $res['from'] . '</b>';
}
// Метка должности
switch ($res['rights']) {
    case 7:
        echo " Adm ";
        break;

    case 6:
        echo " Smd ";
        break;

    case 3:
        echo " Mod ";
        break;

    case 1:
        echo " Kil ";
        break;
}
// Метка Онлайн / Офлайн
echo(time() > $res['last_visit'] + 300 ? '<span class="red"> [Off]</span>' : '<span class="green"> [ON]</span>');
// Время поста
echo ' <span class="gray">(' . Functions::displayDate($res['time']) . ')</span><br/>';
// Статус юзера
if (!empty($res['status']))
    echo '<div class="status">' . Functions::getImage('label.png') . '&#160;' . $res['status'] . '</div>';
$text = htmlentities($res['text'], ENT_QUOTES, 'UTF-8');
$text = nl2br($text);
$text = htmlspecialchars($text);
if (App::user()->settings['smilies'])
    $text = Functions::smilies($text, ($res['rights'] >= 1) ? 1 : 0);
echo $text . '</div>';
// Вычисляем, на какой странице сообщение?
$page = ceil(App::db()->query("SELECT COUNT(*) FROM `" . TP . "forum__` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">= " : "<= ") . $id)->fetchColumn() / App::user()->settings['page_size']);
echo '<div class="phdr"><a href="' . $url . '?id=' . $res['refid'] . '&amp;page=' . $page . '">' . __('back_to_topic') . '</a></div>';
echo '<p><a href="' . $url . '">' . __('to_forum') . '</a></p>';
