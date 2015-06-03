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

namespace Mobicms\Environment;

/**
 * Class Network
 *
 * @package Mobicms\Environment
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class Network
{
    private $ip = null;
    private $ipViaProxy = null;
    private $userAgent = null;

    /**
     * Obtain an IP address
     *
     * @param bool $string
     * @return int|string
     * @throws \HttpRuntimeException
     */
    public function getIp($string = false)
    {
        if ($this->ip === null) {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])) : false;

            if ($ip !== false) {
                $this->ip = $ip;
            } else {
                throw new \HttpRuntimeException('Invalid IP address');
            }
        }

        return $string ? long2ip($this->ip) : $this->ip;
    }

    /**
     * Obtain an IP via PROXY address
     *
     * @param bool $string Return IP address as string xx.xx.xx.xx
     * @return int|string  IP Address
     */
    public function getIpViaProxy($string = false)
    {
        if ($this->ipViaProxy === null) {
            $this->ipViaProxy = 0;

            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $vars)
            ) {
                foreach ($vars[0] AS $var) {
                    $ip_via_proxy = ip2long($var);

                    if ($ip_via_proxy
                        && $ip_via_proxy != $this->getIp()
                        && !preg_match('#^(10|172\.16|192\.168)\.#', $var)
                    ) {
                        $this->ipViaProxy = sprintf("%u", $ip_via_proxy);
                        break;
                    }
                }
            }
        }

        return $string && $this->ipViaProxy ? long2ip($this->ipViaProxy) : $this->ipViaProxy;
    }

    /**
     * Obtain an User Agent
     *
     * @return string UserAgent
     */
    public function getUserAgent()
    {
        if ($this->userAgent === null) {
            if (isset($_SERVER["HTTP_X_OPERAMINI_PHONE_UA"])
                && strlen(trim($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])) > 5
            ) {
                $this->userAgent = 'OPERA MINI: '.htmlspecialchars(substr(trim($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']), 0, 250));
            } elseif (isset($_SERVER['HTTP_USER_AGENT'])) {
                $this->userAgent = htmlspecialchars(substr(trim($_SERVER['HTTP_USER_AGENT']), 0, 250));
            } else {
                $this->userAgent = 'User Agent not recognised';
            }
        }

        return $this->userAgent;
    }
}
