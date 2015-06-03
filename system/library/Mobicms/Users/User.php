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

namespace Mobicms\Users;

use App;

/**
 * Class User
 *
 * @package Mobicms\Users
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class User
{
    public $id = 0;
    public $rights = 0;
    public $ban = [];
    public $data = [];
    public $settings =
        [
            'avatars'    => 1,
            'direct_url' => 0,
            'editor'     => 1,
            'field_h'    => 3,
            'lng'        => '#',
            'page_size'  => 10,
            'skin'       => 'thundercloud', //TODO: Переделать
            'smilies'    => 1,
            'timeshift'  => 0,
        ];

    public function __construct()
    {
        $id = false;
        $token = false;
        $cookie = false;

        if (isset($_SESSION['user_id'], $_SESSION['token'])) {
            // Авторизация по сессии
            $id = $_SESSION['user_id'];
            $token = $_SESSION['token'];
        } elseif (isset($_COOKIE['user_id'], $_COOKIE['token'])
            && is_numeric($_COOKIE['user_id'])
            && $_COOKIE['user_id'] > 0
            && strlen($_COOKIE['token']) == 32
        ) {
            // Авторизация по COOKIE
            $id = intval($_COOKIE['user_id']);
            $token = trim($_COOKIE['token']);
            $cookie = true;
        }

        if ($id && $token) {
            $user = App::db()->query("SELECT * FROM `".TP."user__` WHERE `id` = ".$id);

            if ($user->rowCount()) {
                $this->data = $user->fetch();

                // Допуск на авторизацию с COOKIE
                if ($cookie
                    && $this->data['login_try'] > 2
                    && ($this->data['ip'] != App::network()->getIp() || $this->data['ip_via_proxy'] != App::network()->getIpViaProxy() || $this->data['useragent'] != App::network()->getUserAgent())
                ) {
                    $permit = false;
                } else {
                    $permit = true;
                }

                // Если авторизация прошла успешно
                if ($permit && $token === $this->data['token']) {
                    $this->id = $id;
                    $this->rights = $this->data['rights'];
                    $_SESSION['user_id'] = $id;
                    $_SESSION['token'] = $token;

                    // Получаем пользовательские настройки
                    if (isset($_SESSION['user_set'])) {
                        if ($_SESSION['user_set'] != '#') {
                            $this->settings = unserialize($_SESSION['user_set']);
                        }
                    } else {
                        if (($settings = $this->getData('user_set')) !== false && is_array($settings)) {
                            $this->settings = $settings;
                            $_SESSION['user_set'] = serialize($settings);
                        } else {
                            $_SESSION['user_set'] = '#';
                        }
                    }

                    if ($this->data['ip'] != App::network()->getIp() || $this->data['ip_via_proxy'] != App::network()->getIpViaProxy()) {
                        $this->ip_history();
                    }

                    // Фиксация данных
                    $stmt = App::db()->prepare("
                        UPDATE `".TP."user__`
                        SET
                        `last_visit`   = ?,
                        `ip`           = ?,
                        `ip_via_proxy` = ?,
                        `user_agent`   = ?
                        WHERE `id`     = ?
                    ");

                    $stmt->execute(
                        [
                            time(),
                            App::network()->getIp(),
                            App::network()->getIpViaProxy(),
                            App::network()->getUserAgent(),
                            $id
                        ]
                    );

                    // Проверка на бан
                    if ($this->data['ban']) {
                        $this->check_ban();
                    }
                } else {
                    // Если авторизация не прошла
                    App::db()->exec("
                        UPDATE `".TP."user__` SET
                        `login_try` = ".++$this->data['login_try']."
                        WHERE `id`  = ".$this->data['id']
                    );

                    $this->destroy();
                }
            } else {
                // Если пользователь не существует
                $this->destroy();
            }
        }
    }

    /**
     * Уничтожаем данные авторизации юзера
     *
     * @param bool
     */
    public function destroy($clear_token = false)
    {
        if ($this->id && $clear_token) {
            App::db()->exec("UPDATE `".TP."user__` SET `token` = '' WHERE `id` = ".$this->id);
        }

        $this->id = 0;
        $this->rights = 0;
        $this->data = [];
        setcookie('user_id', '', time() - 3600, '/');
        setcookie('token', '', time() - 3600, '/');
        session_destroy();
    }

    /**
     * Получаем пользовательские настройки
     *
     * @param string $key
     *
     * @return bool|array
     */
    public function getData($key)
    {
        if ($this->id && !empty($key)) {
            $stmt = App::db()->prepare("
                SELECT `value`
                FROM `".TP."user__settings`
                WHERE `user_id` = ?
                AND `key`       = ?
                LIMIT 1
                ");

            $stmt->execute([$this->id, $key]);

            if ($stmt->rowCount()) {
                $result = $stmt->fetch();

                return unserialize($result['value']);
            }
        }

        return false;
    }

    /**
     * Добавляем, обновляем, удаляем пользовательские настройки
     *
     * @param  string $key
     * @param  array  $val
     *
     * @return bool
     */
    public function set_data($key, array $val = [])
    {
        if ($this->id || !empty($key)) {
            if (empty($val)) {
                // Удаляем пользовательские данные
                $stmt = App::db()->prepare("
                    DELETE FROM `".TP."user__settings`
                    WHERE `user_id` = ?
                    AND `key`       = ?
                    LIMIT 1
                    ");

                $stmt->execute(
                    [
                        $this->id,
                        $key
                    ]
                );
            } else {
                $stmt = App::db()->prepare("
                    REPLACE INTO `".TP."user__settings` SET
                    `user_id` = ?,
                    `key`     = ?,
                    `value`   = ?
                    ");

                $stmt->execute(
                    [
                        $this->id,
                        $key,
                        serialize($val)
                    ]
                );
            }

            return true;
        }

        return false;
    }

    /**
     * Проверка пользователя на Бан
     */
    private function check_ban()
    {
        $ban = App::db()->query("
            SELECT *
            FROM `".TP."user__ban`
            WHERE `user_id` = ".$this->id."
            AND `ban_time`  > ".time()
        );

        if ($ban->rowCount()) {
            $this->rights = 0;
            $this->data['rights'] = 0;
            while ($result = $ban->fetch()) {
                $this->ban[$result['ban_type']] = 1;
            }
        }
    }

    /**
     * Фиксация истории адресов IP
     */
    private function ip_history()
    {
        $q = App::db()->prepare("
            SELECT `id`
            FROM `".TP."user__ip`
            WHERE `user_id`    = ?
            AND `ip`           = ?
            AND `ip_via_proxy` = ?
            LIMIT 1
            ");
        $q->execute(
            [
                $this->id,
                App::network()->getIp(),
                App::network()->getIpViaProxy()
            ]
        );

        if ($q->rowCount()) {
            // Обновляем имеющуюся запись
            $result = $q->fetch();
            $stmt = App::db()->prepare("
                UPDATE `".TP."user__ip` SET
                `user_agent` = ?,
                `timestamp`  = ?
                WHERE `id`   = ?
            ");
            $stmt->execute(
                [
                    App::network()->getUserAgent(),
                    time(),
                    $result['id']
                ]
            );
        } else {
            // Вставляем новую запись
            $stmt = App::db()->prepare("
                INSERT INTO `".TP."user__ip`
                SET
                `user_id`      = ?,
                `ip`           = ?,
                `ip_via_proxy` = ?,
                `user_agent`   = ?,
                `timestamp`    = ?
                ");

            $stmt->execute(
                [
                    $this->id,
                    App::network()->getIp(),
                    App::network()->getIpViaProxy(),
                    App::network()->getUserAgent(),
                    time()
                ]
            );
        }
    }
}
