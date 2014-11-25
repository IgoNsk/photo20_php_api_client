<?php

namespace DG\API\Photo;

use \DG\API\Photo\Transport\TransportInterface;

abstract class AbstractClient
{
    const HTTP_POST = 'POST';
    const HTTP_GET = 'GET';

    const FORMAT_JSON = 'json';
    const FORMAT_JSONP = 'jsonp';

    const LOCALE_RU_RU = 'ru_RU';
    const LOCALE_EN_GB = 'en_GB';
    const LOCALE_EN_US = 'en_US';

    protected $_apiKey;
    protected $_format;
    protected $_locale;
    protected $_onResult;

    /**
     * @var TransportInterface
     */
    protected $transport;

    public function __construct($apiKey, $format = self::FORMAT_JSON, $locale = self::LOCALE_RU_RU)
    {
        $this->_apiKey = $apiKey;
        $this->_format = $format;
        $this->_locale = $locale;
    }

    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    protected function onResult($jsonResult, $methodName, $params, $httpMethod, $result)
    {
        if($this->_onResult && is_callable($this->_onResult))
        {
            return call_user_func_array($this->_onResult, func_get_args());
        }

        return false;
    }

    protected function callMethod($methodName, array $params = [], $httpMethod = self::HTTP_POST)
    {
        $httpCode = 200;

        try
        {
            $result = $this->transport->makeRequest($methodName, $params, $httpMethod);
            if (!$result) {
                $httpCode = 500;
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        if ($httpCode != 200) {
            /**
             * @TODO error
             */
            throw new Exception('Result code is not 200');
        }

        $res = $result->result;
        $this->onResult($res, $methodName, $params, $httpMethod, $result->getParams());

        return $res;
    }

    protected function parseResponse($response, $format = self::FORMAT_JSON)
    {
        $resJson = json_decode($response, true);

        if (!$resJson) {
            throw new Exception('Result is not JSON');
        }

        return $resJson;
    }

    public function makeRequest($methodName, array $params, $httpMethod)
    {
        $res = $this->callMethod($methodName, $params, $httpMethod);

        $resJson = $this->parseResponse($res, $this->_format);

        return $resJson;
    }

    protected function extendParams(array $params = [], array $fields = ['key', 'format', 'locale'])
    {
        $systemFields = [
            'key' => $this->_apiKey,
            'format' => $this->_format,
            'locale' => $this->_locale,
        ];

        foreach($fields as $fieldKey)
        {
            $params[$fieldKey] = $systemFields[$fieldKey];
        }

        return $params;
    }

    public function setOnResult($onResult)
    {
        $this->_onResult = $onResult;

        return $this;
    }

    public function getOnResult()
    {
        return $this->_onResult;
    }
} 