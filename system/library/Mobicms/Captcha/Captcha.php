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

namespace Mobicms\Captcha;

/**
 * Class Captcha
 *
 * @package Mobicms\Captcha
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class Captcha
{
    /**
     * @var int Image Width
     */
    public $width = 140;

    /**
     * @var int Image Height
     */
    public $height = 50;

    /**
     * @var int Default font size
     */
    public $defaultFontSize = 24;

    /**
     * @var array Individual sizes of fonts (if not present, use default)
     */
    public $customFontSize =
        [
            'baby_blocks.ttf'    => 16,
            'betsy_flanagan.ttf' => 28,
            'karmaticarcade.ttf' => 20,
        ];

    /**
     * @var int The minimum length of Captcha
     */
    public $lenghtMin = 3;

    /**
     * @var int The maximum length of Captcha
     */
    public $lenghtMax = 5;

    /**
     * @var string Symbols used in Captcha
     */
    public $letters = '23456789abcdeghkmnpqsuvxyz';

    /**
     * Captcha code generation
     *
     * @return string
     */
    public function generateCode()
    {
        $lenght = mt_rand($this->lenghtMin, $this->lenghtMax);

        do {
            $capcha = [];

            for ($i = 0; $i < $lenght; $i++) {
                $capcha[$i] = $this->letters[mt_rand(0, strlen($this->letters) - 1)];

                if (mt_rand(0, 1)) {
                    $capcha[$i] = strtoupper($capcha[$i]);
                }
            }

            $code = implode($capcha);
        } while (preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $code));

        return $code;
    }

    /**
     * Captcha image generation
     *
     * @param $string
     * @return string
     */
    public function generateImage($string)
    {
        $textlen = mb_strlen($string);
        $image = imagecreatetruecolor($this->width, $this->height);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));

        $fonts_dir = __DIR__.DS.'fonts'.DS;
        $fonts_list = glob($fonts_dir.'*.ttf');
        $font = basename($fonts_list[mt_rand(0, count($fonts_list) - 1)]);
        $font_size = isset($this->customFontSize[$font]) ? $this->customFontSize[$font] : $this->defaultFontSize;

        $captcha = str_split($string);

        for ($i = 0; $i < $textlen; $i++) {
            $x = ($this->width - $font_size) / $textlen * $i + ($font_size / 2);
            $x = mt_rand($x, $x + 5);
            $y = $this->height - (($this->height - $font_size) / 2);
            $capcolor = imagecolorallocate($image, rand(0, 150), rand(0, 150), rand(0, 150));
            $capangle = rand(-25, 25);
            imagettftext($image, $font_size, $capangle, $x, $y, $capcolor, $fonts_dir.$font, $captcha[$i]);
        }

        ob_start();
        imagepng($image);
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode(ob_get_clean());
    }
}
