<?php

/**
 * @package     mobiCMS
 * @link        http://mobicms.net
 * @copyright   Copyright (C) 2008-2011 mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://mobicms.net/about
 */

defined('MOBICMS') or die('Error: restricted access');
$id = isset($_POST['admin_id']) ? abs(intval($_POST['admin_id'])) : false;
$act = isset($_POST['admin_act']) ? trim($_POST['admin_act']) : '';
if ($act == 'clean')
    header('Location: ' . App::router()->getUri(1) . '?act=scan_dir&do=clean&id=' . $id);
else
    header('Location: ' . App::router()->getUri(1) . '?act=' . $act . '&id=' . $id);