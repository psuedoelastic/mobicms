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

//TODO: Написать загрузку тем без создания файлов

if (empty($_GET['n'])) {
    echo __('error_wrong_data');
    exit;
}
$n = trim($_GET['n']);
$o = opendir("../files/forum/topics");
while ($f = readdir($o)) {
    if ($f != "." && $f != ".." && $f != "index.php" && $f != ".htaccess") {
        $ff = Functions::format($f);
        $f1 = str_replace(".$ff", "", $f);
        $a[] = $f;
        $b[] = $f1;
    }
}
$tt = count($a);
if (!in_array($n, $b)) {
    echo __('error_wrong_data');
    exit;
}
for ($i = 0; $i < $tt; $i++) {
    $tf = Functions::format($a[$i]);
    $tf1 = str_replace(".$tf", "", $a[$i]);
    if ($n == $tf1) {
        header("Location: ../files/forum/topics/$n.$tf");
    }
}
