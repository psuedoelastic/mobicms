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

namespace Mobicms\Form;

/**
 * Class Validate
 *
 * @package Mobicms\Form
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class Validate
{
    private static $_userData = null;

    public $error = [];
    public $is = false;

    public function __construct($type, $value, array $option = [])
    {
        if (method_exists($this, $type)) {
            $option['value'] = $value;
            $this->is = call_user_func([$this, $type], $option);
        } else {
            $this->error[] = 'Unknown Validator';
        }
    }

    public static function getUserData()
    {
        return (self::$_userData === null ? false : self::$_userData);
    }

    private function captcha(array $option)
    {
        if (isset($_SESSION['captcha'], $option['value'])
            && strtoupper($_SESSION['captcha']) == strtoupper($option['value'])
        ) {
            return true;
        }
        $this->error[] = __('error_wrong_captcha');

        return false;
    }

    /**
     * Проверка Логина по базе пользователей
     *
     * @param array $option
     *
     * @return bool
     */
    private function login(array $option)
    {
        $stmt = \App::db()->prepare("
            SELECT * FROM `".TP."user__`
            WHERE `".($this->email($option, false) ? 'email' : 'nickname')."` = ?
            LIMIT 1
        ");

        $stmt->execute([$option['value']]);

        if ($stmt->rowCount()) {
            self::$_userData = $stmt->fetch();

            return true;
        } else {
            $this->error[] = __('error_user_not_exist');

            return false;
        }
    }

    /**
     * Проверка пароля
     *
     * @param array $option
     *
     * @return bool
     */
    private function password(array $option)
    {
        if (self::$_userData === null && \App::user()->id) {
            self::$_userData = &\App::user()->data;
        }

        if (self::$_userData !== null) {
            if (password_verify($option['value'], self::$_userData['password'])) {
                return true;
            } else {
                $this->error[] = __('error_wrong_password');

                // Накручиваем счетчик неудачных логинов
                if (!\App::user()->id && self::$_userData['login_try'] < 3) {
                    \App::db()->exec("UPDATE `".TP."user__` SET `login_try` = ".++self::$_userData['login_try']." WHERE `id` = ".self::$_userData['id']);
                    self::$_userData = null;
                }
            }
        }

        return false;
    }

    /**
     * Валидация длины строки
     *
     * @param array $option
     *
     * @return bool
     */
    private function lenght(array $option)
    {
        if (isset($option['empty']) && $option['empty'] && empty($option['value'])) {
            return true;
        }

        if (isset($option['min']) && mb_strlen($option['value']) < $option['min']) {
            $this->error[] = __('minimum').'&#160;'.$option['min'].' '.__('characters');

            return false;
        } elseif (isset($option['max']) && mb_strlen($option['value']) > $option['max']) {
            $this->error[] = __('maximum').'&#160;'.$option['max'].' '.__('characters');

            return false;
        }

        return true;
    }

    /**
     * Валидация числового значения
     *
     * @param array $option
     *
     * @return bool
     */
    private function numeric(array $option)
    {
        if (isset($option['empty']) && $option['empty'] && empty($option['value'])) {
            return true;
        }

        if (!is_numeric($option['value'])) {
            $this->error[] = __('must_be_a_number');

            return false;
        }

        if (isset($option['min']) && $option['value'] < $option['min']) {
            $this->error[] = __('minimum').'&#160;'.$option['min'];

            return false;
        } elseif (isset($option['max']) && $option['value'] > $option['max']) {
            $this->error[] = __('maximum').'&#160;'.$option['max'];

            return false;
        }

        return true;
    }

    /**
     * Валидация Email адреса
     *
     * @param array $option
     * @param bool  $log
     *
     * @return bool
     */
    protected function email(array $option, $log = true)
    {
        if (isset($option['empty']) && $option['empty'] && empty($option['value'])) {
            return true;
        }

        if (!filter_var($option['value'], FILTER_VALIDATE_EMAIL)) {
            if ($log) {
                $this->error[] = __('error_email');
            }

            return false;
        }

        return true;
    }

    /**
     * Валидация IPv4 адреса
     *
     * @param array $option
     *
     * @return bool
     */
    protected function ip(array $option)
    {
        if (filter_var($option['value'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }

        $this->error[] = __('error_ip');

        return false;
    }

    /**
     * Валидация ника
     *
     * @param array $option
     *
     * @return bool
     */
    private function nickname(array $option)
    {
        if (preg_match('/[^\da-zа-я\-\.\ \@\*\(\)\?\!\~\_\=\[\]]+/iu', $option['value'])) {
            $this->error[] = __('error_wrong_symbols');
        } elseif (preg_match('~(([a-z]+)([а-я]+)|([а-я]+)([a-z]+))~iu', $option['value'])) {
            $this->error[] = __('error_double_charset');
        } elseif (filter_var($option['value'], FILTER_VALIDATE_INT) !== false && !\App::cfg()->sys->usr_nickname_digits_only) {
            $this->error[] = __('error_digits_only');
        } elseif (preg_match("/(.)\\1\\1\\1/", $option['value'])) {
            $this->error[] = __('error_recurring_characters');
        } elseif ($this->email($option, false)) {
            $this->error[] = __('error_email_login');
        } else {
            return true;
        }

        return false;
    }

    /**
     * Сравнение двух значений
     *
     * @param array $option
     *
     * @return bool
     */
    private function compare(array $option)
    {
        if (!isset($option['value']) || !isset($option['compare_value'])) {
            $this->error[] = 'ERROR: missing parameter';

            return false;
        }

        if ($option['value'] == $option['compare_value']) {
            return true;
        }

        $this->error[] = isset($option['error']) ? $option['error'] : __('values_not_match');

        return false;
    }

    /**
     * Проверка ника на занятость
     *
     * @param array $option
     *
     * @return bool
     */
    private function nickoccupied(array $option)
    {
        $stmt = \App::db()->prepare("
            SELECT COUNT(*) FROM `".TP."user__`
            WHERE `nickname` = ?
        ");

        $stmt->execute([$option['value']]);
        if (!$stmt->fetchColumn()) {
            return true;
        }

        $this->error[] = __('error_nick_occupied');

        return false;
    }

    /**
     * Проверка Email на занятость
     *
     * @param array $option
     *
     * @return bool
     */
    private function emailoccupied(array $option)
    {
        $stmt = \App::db()->prepare("
            SELECT COUNT(*) FROM `".TP."user__`
            WHERE `email` = ?
        ");

        $stmt->execute([$option['value']]);
        if (!$stmt->fetchColumn()) {
            return true;
        }

        $this->error[] = __('error_email_occupied');

        return false;
    }
}
