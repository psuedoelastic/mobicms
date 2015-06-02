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

/**
 * Class System
 *
 * @package Mobicms\Config
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 *
 * @property int    $acl_downloads
 * @property int    $acl_downloads_comm
 * @property int    $acl_forum
 * @property int    $acl_guestbook
 * @property int    $acl_library
 * @property string $copyright
 * @property string $email
 * @property int    $filesize
 * @property string $home_title
 * @property string $homeurl
 * @property string $lng
 * @property int    $lng_switch
 * @property string $meta_desc
 * @property string $meta_key
 * @property int    $profiling_generation
 * @property int    $profiling_memory
 * @property int    $sitemap_browsers
 * @property int    $sitemap_forum
 * @property int    $sitemap_library
 * @property int    $sitemap_users
 * @property int    $timeshift
 * @property string $theme_current
 * @property string $theme_default
 * @property int    $usr_change_nickname
 * @property int    $usr_change_nickname_period
 * @property int    $usr_change_sex
 * @property int    $usr_change_status
 * @property int    $usr_flood_day
 * @property int    $usr_flood_mode
 * @property int    $usr_flood_night
 * @property int    $usr_gravatar
 * @property int    $usr_nickname_digits_only
 * @property int    $usr_reg_allow
 * @property int    $usr_reg_email
 * @property int    $usr_reg_moderation
 * @property int    $usr_reg_quarantine
 * @property int    $usr_upload_avatars
 * @property int    $usr_view_online
 * @property int    $usr_view_profiles
 * @property int    $usr_view_userlist
 */
class System
{
    private $settings;

    public function __construct()
    {
        $this->settings = $this->readSettings();
    }

    public function __get($key)
    {
        if (isset($this->settings[$key])) {
            return $this->settings[$key];
        } else {
            return false;
        }
    }

    public function __isset($key)
    {
        return isset($this->settings[$key]);
    }

    public function getThemesList()
    {
        $tpl_list = [];
        $dirs = glob(THEMES_PATH . '*', GLOB_ONLYDIR);

        foreach ($dirs as $val) {
            if (is_file($val . DS . 'theme.ini')) {
                $options = parse_ini_file($val . DS . 'theme.ini');

                if (isset($options['name'], $options['author'], $options['author_url'], $options['author_email'], $options['description'])
                    && is_file($val . DS . 'theme.png')
                ) {
                    $dir = basename($val);
                    $options['thumbinal'] = \App::cfg()->sys->homeurl . 'themes/' . $dir . '/theme.png';
                    $tpl_list[$dir] = $options;
                }
            }
        }

        ksort($tpl_list);

        return $tpl_list;
    }

    /**
     * Write system settings
     *
     * @param array $settings
     */
    public function write(array $settings)
    {
        if (empty($settings)) {
            return;
        }

        foreach ($settings as $key => $val) {
            if (isset($this->settings[$key])) {
                $this->settings[$key] = $val;
            }
        }

        $this->writeToFile($this->settings);
    }

    /**
     * Write configuration file
     *
     * @param array $settings
     */
    private function writeToFile(array $settings)
    {
        if (file_put_contents(CONFIG_PATH . 'settings.php', '<?php' . "\n" . '$settings = ' . var_export($settings, true) . ';') === false) {
            throw new \RuntimeException('Can not write system configuration file');
        }
    }

    /**
     * Read configuration from file, or get defaults
     *
     * @return array
     */
    private function readSettings()
    {
        if (is_file(CONFIG_PATH . 'settings.php')) {
            include_once CONFIG_PATH . 'settings.php';

            if (isset($settings)
                && is_array($settings)
                && !empty($settings)
            ) {
                return $settings;
            }
        }

        $defaults = $this->defaults();
        $this->writeToFile($defaults);

        return $defaults;
    }

    /**
     * Default settings
     *
     * @return array
     */
    private function defaults()
    {
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/';
        $path = trim(str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['PHP_SELF'])), '/');

        return
            [
                'acl_downloads'              => 2,
                'acl_downloads_comm'         => 1,
                'acl_forum'                  => 2,
                'acl_guestbook'              => 2,
                'acl_library'                => 2,
                'copyright'                  => 'Powered by mobiCMS',
                'email'                      => 'user@example.com',
                'filesize'                   => 2100,
                'home_title'                 => 'mobiCMS!',
                'homeurl'                    => $url . (!empty($path) ? $path . '/' : ''),
                'lng'                        => 'ru',
                'lng_switch'                 => 1,
                'meta_desc'                  => 'mobiCMS mobile content management system http://mobicms.net',
                'meta_key'                   => 'mobicms',
                'profiling_generation'       => 1,
                'profiling_memory'           => 1,
                'sitemap_browsers'           => 1,
                'sitemap_forum'              => 1,
                'sitemap_library'            => 1,
                'sitemap_users'              => 1,
                'theme_current'              => 'thundercloud',
                'theme_default'              => 'thundercloud',
                'timeshift'                  => 4,
                'usr_change_nickname'        => 1,
                'usr_change_nickname_period' => 30,
                'usr_change_sex'             => 1,
                'usr_change_status'          => 1,
                'usr_flood_day'              => 10,
                'usr_flood_mode'             => 2,
                'usr_flood_night'            => 30,
                'usr_gravatar'               => 1,
                'usr_nickname_digits_only'   => 0,
                'usr_reg_allow'              => 1,
                'usr_reg_email'              => 0,
                'usr_reg_moderation'         => 0,
                'usr_reg_quarantine'         => 0,
                'usr_upload_avatars'         => 1,
                'usr_view_online'            => 1,
                'usr_view_profiles'          => 0,
                'usr_view_userlist'          => 1
            ];
    }
} 