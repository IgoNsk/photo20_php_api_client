<?php

namespace DG\API\Photo\Transport;

/**
 * @DESC php curl cant work with multi-array arguments normally with file attache
 */
class CurlExecTransport implements TransportInterface
{
    const HTTP_POST = 'POST';
    const HTTP_GET = 'GET';

    private $apiUrl;

    public function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    private function getCurlExecString($methodName, array $params = [], $httpMethod = self::HTTP_POST, array $headers = [])
    {
        $fx = function($fx, $prefix, $ar) use ($httpMethod) {
            $res = [];

            foreach ($ar as $k => $v) {
                if(is_array($v)) {
                    $r = $fx($fx, ($prefix ? $prefix.'['.$k.']' : $k), $v);
                    foreach ($r as $vx) {
                        $res[] = $vx;
                    }
                }
                else {
                    switch ($httpMethod){
                        case self::HTTP_POST:
                            $paramName = '--form';
                            break;

                        case self::HTTP_GET:
                            $paramName = '--data';
                            break;
                    }
                    if (is_string($v)) {
                        $v = str_replace(["'", '\\'], ["\\'", '\\\\'], $v);
                    } elseif (is_bool($v)) {
                        $v = $v ? 'true' : 'false';
                    }
                    $res[] = $paramName.' '.($prefix ? $prefix.'['.$k.']' : $k) . "='$v'";
                }
            }

            return $res;
        };

        $cmd = '/usr/bin/curl -s -X '.$httpMethod.' \''.$this->apiUrl.$methodName.'\' ';

        foreach ($headers as $headerName=>$headerVal) {
            $cmd .= ' --header "' . $headerName . ': ' . $headerVal . '"';
        }

        if ($httpMethod == self::HTTP_GET) {
            $cmd .= '--get ';
        }

        $args = $fx($fx, '', $params);
        $cmd .= implode(' ', $args);

        return $cmd;
    }

    public function makeRequest($methodName, array $params, $httpMethod, array $headers = [])
    {
        $cmd = $this->getCurlExecString($methodName, $params, $httpMethod, $headers);
        exec($cmd, $res, $returnCode);

        if ($returnCode != 0) {
            return null;
        }
        $res = implode('', $res);
        $result = new TransportResult($res, [
            'cmd' => $cmd,
            'res' => $res,
            'returnCode' => $returnCode
        ]);

        return $result;
    }
} 