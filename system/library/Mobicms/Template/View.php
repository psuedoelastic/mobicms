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

namespace Mobicms\Template;

use App;

/**
 * Class View
 *
 * @package Mobicms\Template
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class View extends \ArrayObject
{
    private $httpHeaders = [];
    private $layout = null;
    private $template = null;
    private $rawContent;
    private $css = [];
    private $headerJs = [];
    private $footerJs = [];

    public function __construct()
    {
        parent::setFlags(parent::ARRAY_AS_PROPS);
    }

    /**
     * Sets the value at the specified index
     * All data, including arrays are sanitized with htmlspecialchars()
     *
     * @param mixed $key
     * @param mixed $val
     */
    public function offsetSet($key, $val)
    {
        if (is_array($val)) {
            parent::offsetSet($key, $this->sanitizeArray($val));
        } else {
            parent::offsetSet($key, htmlspecialchars($val, ENT_QUOTES, 'UTF-8', true));
        }
    }

    /**
     * Returns the value at the specified index
     *
     * @param mixed $key
     * @return bool|mixed
     */
    public function offsetGet($key)
    {
        return parent::offsetExists($key) ? parent::offsetGet($key) : false;
    }

    /**
     * Sets the value at the specified index
     * Data is transmitted in raw (as is)
     *
     * @param $key
     * @param $val
     */
    public function setRawVar($key, $val)
    {
        parent::offsetSet($key, $val);
    }

    public function setLayout($file, $module = false)
    {
        if ($file === false) {
            $this->layout = false;
        } else {
            $this->layout = $this->getPath($file, ['module' => $module]);
        }
    }

    /**
     * Set template
     *
     * @param string $template
     * @param null   $key
     * @param bool   $module
     */
    public function setTemplate($template, $key = null, $module = true)
    {
        if ($key === null) {
            $key = 'content';
        }

        if ($module === true) {
            $module = App::router()->dir;
        }

        $this->template[$key] = ['template' => $template, 'module' => $module];
    }

    /**
     * Preparing CSS
     *
     * @param string $file
     * @param array  $args
     */
    public function setCss($file, array $args = [])
    {
        $media = isset($args['media']) ? ' media="' . $args['media'] . '"' : '';

        $css = '    <link rel="stylesheet" href="' . $this->getPath($file, $args) . '"' . $media . '>';

        if (isset($args['first']) && $args['first']) {
            array_unshift($this->css, $css);
        } else {
            $this->css[] = $css;
        }
    }

    public function embedCss($css = '')
    {
        $this->css[] = $css;
    }

    /**
     * Preparing JS
     *
     * @param string $file
     * @param array  $args
     */
    public function setJs($file, array $args = [])
    {
        $js = '<script src="' . $this->getPath($file, $args) . '" type="text/javascript"></script>';

        if (isset($args['header']) && $args['header']) {
            $this->headerJs[] = $js;
        } else {
            $this->footerJs[] = $js;
        }
    }

    /**
     * Embed a custom JS
     *
     * @param string $js
     * @param bool   $footer
     */
    public function embedJs($js = '', $footer = true)
    {
        if ($footer) {
            $this->footerJs[] = $js;
        } else {
            $this->headerJs[] = $js;
        }
    }

    /**
     * Set HTTP header
     *
     * @param string $header
     */
    public function setHeader($header)
    {
        if (!empty($header)) {
            $this->httpHeaders[] = $header;
        }
    }

    /**
     * Get file path|uri
     *
     * @param string $file
     * @param array  $args
     * @return string
     */
    public function getPath($file, array $args = [])
    {
        $version = isset($args['version']) ? $args['version'] : false;
        $module = isset($args['module']) ? $args['module'] : false;

        $tmp = explode('.', $file);
        $ext = array_pop($tmp);
        $dir = strtolower($ext);

        if ($module === true) {
            $module = App::router()->dir;
        }

        switch ($dir) {
            case 'css':
            case 'js':
                $load = implode('.', $tmp) . '.' . ($version ? $version . '.' : '') . $ext;

                if ($module === false) {
                    // Вызов системного файла
                    if (is_file(THEMES_PATH . App::user()->settings['skin'] . DS . $dir . DS . $file)) {
                        return App::cfg()->sys->homeurl . 'themes/' . App::user()->settings['skin'] . '/' . $dir . '/' . $load;
                    } elseif (is_file(THEMES_PATH . App::cfg()->sys->theme_default . DS . $dir . DS . $file)) {
                        return App::cfg()->sys->homeurl . 'themes/' . App::cfg()->sys->theme_default . '/' . $dir . '/' . $load;
                    }
                } else {
                    // Вызов файла модуля
                    if (is_file(THEMES_PATH . App::user()->settings['skin'] . DS . 'modules' . DS . $module . DS . $dir . DS . $file)) {
                        return App::cfg()->sys->homeurl . 'themes/' . App::user()->settings['skin'] . '/modules/' . $module . '/' . $dir . '/' . $file;
                    } elseif (is_file(ASSETS_PATH . 'modules' . DS . $module . DS . $dir . DS . $file)) {
                        return App::cfg()->sys->homeurl . 'assets/modules/' . $module . '/' . $dir . '/' . $file;
                    }
                }
                break;

            case 'php':
                if ($module === false) {
                    // Вызов системного файла
                    if (is_file(THEMES_PATH . App::user()->settings['skin'] . DS . 'templates' . DS . $file)) {
                        return THEMES_PATH . App::user()->settings['skin'] . DS . 'templates' . DS . $file;
                    } elseif (is_file(THEMES_PATH . App::cfg()->sys->theme_default . DS . 'templates' . DS . $file)) {
                        return THEMES_PATH . App::cfg()->sys->theme_default . DS . 'templates' . DS . $file;
                    }
                } else {
                    // Вызов файла модуля
                    if (is_file(THEMES_PATH . App::user()->settings['skin'] . DS . 'modules' . DS . $module . DS . 'templates' . DS . $file)) {
                        return THEMES_PATH . App::user()->settings['skin'] . DS . 'modules' . DS . $module . DS . 'templates' . DS . $file;
                    } elseif (is_file(MODULE_PATH . $module . DS . '_sys' . DS . 'templates' . DS . $file)) {
                        return MODULE_PATH . $module . DS . '_sys' . DS . 'templates' . DS . $file;
                    }
                }
                break;

            default:
                throw new \InvalidArgumentException('Unknown extension');
        }

        throw new \InvalidArgumentException($file . '" not found');
    }

    ////////////////////////////////////////////////////////////////////////////////
    // Рендеринг шаблонов                                                         //
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Rendering layout
     */
    public function render()
    {
        foreach ($this->httpHeaders as $header) {
            header($header);
        }

        if ($this->layout !== false) {
            // Obtain the old modules output contents
            $this->rawContent = ob_get_clean();

            if ($this->layout === null) {
                $this->layout = $this->getPath('layout.default.php');
            }

            if (@extension_loaded('zlib')) {
                ob_start('ob_gzhandler');
            } else {
                ob_start();
            }

            include_once $this->layout;
        }
    }

    private function loadHeader()
    {
        return '<meta name="Generator" content="mobiCMS, http://mobicms.net"/>' . "\n" .
        implode("\n", array_merge($this->css, $this->headerJs)) . "\n";
    }

    private function loadFooter()
    {
        return implode("\n", $this->footerJs) . "\n";
    }

    private function loadTemplate($key = null)
    {
        if ($key === null) {
            $key = 'content';
        }

        if (isset($this->template[$key])) {
            return include_once $this->getPath($this->template[$key]['template'], ['module' => $this->template[$key]['module']]);
        }

        return 'rrr';//TODO: Убрать
    }

    private function loadRawContent($force = false)
    {
        if ($this->template === null || $force) {
            echo $this->rawContent;
        }
    }

    ////////////////////////////////////////////////////////////////////////////////
    // Служебные методы                                                           //
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Sanitize arrays
     *
     * @param array $array
     * @return array
     */
    private function sanitizeArray(array $array)
    {
        foreach ($array as $key => $val) {
            if (is_array($array[$key])) {
                $array[$key] = $this->sanitizeArray($array[$key]);
            } else {
                $array[$key] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8', true);
            }
        }

        return $array;
    }
}
