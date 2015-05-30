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

namespace Mobicms\Exceptions;

/**
 * Class RuntimeException
 *
 * @package Mobicms\Exceptions
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class RuntimeException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct();
        $trace = $this->getTrace();
        $this->message = 'RUNTIME ERROR: ' . $message .
            "\n" . 'FILE: ' . $trace[1]['file'] .
            "\n" . 'LINE: ' . $trace[1]['line'];
    }
}
