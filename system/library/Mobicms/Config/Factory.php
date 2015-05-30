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

namespace Mobicms\Config;

use Mobicms\Exceptions\BadMethodCallException;

/**
 * Class Factory
 *
 * @package Mobicms\Config
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 *
 * @property \Mobicms\Config\System   $sys
 */
class Factory
{
    private $objects = [];
    private $classes =
        [
            'sys' => '\Mobicms\Config\System',
        ];

    public function __get($method)
    {
        if (isset($this->classes[strtolower($method)])) {
            if (!isset($this->objects[$method])) {
                $this->objects[$method] = new  $this->classes[$method];
            }
        } else {
            throw new BadMethodCallException;
        }

        return $this->objects[$method];
    }
} 