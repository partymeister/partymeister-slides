<?php

namespace Partymeister\Slides\Services;

use Exception;
use Partymeister\Slides\Models\SlideClient;
use Partymeister\Slides\Services\XMLService\Generator;

/**
 * Class XMLService
 */
class XMLService
{
    /**
     * @param    $method
     * @param  array  $parameters
     * @param  bool  $send
     * @param  bool  $debug
     * @return bool|string
     */
    public static function send($method, $parameters = [], $send = true, $debug = false)
    {
        $xml = Generator::$method($parameters);

        if ($send) {
            return self::_send($xml);
        }

        if ($debug) {
            return $xml;
        }
    }

    /**
     * @param    $xml
     * @param  null  $screen
     * @return bool|string
     */
    public static function _send($xml, $screen = null)
    {
        if ($screen == null) {
            $screen = SlideClient::find(session('screens.active'));
            if ($screen == null) {
                return false;
            }
        }

        try {
            $fp = fsockopen($screen->ip_address, $screen->port, $errno, $errstr, 2);
            if (! $fp) {
                return "$errstr - $errno";
            } else {
                $retval = '';
                fwrite($fp, $xml."\r\n\r\n");
                while (! feof($fp)) {
                    $retval .= fgets($fp, 128);
                }
                fclose($fp);
            }

            return $retval;
        } catch (Exception $e) {
            return false;
        }
    }
}
