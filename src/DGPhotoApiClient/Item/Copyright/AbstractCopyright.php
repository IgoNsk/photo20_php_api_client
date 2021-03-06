<?php

namespace DG\API\Photo\Item\Copyright;

class AbstractCopyright
{
    private $_code;
    private $_value;
    private $_url;

    public function __construct($code, $value, $url = null)
    {
        $this->_code = $code;
        $this->_value = $value;
        $this->_url = $url;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function getCode()
    {
        return $this->_code;
    }
} 