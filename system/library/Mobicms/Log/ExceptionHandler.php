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

namespace Mobicms\Log;

/**
 * Class ExceptionHandler
 *
 * @package Mobicms\Log
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-06-03
 */
class ExceptionHandler
{
    public function __construct(\Exception $e)
    {
        $out = $this->format($e);
        echo '<div style="background-color: #fedad7; margin: 24px; padding: 0 12px; border: 1px solid red; display: inline-block">'.
            '<pre>'.$out.'</pre>'.
            '</div>';

        if (DEBUG) {
            error_log($out);
        }
    }

    /**
     * Format the event for the Exception
     *
     * @param \Exception $event
     * @return string
     */
    private function format(\Exception $event)
    {
        ob_get_level() && ob_clean();

        $out = "\n================================================================================\n";
        $out .= wordwrap('EXCEPTION: '.$event->getMessage(), 80, "\n", true);
        $out .= "\nFILE: ".$event->getFile();
        $out .= "\nLINE: ".$event->getLine()."\n";
        $tracearray = $event->getTrace();

        if (!empty($tracearray)) {
            $out .= '--------------------------------------------------------------------------------';
            foreach ($tracearray as $trace) {
                if (isset($trace['file'])) {
                    $out .= "\nTrace File: ".$trace['file'];
                }

                if (isset($trace['line'])) {
                    $out .= '('.$trace['line'].')';
                }

                if (isset($trace['class'], $trace['type'], $trace['function'])) {
                    $out .= "\nTrace Call: {$trace['class']}{$trace['type']}{$trace['function']}()\n";
                } elseif (isset($trace['function'])) {
                    $out .= "\nTrace Func: {$trace['function']}()\n";
                }

                if (!empty($trace['args'])) {
                    foreach ($trace['args'] as $key => $val) {
                        if (empty($val)) {
                            unset($trace['args'][$key]);
                        }
                    }
                }

                if (!empty($trace['args'])) {
                    $out .= "Args: ".print_r($trace['args'], true);
                }
            }
        }

        $out .= '================================================================================';

        return $out;
    }
}