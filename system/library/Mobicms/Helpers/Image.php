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

namespace Mobicms\Helpers;

use Mobicms\Exceptions\RuntimeException;

/**
 * Class Image
 *
 * image(string $image [,array $attributes, boolean $isModule, boolean $generateIMGtag])
 * Supported attributes: alt, width, height, style
 *
 * Example:
 * App::image('image.jpg');                                                       System image
 * App::image('image.jpg', array('width'=>16, 'height'=>16, 'alt'=>'My Image');   System image with attributes
 * App::image('image.jpg', array(), true);                                        Module image
 * App::image('image.jpg', array(), false, false);                                Returns only path to system image file
 *
 * @package Mobicms\Helpers
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class Image
{
    private $img;
    private $args = [];
    private $alt = '';
    private $isModule;
    private $imgTag;

    public function __construct($args)
    {
        if (!isset($args[0]) || empty($args[0])) {
            throw new RuntimeException('Image not specified');
        }

        if (isset($args[1])) {
            if (isset($args[1]['alt'])) {
                $this->alt = $args[1]['alt'];
            }

            if (isset($args[1]['width'])) {
                $this->args[] = 'width="' . $args[1]['width'] . '"';
            }

            if (isset($args[1]['height'])) {
                $this->args[] = 'height="' . $args[1]['height'] . '"';
            }

            if (isset($args[1]['style'])) {
                $this->args[] = 'style="' . $args[1]['style'] . '"';
            }
        }

        $this->isModule = isset($args[2]) && $args[2] === true ? true : false;
        $this->imgTag = isset($args[3]) && $args[3] === false ? false : true;
        $this->img = $args[0];
    }

    public function __toString()
    {
        if ($this->isModule) {
            if (is_file(THEMES_PATH . \App::user()->settings['skin'] . DS . 'modules' . DS . \App::router()->dir . DS . 'images' . DS . $this->img)) {
                // Картинка из текущей темы (если есть)
                $file = \App::cfg()->sys->homeurl . 'themes/' . \App::user()->settings['skin'] . '/modules/' . \App::router()->dir . '/images/' . $this->img;
            } elseif (is_file(ASSETS_PATH . 'modules' . DS . \App::router()->dir . DS . 'images' . DS . $this->img)) {
                // Если нет в теме, то выдаем картинку из модуля
                $file = \App::cfg()->sys->homeurl . 'assets/modules/' . \App::router()->dir . '/images/' . $this->img;
            } else {
                // Если картинка не найдена
                return '';
            }
        } else {
            if (is_file(THEMES_PATH . \App::user()->settings['skin'] . DS . 'images' . DS . $this->img)) {
                // Картинка из текущей темы (если есть)
                $file = \App::cfg()->sys->homeurl . 'themes/' . \App::user()->settings['skin'] . '/images/' . $this->img;
            } elseif (is_file(THEMES_PATH . \App::cfg()->sys->theme_default . DS . 'images' . DS . $this->img)) {
                // Если нет в теме, то выдаем картинку по-умолчанию
                $file = \App::cfg()->sys->homeurl . 'themes/' . \App::cfg()->sys->theme_default . '/images/' . $this->img;
            } else {
                // Если картинка не найдена
                return '';
            }
        }

        return $this->imgTag ? '<img src="' . $file . '" alt="' . $this->alt . '" ' . implode(' ', $this->args) . '/>' : $file;
    }
}
