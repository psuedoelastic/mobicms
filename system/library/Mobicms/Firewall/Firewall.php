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

namespace Mobicms\Firewall;

/**
 * Class Firewall
 *
 * @package Mobicms\Environment
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class Firewall
{
    /**
     * @var int The time period for calculating number of allowed requests [sec]
     */
    public $calculatingPeriod = 120;

    /**
     * @var int Set the maximum number of allowed requests per time period
     */
    public $requestsLimit = 40;

    /**
     * @var string File name for log the number of requests for each IP
     */
    public $requestsLogFile = 'ip-requests.log';

    /**
     * @var int Interval for writing LOG file
     */
    public $requestsLogInterval = 10;

    /**
     * @var string File name for cache of IP requests
     */
    public $requestsCacheFile = 'firewall.cache';

    /**
     * @var string File name for cache of IP black/white list
     */
    public $bwlistCacheFile = 'bw-list.cache';

    /**
     * @var int Counter inquiries from the current IP address
     */
    public $count = 1;

    /**
     * Start the Firewall
     * Matches the IP with the black/white lists, check for HTTP flood
     *
     * @param int $ip
     * @throws \Exception
     */
    public function run($ip)
    {
        // Matches the IP with the black/white lists
        $check = true;
        switch ($this->bwListHandler($ip)) {
            case 2:
                // IP is found in the white list
                $check = false;
                break;

            case 1:
                // IP is found in the black list
                header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
                throw new \Exception('Access denied');

            default:
        }

        // Check for HTTP flood
        $this->requestsCount($ip);

        if ($check && $this->count > $this->requestsLimit) {
            throw new \Exception('You have reached the limit of allowed requests. Please wait a few minutes');
        }
    }

    /**
     * Processing the cache of white / black lists of IP
     *
     * @param $ip
     * @return bool|int 0 = not found, 1 = found in the black list, 2 = found in the white list
     */
    private function bwListHandler($ip)
    {
        $file = CACHE_PATH.$this->bwlistCacheFile;

        if (file_exists($file)) {
            $in = fopen($file, 'r');

            while ($block = fread($in, 18)) {
                $arr = unpack('dip/dip_upto/Smode', $block);

                if ($ip >= $arr['ip'] && $ip <= $arr['ip_upto']) {
                    fclose($in);

                    return $arr['mode'];
                }
            }
            fclose($in);
        }

        return false;
    }

    /**
     * Check for HTTP Flood attack
     *
     * @param $ip
     * @return int
     * @throws \Exception
     */
    private function requestsCount($ip)
    {
        $tmp = [];
        $requests = [];

        $in = fopen(CACHE_PATH.$this->requestsCacheFile, 'c+');

        if (!flock($in, LOCK_EX)) {
            throw new \RuntimeException('firewall cache file is not writable');
        }

        $now = time();

        while ($block = fread($in, 8)) {
            $arr = unpack('Lip/Ltime', $block);

            if (($now - $arr['time']) > $this->calculatingPeriod) {
                continue;
            }

            if ($arr['ip'] == $ip) {
                $this->count++;
            }

            $tmp[] = $arr;
            $requests[] = $arr['ip'];
        }

        fseek($in, 0);
        ftruncate($in, 0);
        $tmpcount = count($tmp);

        for ($i = 0; $i < $tmpcount; $i++) {
            fwrite($in, pack('LL', $tmp[$i]['ip'], $tmp[$i]['time']));
        }

        fwrite($in, pack('LL', $ip, $now));
        fclose($in);
        $this->writeLog($requests, $ip);
    }

    /**
     * Write to LOG the number of calls for each IP
     *
     * @param array $requests
     * @param       $ip
     * @throws \Exception
     */
    private function writeLog(array $requests, $ip)
    {
        $file = LOG_PATH.$this->requestsLogFile;

        if (!is_file($file) || filemtime($file) < time() - $this->requestsLogInterval) {
            $out = 'Date: GMT '.date('d.m.Y H:i:s')."\n";
            $out .= '-----------------------------'."\n";

            if (empty($requests)) {
                $out .= '1::'.long2ip($ip)."\r\n";
            } else {
                $ip_list = array_count_values($requests);
                arsort($ip_list);

                foreach ($ip_list as $key => $val) {
                    $out .= $val.'::'.long2ip($key)."\n";
                }
            }

            if (file_put_contents($file, $out) === false) {
                throw new \RuntimeException('firewall Log file is not writable');
            }
        }
    }
}
