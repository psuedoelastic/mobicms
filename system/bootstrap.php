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

/**
 * Check the current PHP version
 */
version_compare(phpversion(), '5.5', '>') || die('ERROR: your needs PHP 5.5 or higher');

/**
 * Toggle debug mode
 */
defined('DEBUG') || define('DEBUG', false);

/**
 * mobiCMS version
 */
defined('MOBICMS') || define('MOBICMS', true);

/**
 * Directory Separator alias
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * Defines the root directory of the mobiCMS installation
 */
define('ROOT_PATH', dirname(__DIR__).DS);

/**
 * Path to the PSR-0 compatible libraries
 */
define('VENDOR_PATH', __DIR__.DS.'library'.DS);

/**
 * Path to the configuration files
 */
define('CONFIG_PATH', __DIR__.DS.'config'.DS);

/**
 * Path to the system cache files
 */
define('CACHE_PATH', __DIR__.DS.'cache'.DS);

/**
 * Path to the LOG files
 */
define('LOG_PATH', __DIR__.DS.'logs'.DS);

/**
 * Path to the language files
 */
define('LANGUAGE_PATH', __DIR__.DS.'languages'.DS);

/**
 * Path to the modiles
 */
define('MODULE_PATH', ROOT_PATH.'modules'.DS);

/**
 * Path to the Templates
 */
define('THEMES_PATH', ROOT_PATH.'themes'.DS);

/**
 * Path to the Upload files
 */
define('FILES_PATH', ROOT_PATH.'uploads'.DS);

/**
 * Path to the Upload files
 */
define('ASSETS_PATH', ROOT_PATH.'assets'.DS);


/**
 * Profiling
 */
define('START_MEMORY', memory_get_usage());
define('START_TIME', microtime(true));

/**
 * Define some PHP settings
 */
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

/**
 * Error reporting configuration
 */
if (DEBUG) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 'On');
    ini_set('log_errors', 'On');
    ini_set('error_log', LOG_PATH.'errors-'.date('Y-m-d').'.log');
} else {
    ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 'Off');
    ini_set('log_errors', 'Off');
}

/**
 * Class App
 *
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 *
 * @method static \Mobicms\Autoload\Loader      autoload()
 * @method static \Mobicms\Config\Factory       cfg()
 * @method static \Mobicms\Database\PDOmysql    db()
 * @method static \Mobicms\HtmlFilter\Filter    filter()
 * @method static \Mobicms\Helpers\Image        image()
 * @method static \Mobicms\L10n\Languages       languages()
 * @method static \Mobicms\Environment\Network  network()
 * @method static \Mobicms\HtmlFilter\Purify    purify()
 * @method static \Mobicms\Routing\Router       router()
 * @method static \Mobicms\Users\User           user()
 * @method static \Mobicms\Environment\Vars     vars()
 * @method static \Mobicms\Template\View        view()
 */
class App
{
    /**
     * @var array Object Pool
     */
    private static $objects = [];

    /**
     * @var array Multi instance Service Locator (alias => service)
     */
    private static $services =
        [
            'image'  => Mobicms\Helpers\Image::class,
            'filter' => Mobicms\HtmlFilter\Filter::class,
        ];

    /**
     * @var array Single instance Service Locator (alias => service)
     */
    private static $singleInstanceServices =
        [
            'autoload'  => Mobicms\Autoload\Loader::class,
            'cfg'       => Mobicms\Config\Factory::class,
            'db'        => Mobicms\Database\PDOmysql::class,
            'languages' => Mobicms\L10n\Languages::class,
            'network'   => Mobicms\Environment\Network::class,
            'router'    => Mobicms\Routing\Router::class,
            'purify'    => Mobicms\HtmlFilter\Purify::class,
            'user'      => Mobicms\Users\User::class,
            'vars'      => Mobicms\Environment\Vars::class,
            'view'      => Mobicms\Template\View::class,
        ];

    /**
     * @param string $name
     * @param array  $args
     * @return mixed
     * @throws BadMethodCallException
     */
    public static function __callStatic($name, $args = [])
    {
        if (isset(self::$services[$name])) {
            return self::factory(self::$services[$name], $args);
        } elseif (isset(self::$objects[$name])) {
            return self::$objects[$name];
        } elseif (isset(self::$singleInstanceServices[$name])) {
            self::$objects[$name] = self::factory(self::$singleInstanceServices[$name], $args);

            return self::$objects[$name];
        } else {
            throw new BadMethodCallException('method '.$name.'() not found');
        }
    }

    private static function factory($class, $args)
    {
        switch (count($args)) {
            case 0:
                return new $class;

            case 1:
                return new $class($args[0]);

            case 2:
                return new $class($args[0], $args[1]);

            case 3:
                return new $class($args[0], $args[1], $args[2]);

            case 4:
                return new $class($args[0], $args[1], $args[2], $args[3]);

            default:
                return new $class($args);
        }
    }
}

/**
 * Autoloading classes
 */
require_once(VENDOR_PATH.'Mobicms/Autoload/Loader.php');

// Register old classes in folder /system/includes
App::autoload()->import('Counters', __DIR__.'/includes/Counters.php');
App::autoload()->import('Functions', __DIR__.'/includes/Functions.php');
App::autoload()->import('Users', __DIR__.'/includes/Users.php'); //TODO: удалить

/**
 * Exception handler
 */
set_exception_handler(
    function (Exception $exception) {
        App::view()->setLayout(false);
        new Mobicms\Log\ExceptionHandler($exception);
    }
);

/**
 *
 */
App::db();
//TODO: Переделать на отлов исключений
/**
 * Starting firewall
 */
(new \Mobicms\Firewall\Firewall)->run(App::network()->getIp());

/**
 * Starting session
 */
(new \Mobicms\Session\SessionHandler)->run();

/**
 * Languages
 *
 * @param string $key    Ключ
 * @param bool   $system Использовать системный язык
 * @param bool   $isFile Получение содержимого текстового файла
 * @return string        Фраза, соответствующая переданному ключу
 */
function __($key, $system = false, $isFile = false)
{
    return App::languages()->getPhrase($key, $system, $isFile);
}

/**
 * Output buffering
 */
ob_start();

/**
 * Templates
 */
register_shutdown_function(function () {
    App::view()->render();
});

/**
 * Closing session
 */
session_register_shutdown();
