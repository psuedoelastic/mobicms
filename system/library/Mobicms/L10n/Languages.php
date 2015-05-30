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

namespace Mobicms\L10n;

use App;

/**
 * Class Languages
 *
 * @package Mobicms\L10n
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class Languages
{
    private $lng = null;
    private $systemLanguage;
    private $moduleLanguage;
    private $lngList;

    /**
     * Automatic detection of language
     *
     * @return string
     */
    public function getCurrentISO()
    {
        if ($this->lng === null) {
            $this->lng = App::cfg()->sys->lng;

            if (App::cfg()->sys->lng_switch) {
                if (isset($_SESSION['lng'])) {
                    $this->lng = $_SESSION['lng'];
                } else {
                    if (App::user()->id
                        && isset(App::user()->settings['lng'])
                        && App::user()->settings['lng'] != '#'
                        && in_array(App::user()->settings['lng'], $this->getLngList())
                    ) {
                        $this->lng = App::user()->settings['lng'];
                    } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                        $accept = explode(',', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])));

                        foreach ($accept as $var) {
                            $iso = substr($var, 0, 2);

                            if (in_array($iso, $this->getLngList())) {
                                $this->lng = $iso;
                                break;
                            }
                        }
                    }
                    $_SESSION['lng'] = $this->lng;
                }
            }
        }

        return $this->lng;
    }

    /**
     * Receive the ISO list of languages available in system
     *
     * @return array
     */
    public function getLngList()
    {
        if ($this->lngList === null) {
            $list = glob(LANGUAGE_PATH . '*', GLOB_ONLYDIR);

            foreach ($list as $val) {
                $this->lngList[] = basename($val);
            }
        }

        return $this->lngList;
    }

    /**
     * Receive the list of languages together with names and flags
     *
     * @return array ISO code => name
     */
    public function getLngDescription()
    {
        $description = [];
        $list = $this->getLngList();

        foreach ($list as $iso) {
            $file = LANGUAGE_PATH . $iso . '/language.ini';

            if (is_file($file) && ($desc = parse_ini_file($file)) !== false) {
                $description[$iso] = App::image('flag_' . $iso . '.gif') . '&#160; ';
                $description[$iso] .= isset($desc['name']) && !empty($desc['name']) ? $desc['name'] : $iso;
            } else {
                exit;
            }
        }

        return $description;
    }

    /**
     * Return phrase on the given key
     *
     * @param string $key
     * @param bool   $forceSystem
     * @param bool   $isFile
     *
     * @return string
     */
    public function getPhrase($key, $forceSystem = false, $isFile = false)
    {
        if ($isFile) {
            if ($forceSystem) {
                $phrase = $this->readLng(LANGUAGE_PATH, $key);
            } else {
                $phrase = $this->readLng(MODULE_PATH . App::router()->dir . DS . '_sys' . DS . 'languages' . DS, $key);
            }

            if ($phrase) {
                return $phrase;
            }
        } else {
            // Receive module phrases
            if ($this->moduleLanguage === null && $this->moduleLanguage !== false) {
                $this->moduleLanguage = $this->readLng(MODULE_PATH . App::router()->dir . DS . '_sys' . DS . 'languages' . DS);
            }

            // Receive system phrases
            if ($this->systemLanguage === null) {
                $this->systemLanguage = $this->readLng(LANGUAGE_PATH);
            }

            if ($this->systemLanguage === false) {
                return 'ERROR: language file';
            } elseif (!$forceSystem && isset($this->moduleLanguage[$key])) {
                // Return the module phrase
                return $this->moduleLanguage[$key];
            } elseif (isset($this->systemLanguage[$key])) {
                // Return the system phrase
                return $this->systemLanguage[$key];
            }
        }

        // If the phrase doesn't exist, we return a key
        return '# ' . $key . ' #';
    }

    /**
     * Parse Language file
     *
     * @param string      $path
     * @param bool|string $isFile
     * @return array|bool
     */
    private function readLng($path, $isFile = false)
    {
        $lngFile = $isFile ? $isFile . '.txt' : 'language.lng';
        $array =
            [
                $path . $this->getCurrentISO() . DS . $lngFile,
                $path . 'en' . DS . $lngFile,
                $path . 'ru' . DS . $lngFile
            ];

        foreach ($array as $file) {
            if (is_file($file)) {
                return $isFile ? file_get_contents($file) : parse_ini_file($file);
            }
        }

        return false;
    }
}
