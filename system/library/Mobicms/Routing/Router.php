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

namespace Mobicms\Routing;

/**
 * Class Router
 *
 * @package Mobicms\Routing
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class Router
{
    private $path = [];
    private $pathQuery = [];
    private $module = null;

    public $dir = '';

    public function __construct()
    {
        $uri = trim(urldecode(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL)), '/');
        $uri = substr($uri, strlen(trim(dirname($_SERVER['PHP_SELF']), DIRECTORY_SEPARATOR)), 400);
        $parse = parse_url($uri);
        $this->path = array_map('trim', array_filter(explode('/', trim($parse['path'], '/'))));
        $this->module = !empty($this->path) ? $this->path[0] : 'home';
    }

    public function dispatch()
    {
        // Подключаем файл конфигурации модулей
        if (is_file(CONFIG_PATH . 'routing.php')) {
            require_once CONFIG_PATH . 'routing.php';
            $modules = isset($modules) && is_array($modules) ? $modules : [];
        } else {
            throw new \RuntimeException('modules configuration file does not exist');
        }

        if (!isset($modules[$this->module])) {
            $this->module = '404';
        }

        $this->dir = $modules[$this->module];
        $dir = MODULE_PATH . $this->dir . DS;
        $file = 'index.php';

        /**
         * Предварительные проверки модуля
         */
        if ($this->module === null) {
            // Если URI запроса нет, то загружаем домашнюю страницу
            $this->module = 'home';
        } elseif (
            !array_key_exists($this->module, $modules) // Если модуль не зарегистрирован
            || !is_dir(MODULE_PATH . $modules[$this->module]) // Если папки с модулем не существует
            || !is_file(MODULE_PATH . $modules[$this->module] . DS . 'index.php') // Если нет индексного файла
        ) {
            // Пересылаем на ошибку 404
            $this->module = '404';
        } else {
            /**
             * Проверка дополнительных параметров Path
             */
            $i = 0;
            $path = array_slice($this->path, 1);

            if (!empty($path)) {
                foreach ($path as $val) {
                    $val = ltrim($val, '_');

                    if (is_dir($dir . $val)) {
                        // Если существует директория
                        $dir .= $val . DS;
                    } else {
                        if (pathinfo($val, PATHINFO_EXTENSION) == 'php' && is_file($dir . $val)) {
                            // Если вызван PHP файл
                            $file = $val;
                            ++$i;
                        }

                        break;
                    }

                    ++$i;
                }

                if (!is_file($dir . $file)) {
                    // Пересылаем на ошибку 404
                    $this->module = '404';
                    $dir = MODULE_PATH . $modules[$this->module] . DS;
                    $file = 'index.php';
                } else {
                    // Разделяем URI на Path и Query
                    $this->path = array_slice($this->path, 0, $i + 1);
                    $this->pathQuery = array_slice($path, $i);
                }
            }
        }

        // Загружаем модуль
        include_once $dir . $file;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getQuery($key = null)
    {
        if ($key === null) {
            return $this->pathQuery;
        } else {
            return isset($this->pathQuery[$key]) ? $this->pathQuery[$key] : false;
        }
    }

    public function getUri($key = 0)
    {
        if ($key) {
            $uri = array_merge($this->path, $this->pathQuery);
            $uri = array_slice($uri, 0, $key);
        } else {
            $uri = $this->path;
        }

        return htmlspecialchars(\App::cfg()->sys->homeurl . implode('/', $uri) . '/');
    }
}
