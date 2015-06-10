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

namespace Mobicms\Autoload;

/**
 * Class Loader
 *
 * @package Mobicms\Autoload
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class Loader
{
    private $map = [];

    public function __construct()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Register custom class for loading
     *
     * @param string $className
     * @param string $pahToFile
     * @throws \InvalidArgumentException
     */
    public function import($className, $pahToFile)
    {
        $this->map[$className] = $pahToFile;
    }

    /**
     * PSR-0 compatible Class Loader
     *
     * @param null $name
     * @throws \RuntimeException
     */
    private function loadClass($name = null)
    {
        $className = ltrim($name, '\\');

        if (!class_exists($className)) {
            if (isset($this->map[$className])) {
                $fileName = $this->map[$className];
            } else {
                $fileName = VENDOR_PATH;
                if ($lastNsPos = strrpos($className, '\\')) {
                    $namespace = substr($className, 0, $lastNsPos);
                    $fileName .= str_replace('\\', DS, $namespace).DS;
                }
                $fileName .= str_replace('_', DS, substr($className, $lastNsPos + 1)).'.php';
            }

            if (!is_file($fileName)) {
                throw new \RuntimeException('Class "'.$name.'" is not found or unreadable');
            }

            include_once $fileName;
        }
    }
}
