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

//TODO: Удалить и заменить на новую систему
class Users
{
    public static $data;

    public static function get($id)
    {
        if ($id == App::user()->id) {
            self::$data = App::user()->data;

            return true;
        }

        $req = App::db()->query("SELECT * FROM `user__` WHERE `id` = " . intval($id));
        if ($req->rowCount()) {
            self::$data = $req->fetch();

            return true;
        }

        return false;
    }
}
