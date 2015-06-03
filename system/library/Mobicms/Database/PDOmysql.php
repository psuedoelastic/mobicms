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

namespace Mobicms\Database;

/**
 * Class PDOmysql
 *
 * @package Mobicms\Database
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class PDOmysql extends \PDO
{
    public function __construct()
    {
        $db_host = 'localhost';
        $db_user = 'root';
        $db_pass = '';
        $db_name = 'mobicms';
        $db_prefix = '';

        if (is_file(CONFIG_PATH.'database.php')) {
            require_once CONFIG_PATH.'database.php';
        } else {
            throw new \RuntimeException('database configuration file does not exist.<br/>Please install the system or restore the file /system/config/system/database.php.');
        }

        /**
         * Префиксы таблиц базы данных
         */
        define('TP', $db_prefix);

        try {
            parent::__construct(
                'mysql:host='.$db_host.';dbname='.$db_name,
                $db_user,
                $db_pass,
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY
                ]
            );
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function pagination()
    {
        return ' LIMIT '.\App::vars()->start.','.\App::user()->settings['page_size'];
    }
}
