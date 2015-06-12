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

namespace Mobicms\Session;

/**
 * Class SessionHandler
 *
 * @package Mobicms\Session
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class SessionHandler implements \SessionHandlerInterface
{
    private $movings = 1;
    private $place = '';
    private $timestamp = 0;
    private $views = 1;

    /**
     * @var string session.name
     */
    public $sessionName = 'SID';

    /**
     * @var int session.gc_maxlifetime
     */
    public $sessionLifeTime = 86400;

    public function __construct()
    {
        ini_set('session.use_trans_sid', '0');
        ini_set('session.use_cookies', true);
        ini_set('session.use_only_cookies', true);
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '100');

        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );
    }

    public function run()
    {
        session_name($this->sessionName);
        session_set_cookie_params($this->sessionLifeTime, '/');
        session_start();
        setcookie(session_name(), session_id(), (time() + $this->sessionLifeTime), '/');
    }

    /**
     * Open Session
     *
     * @param string $savePath
     * @param string $sessionId
     * @return bool
     */
    public function open($savePath, $sessionId)
    {
        return true;
    }

    /**
     * Close Session
     *
     * @return bool true
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        $stmt = \App::db()->prepare("
            SELECT *
            FROM `system__sessions`
            WHERE `session_id` = ?
            FOR UPDATE
            ");

        $stmt->execute([$sessionId]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetch();
            $this->movings = $result['movings'];
            $this->place = $result['place'];
            $this->timestamp = $result['session_timestamp'];
            $this->views = $result['views'];

            return $result['session_data'];
        } else {
            $stmt = \App::db()->prepare("
                INSERT INTO `system__sessions`
                SET
                `session_id`        = ?,
                `session_timestamp` = ?,
                `session_data`      = ?
                ");

            $stmt->execute(
                [
                    $sessionId,
                    time(),
                    ''
                ]
            );

            return '';
        }
    }

    /**
     * Write session data
     *
     * @param string $sessionId
     * @param string $data
     * @return bool
     */
    public function write($sessionId, $data)
    {
        if ($this->timestamp > (time() - 300)) {
            ++$this->views;
        } else {
            $this->movings = 1;
            $this->views = 1;
        }

        $stmt = \App::db()->prepare("
            UPDATE `system__sessions`
            SET
            `session_timestamp` = ?,
            `session_data`      = ?,
            `user_id`           = ?,
            `ip`                = ?,
            `ip_via_proxy`      = ?,
            `user_agent`        = ?,
            `place`             = ?,
            `views`             = ?,
            `movings`           = ?
            WHERE `session_id`  = ?
            ");

        $stmt->execute(
            [
                time(),
                $data,
                \App::user()->id,
                \App::network()->getIp(),
                \App::network()->getIpViaProxy(),
                \App::network()->getUserAgent(),
                \App::router()->getModule(), //TODO: разобраться с местоположением
                $this->views,
                $this->movings,
                $sessionId
            ]
        );

        return true;
    }

    /**
     * Destroy Session
     *
     * @param string $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        $stmt = \App::db()->prepare("
            DELETE FROM `system__sessions`
            WHERE `session_id` = ?
            ");

        $stmt->execute([$sessionId]);

        return true;
    }

    /**
     * Garbage collector
     *
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        //TODO: Обдумать использование $maxlifetime
        $stmt = \App::db()->prepare("
            DELETE FROM `system__sessions`
            WHERE `session_timestamp` < ?
            ");

        $stmt->execute(
            [
                time() - $this->sessionLifeTime
            ]
        );

        return true;
    }
}
