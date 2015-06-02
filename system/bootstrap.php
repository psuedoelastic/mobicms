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
define('ROOT_PATH', dirname(__DIR__) . DS);

/**
 * Path to the PSR-0 compatible libraries
 */
define('VENDOR_PATH', __DIR__ . DS . 'library' . DS);

/**
 * Path to the configuration files
 */
define('CONFIG_PATH', __DIR__ . DS . 'config' . DS);

/**
 * Path to the system cache files
 */
define('CACHE_PATH', __DIR__ . DS . 'cache' . DS);

/**
 * Path to the LOG files
 */
define('LOG_PATH', __DIR__ . DS . 'logs' . DS);

/**
 * Path to the language files
 */
define('LANGUAGE_PATH', __DIR__ . DS . 'languages' . DS);

/**
 * Path to the modiles
 */
define('MODULE_PATH', ROOT_PATH . 'modules' . DS);

/**
 * Path to the Templates
 */
define('THEMES_PATH', ROOT_PATH . 'themes' . DS);

/**
 * Path to the Upload files
 */
define('FILES_PATH', ROOT_PATH . 'uploads' . DS);

/**
 * Path to the Upload files
 */
define('ASSETS_PATH', ROOT_PATH . 'assets' . DS);


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
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    ini_set('log_errors', 'On');
    ini_set('error_log', LOG_PATH . 'errors-' . date('Y-m-d') . '.log');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 'Off');
    ini_set('log_errors', 'Off');
}

/**
 * Autoloading classes
 */
require_once(VENDOR_PATH . 'Mobicms/Autoload/Loader.php');
$loader = new Mobicms\Autoload\Loader;

// Register old classes in folder /system/includes
$loader->import('Counters', __DIR__ . '/includes/Counters.php');
$loader->import('Functions', __DIR__ . '/includes/Functions.php');
$loader->import('Users', __DIR__ . '/includes/Users.php'); //TODO: удалить

/**
 * Class App
 *
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 *
 * @method static \Mobicms\Config\Factory       cfg()
 * @method static \Mobicms\Database\PDOmysql    db()
 * @method static \Mobicms\HtmlFilter\Filter    filter()
 * @method static \Mobicms\Helpers\Image        image()
 * @method static \Mobicms\L10n\Languages       languages()
 * @method static \Mobicms\Environment\Network  network()
 * @method static \Mobicms\Environment\Request  request()
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
            'image'  => '\Mobicms\Helpers\Image',
            'filter' => '\Mobicms\HtmlFilter\Filter'
        ];

    /**
     * @var array Single instance Service Locator (alias => service)
     */
    private static $singleInstanceServices =
        [
            'cfg'       => '\Mobicms\Config\Factory',
            'db'        => '\Mobicms\Database\PDOmysql',
            'languages' => '\Mobicms\L10n\Languages',
            'network'   => '\Mobicms\Environment\Network',
            'request'   => '\Mobicms\Environment\Request',
            'router'    => '\Mobicms\Routing\Router',
            'purify'    => '\Mobicms\HtmlFilter\Purify',
            'user'      => '\Mobicms\Users\User',
            'vars'      => '\Mobicms\Environment\Vars',
            'view'      => '\Mobicms\Template\View'
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
            return new self::$services[$name]($args);
        } elseif (isset(self::$objects[$name])) {
            return self::$objects[$name];
        } elseif (isset(self::$singleInstanceServices[$name])) {
            return self::$objects[$name] = new self::$singleInstanceServices[$name]($args);
        } else {
            throw new BadMethodCallException('method ' . $name . '() not found');
        }
    }
}

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