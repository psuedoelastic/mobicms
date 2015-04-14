<?php

/**
 * @package     mobiCMS
 * @link        http://mobicms.net
 * @copyright   Copyright (C) 2008-2012 mobiCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://mobicms.net/about
 */
class Album
{
    public static function vote($arg = null)
    {
        if (!$arg) {
            return false;
        }

        $rating = $arg['vote_plus'] - $arg['vote_minus'];

        if ($rating > 0) {
            $color = 'C0FFC0';
        } elseif ($rating < 0) {
            $color = 'F196A8';
        } else {
            $color = 'CCC';
        }

        $out = '<div class="gray">' . __('rating') . ': <span style="color:#000;background-color:#' . $color . '">&#160;&#160;<big><b>' . $rating . '</b></big>&#160;&#160;</span> ' .
            '(' . __('vote_against') . ': ' . $arg['vote_minus'] . ', ' . __('vote_for') . ': ' . $arg['vote_plus'] . ')';

        if (App::user()->id
            && App::user()->id != $arg['user_id']
            && empty(App::user()->ban)
            && App::user()->data['count_forum'] > 10
        ) {
            // Проверяем, имеет ли юзер право голоса
            //TODO: Доработать ссылки
            $req = App::db()->query("SELECT * FROM `" . TP . "album__votes` WHERE `user_id` = " . App::user()->id . " AND `file_id` = '" . $arg['id'] . "' LIMIT 1");
            if (!$req->rowCount()) {
                $out .= '<br />' . __('vote') . ': <a href="' . App::router()->getUri(2) . '?act=vote&amp;mod=minus&amp;img=' . $arg['id'] . '">&lt;&lt; -1</a> | ';
                $out .= '<a href="' . App::router()->getUri(2) . '?act=vote&amp;mod=plus&amp;img=' . $arg['id'] . '">+1 &gt;&gt;</a>';
            }
        }

        $out .= '</div>';

        return $out;
    }
}
