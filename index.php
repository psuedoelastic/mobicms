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

/**
 * System version
 */
define('MOBICMS', '0.3.0');

/**
 * Toggle debug mode
 */
define('DEBUG', true);

/**
 * Bootstrap the application
 */
require_once(__DIR__ . '/system/bootstrap.php');
App::router()->dispatch();
