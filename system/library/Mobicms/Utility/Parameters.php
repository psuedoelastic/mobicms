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

namespace Mobicms\Utility;

use ArrayObject;

/**
 * Class Parameters
 *
 * @package Mobicms\Utility
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 *          This class implements the ideas of the Zend Framework
 *          <https://github.com/zendframework/Component_ZendStdlib>
 * @version v.1.0.0 2015-02-10
 */
class Parameters extends ArrayObject
{
    public function __construct(array $val = null)
    {
        if (null === $val) {
            $val = [];
        }

        parent::__construct($val, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Retrieve by key
     *
     * @param string $name
     * @param mixed  $default Optional default value
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (isset($this[$name])) {
            return parent::offsetGet($name);
        }

        return $default;
    }
}