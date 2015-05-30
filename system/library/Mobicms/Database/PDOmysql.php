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

namespace Mobicms\Database;

use PDO;
use PDOException;
use Mobicms\Exceptions\DatabaseException;

/**
 * Class PDOmysql
 *
 * @package Mobicms\Database
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class PDOmysql extends PDO
{
    public function __construct()
    {
        if (is_file(CONFIG_PATH . 'database.php')) {
            require_once CONFIG_PATH . 'database.php';
        } else {
            throw new DatabaseException('database configuration file does not exist.<br/>Please install the system or restore the file /system/config/system/database.php.');
        }

        $db_host = isset($db_host) ? $db_host : 'localhost';
        $db_user = isset($db_user) ? $db_user : 'root';
        $db_pass = isset($db_pass) ? $db_pass : '';
        $db_name = isset($db_name) ? $db_name : 'mobicms';

        /**
         * Префиксы таблиц базы данных
         */
        define('TP', isset($db_prefix) ? $db_prefix : '');

        try {
            parent::__construct(
                'mysql:host=' . $db_host . ';dbname=' . $db_name,
                $db_user,
                $db_pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY
                ]
            );
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage());
        }
    }

    public function pagination()
    {
        return ' LIMIT ' . \App::vars()->start . ',' . \App::user()->settings['page_size'];
    }
}
