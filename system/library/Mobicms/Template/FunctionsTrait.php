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

namespace Mobicms\Template;

use App;

/**
 * Class FunctionsTrait
 *
 * @package Mobicms\Template
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-06-11
 */
trait FunctionsTrait
{
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
                $load = implode('.', $tmp).'.'.($version ? $version.'.' : '').$ext;

                if ($module === false) {
                    // Вызов системного файла
                    if (is_file(THEMES_PATH.App::user()->settings['skin'].DS.$dir.DS.$file)) {
                        return App::cfg()->sys->homeurl.'themes/'.App::user()->settings['skin'].'/'.$dir.'/'.$load;
                    } elseif (is_file(THEMES_PATH.App::cfg()->sys->theme_default.DS.$dir.DS.$file)) {
                        return App::cfg()->sys->homeurl.'themes/'.App::cfg()->sys->theme_default.'/'.$dir.'/'.$load;
                    }
                } else {
                    // Вызов файла модуля
                    if (is_file(THEMES_PATH.App::user()->settings['skin'].DS.'modules'.DS.$module.DS.$dir.DS.$file)) {
                        return App::cfg()->sys->homeurl.'themes/'.App::user()->settings['skin'].'/modules/'.$module.'/'.$dir.'/'.$file;
                    } elseif (is_file(ASSETS_PATH.'modules'.DS.$module.DS.$dir.DS.$file)) {
                        return App::cfg()->sys->homeurl.'assets/modules/'.$module.'/'.$dir.'/'.$file;
                    }
                }
                break;

            case 'php':
                if ($module === false) {
                    // Вызов системного файла
                    if (is_file(THEMES_PATH.App::user()->settings['skin'].DS.'templates'.DS.$file)) {
                        return THEMES_PATH.App::user()->settings['skin'].DS.'templates'.DS.$file;
                    } elseif (is_file(THEMES_PATH.App::cfg()->sys->theme_default.DS.'templates'.DS.$file)) {
                        return THEMES_PATH.App::cfg()->sys->theme_default.DS.'templates'.DS.$file;
                    }
                } else {
                    // Вызов файла модуля
                    if (is_file(THEMES_PATH.App::user()->settings['skin'].DS.'modules'.DS.$module.DS.'templates'.DS.$file)) {
                        return THEMES_PATH.App::user()->settings['skin'].DS.'modules'.DS.$module.DS.'templates'.DS.$file;
                    } elseif (is_file(MODULE_PATH.$module.DS.'_sys'.DS.'templates'.DS.$file)) {
                        return MODULE_PATH.$module.DS.'_sys'.DS.'templates'.DS.$file;
                    }
                }
                break;

            default:
                throw new \InvalidArgumentException('Unknown extension');
        }

        throw new \InvalidArgumentException($file.'" not found');
    }

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