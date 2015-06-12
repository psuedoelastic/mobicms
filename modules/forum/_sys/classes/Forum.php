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
class Forum
{
    public static function settings()
    {
        if (!App::user()->id || ($set_forum = App::user()->getData('set_forum')) === false) {
            return [
                'farea'    => 0,
                'upfp'     => 0,
                'preview'  => 1,
                'postclip' => 1,
                'postcut'  => 2
            ];
        }

        return $set_forum;
    }

    public static function forum_link($m)
    {
        if (!isset($m[3])) {
            return '[url=' . $m[1] . ']' . $m[2] . '[/url]';
        } else {
            $p = parse_url($m[3]);
            if ('http://' . $p['host'] . $p['path'] . '?id=' == App::cfg()->sys->homeurl . 'forum/?id=') {
                $thid = abs(intval(preg_replace('/(.*?)id=/si', '', $m[3])));
                $req = App::db()->query("SELECT `text` FROM `forum__` WHERE `id`= '$thid' AND `type` = 't' AND `close` != '1'");
                if ($req->rowCount()) {
                    $res = $req->fetch();
                    $name = strtr($res['text'], [
                        '&quot;' => '',
                        '&amp;'  => '',
                        '&lt;'   => '',
                        '&gt;'   => '',
                        '&#039;' => '',
                        '['      => '',
                        ']'      => ''
                    ]);
                    if (mb_strlen($name) > 40)
                        $name = mb_substr($name, 0, 40) . '...';

                    return '[url=' . $m[3] . ']' . $name . '[/url]';
                } else {
                    return $m[3];
                }
            } else
                return $m[3];
        }
    }

    /**
     * Check category availability
     *
     * @param int $id
     * @return array|bool
     */
    public static function checkCategory($id)
    {
        if (is_int($id)) {
            $query = App::db()->query("SELECT * FROM `forum__` WHERE `id` = " . $id)->fetch();

            if ($query !== false && ($query['type'] == 'r' || $query['type'] == 'f')) {
                return $query;
            }
        }

        unset($query);

        return false;
    }
}
