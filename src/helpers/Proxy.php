<?php

namespace xionglonghua\common\helpers;

use yii\web\HttpException;

class Proxy
{
    public $host;
    public $port = 80;

    public function send()
    {
        $req = $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . " HTTP/1.0\r\n";
        foreach ($_SERVER as $k => $v) {
            if (substr($k, 0, 5) == 'HTTP_') {
                $k = str_replace('_', ' ', substr($k, 5));
                $k = str_replace(' ', '-', ucwords(strtolower($k)));
                if ($k == 'Host') {
                    $v = $this->host;
                } elseif ($k == 'Accept-Encoding') {
                    $v = 'identity;q=1.0, *;q=0';
                } elseif ($k == 'Keep-Alive') {
                    continue;
                } elseif ($k == 'Connection' && $v == 'keep-alive') {
                    $v = 'close';
                }
                $req .= "$k: $v\r\n";
            }
        }

        $req .= "Mock: {$_SERVER['HTTP_HOST']}\r\n";
        $req .= "\r\n";
        $req .= @file_get_contents('php://input') . "\r\n";

        if (!$fp = fsockopen($this->host, $this->port, $errno, $errmsg, 30)) {
            throw new HttpException(502, 'Failed to connect remote server', 1);
        }

        fwrite($fp, $req);

        $responseStr = '';
        while (!feof($fp)) {
            $responseStr .= fread($fp, 8192);
        }
        fclose($fp);

        $this->output_response_string($responseStr);
    }

    private function output_response_string($responseStr)
    {
        // Split
        $parts = explode("\r\n\r\n", $responseStr);

        // Send headers
        $headers = array_shift($parts);
        foreach (explode("\r\n", $headers) as $header) {
            $header = trim($header);
            if ($header) {
                header($header);
            }
        }

        // Send body
        echo implode("\r\n\r\n", $parts);
    }
}
