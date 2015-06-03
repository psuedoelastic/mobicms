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

namespace Mobicms\Environment;

//TODO: класс предназначен для удаления
class Vars
{
    public $page = 1;
    public $start = 0;

    public function __construct()
    {
        // Obtain variables
        if (isset($_REQUEST['page'])) {
            $this->page = intval($_REQUEST['page']);
            $this->start = intval($this->page * \App::user()->settings['page_size'] - \App::user()->settings['page_size']);
        } elseif (isset($_REQUEST['start'])) {
            $this->start = intval($_REQUEST['start']);
        }
    }
}
