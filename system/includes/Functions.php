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
//TODO: Удалить и заменить на новую систему
class Functions
{
    /**
     * Антифлуд
     *
     * @return int|bool
     */
    public static function antiFlood()
    {
        switch (App::cfg()->sys->usr_flood_mode) {
            case 1:
                // Адаптивный режим
                $adm = App::db()->query("SELECT COUNT(*) FROM `" . TP . "user__` WHERE `rights` >= 3 AND `last_visit` > " . (time() - 300))->fetchColumn();
                $limit = $adm > 0 ? App::cfg()->sys->usr_flood_day : App::cfg()->sys->usr_flood_night;
                break;
            case 3:
                // День
                $limit = App::cfg()->sys->usr_flood_day;
                break;
            case 4:
                // Ночь
                $limit = App::cfg()->sys->usr_flood_night;
                break;
            default:
                // По умолчанию день / ночь
                $c_time = date('G', time());
                $limit = $c_time > App::cfg()->sys->usr_flood_day && $c_time < App::cfg()->sys->usr_flood_night
                    ? App::cfg()->sys->usr_flood_day
                    : App::cfg()->sys->usr_flood_night;
        }

        if (App::user()->rights > 0) {
            // Для Администрации задаем лимит в 4 секунды
            $limit = 4;
        }

        $flood = App::user()->data['lastpost'] + $limit - time();

        return $flood > 0 ? $flood : 0;
    }

    /**
     * Показываем дату с учетом сдвига времени
     *
     * @param int $var Время в Unix формате
     *
     * @return string
     */
    public static function displayDate($var)
    {
        //TODO: Исправить время сдвига
        $shift = (App::cfg()->sys->timeshift + App::cfg()->sys->timeshift) * 3600;
        if (date('Y', $var) == date('Y', time())) {
            if (date('z', $var + $shift) == date('z', time() + $shift))
                return __('today') . ', ' . date("H:i", $var + $shift);
            if (date('z', $var + $shift) == date('z', time() + $shift) - 1)
                return __('yesterday') . ', ' . date("H:i", $var + $shift);
        }

        return date("d.m.Y / H:i", $var + $shift);
    }

    /**
     * Постраничная навигация
     *
     * @param string $url
     * @param int    $start
     * @param int    $total
     * @param int    $pagesize
     *
     * @return string
     */
    public static function displayPagination($url, $start, $total, $pagesize)
    {
        $neighbors = 1;
        if ($start >= $total) {
            $start = max(0, (int)$total - (((int)$total % (int)$pagesize) == 0 ? $pagesize : ((int)$total % (int)$pagesize)));
        } else {
            $start = max(0, (int)$start - ((int)$start % (int)$pagesize));
        }
        $base_link = '<a class="btn%s" href="' . strtr($url, ['%' => '%%']) . 'page=%d' . '">%s</a>';

        // Кнопка "Назад"
        $out[] = $start == 0 ? '' : '<a class="btn previous" href="' . $url . 'page=' . ($start / $pagesize) . '">«</a>';

        // Кнопка 1-й страницы
        if ($start > $pagesize * $neighbors) {
            $out[] = '<a class="btn pbtn" href="' . $url . 'page=1">1</a>';
        }

        if ($start > $pagesize * ($neighbors + 1)) {
            $out[] = ' ';
        }

        // Кнопки слева от текущей
        for ($nCont = $neighbors; $nCont >= 1; $nCont--) {
            if ($start >= $pagesize * $nCont) {
                $tmpStart = $start - $pagesize * $nCont;
                $out[] = '<a class="btn pbtn" href="' . $url . 'page=' . ($tmpStart / $pagesize + 1) . '">' . ($tmpStart / $pagesize + 1) . '</a>';
            }
        }

        // Кнопка текущей страницы
        $out[] = '<a class="btn btn-primary pbtn" href="' . $url . 'page=' . ($start / $pagesize + 1) . '">' . ($start / $pagesize + 1) . '</a>';

        $tmpMaxPages = (int)(($total - 1) / $pagesize) * $pagesize;

        // Кнопки справа от текущей
        for ($nCont = 1; $nCont <= $neighbors; $nCont++) {
            if ($start + $pagesize * $nCont <= $tmpMaxPages) {
                $tmpStart = $start + $pagesize * $nCont;
                $out[] = '<a class="btn pbtn" href="' . $url . 'page=' . ($tmpStart / $pagesize + 1) . '">' . ($tmpStart / $pagesize + 1) . '</a>';
            }
        }

        if ($start + $pagesize * ($neighbors + 1) < $tmpMaxPages) {
            $out[] = ' ';
        }

        // Кнопка последней страницы
        if ($start + $pagesize * $neighbors < $tmpMaxPages) {
            $out[] = '<a class="btn pbtn" href="' . $url . 'page=' . ($tmpMaxPages / $pagesize + 1) . '">' . ($tmpMaxPages / $pagesize + 1) . '</a>';
        }

        // Кнопка "Вперед"
        if ($start + $pagesize < $total) {
            $display_page = ($start + $pagesize) > $total ? $total : ($start / $pagesize + 2);
            $out[] = sprintf($base_link, ' next', $display_page, '»');
        }

        return '<div class="pagination"><div class="inline-block">' . implode($out) . '</div></div>';
    }

    /**
     * Вычисляем местоположение пользователей
     *
     * @param integer $user_id
     * @param string  $place
     */
    public static function displayPlace($user_id = null, $place = '')
    {
        //TODO: Доработать!
    }

    /**
     * Скачка текстовых данных в виде файла
     *
     * @param $str           Исходный текст
     * @param $file          Имя файла
     *
     * @return bool
     */
    public static function downloadFile($str, $file)
    {
        ob_end_clean();
        ob_start();
        echo $str;
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $file);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . ob_get_length());
        flush();

        return true;
    }

    /**
     * Генерация токена
     *
     * @return string
     */
    public static function generateToken()
    {
        $random = '';
        $length = rand(5, 10);
        for ($i = 0; $i < $length; $i++) {
            $random .= chr(rand(33, 126));
        }

        return md5($random . microtime(true));
    }

    /**
     * Обработка Смайлов
     *
     * @param string $str Исходный текст
     * @param bool   $adm Обрабатывать Админские смайлы
     *
     * @return string        Обработанный текст
     */
    public static function smilies($str, $adm = false)
    {
        static $pattern = [];
        if (empty($pattern)) {
            $file = CACHE_PATH . 'smilies.cache';
            if (file_exists($file) && ($smilies = file_get_contents($file)) !== false) {
                $pattern = unserialize($smilies);
            } else {
                return $str;
            }
        }

        return preg_replace(
            ($adm ? array_merge($pattern['usr_s'], $pattern['adm_s']) : $pattern['usr_s']),
            ($adm ? array_merge($pattern['usr_r'], $pattern['adm_r']) : $pattern['usr_r']),
            $str, 3
        );
    }

    /**
     * Функция пересчета на дни, или часы
     *
     * @param int $var Время в Unix формате
     *
     * @return string
     */
    public static function timeCount($var)
    {
        if ($var < 0) $var = 0;
        $day = ceil($var / 86400);
        if ($var > 345600) return $day . ' ' . __('timecount_days');
        if ($var >= 172800) return $day . ' ' . __('timecount_days_r');
        if ($var >= 86400) return '1 ' . __('timecount_day');

        return date("G:i:s", mktime(0, 0, $var));
    }

    /**
     * Транслитерация текста
     *
     * @param $str
     *
     * @return string
     */
    public static function translit($str)
    {
        $replace = [
            'a'  => 'а',
            'b'  => 'б',
            'v'  => 'в',
            'g'  => 'г',
            'd'  => 'д',
            'e'  => 'е',
            'yo' => 'ё',
            'zh' => 'ж',
            'z'  => 'з',
            'i'  => 'и',
            'j'  => 'й',
            'k'  => 'к',
            'l'  => 'л',
            'm'  => 'м',
            'n'  => 'н',
            'o'  => 'о',
            'p'  => 'п',
            'r'  => 'р',
            's'  => 'с',
            't'  => 'т',
            'u'  => 'у',
            'f'  => 'ф',
            'h'  => 'х',
            'c'  => 'ц',
            'ch' => 'ч',
            'w'  => 'ш',
            'sh' => 'щ',
            'q'  => 'ъ',
            'y'  => 'ы',
            'x'  => 'э',
            'yu' => 'ю',
            'ya' => 'я',
            'A'  => 'А',
            'B'  => 'Б',
            'V'  => 'В',
            'G'  => 'Г',
            'D'  => 'Д',
            'E'  => 'Е',
            'YO' => 'Ё',
            'ZH' => 'Ж',
            'Z'  => 'З',
            'I'  => 'И',
            'J'  => 'Й',
            'K'  => 'К',
            'L'  => 'Л',
            'M'  => 'М',
            'N'  => 'Н',
            'O'  => 'О',
            'P'  => 'П',
            'R'  => 'Р',
            'S'  => 'С',
            'T'  => 'Т',
            'U'  => 'У',
            'F'  => 'Ф',
            'H'  => 'Х',
            'C'  => 'Ц',
            'CH' => 'Ч',
            'W'  => 'Ш',
            'SH' => 'Щ',
            'Q'  => 'Ъ',
            'Y'  => 'Ы',
            'X'  => 'Э',
            'YU' => 'Ю',
            'YA' => 'Я'
        ];

        return strtr($str, $replace);
    }
}
