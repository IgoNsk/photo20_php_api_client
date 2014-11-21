<?php

namespace DG\API\Photo\Item\Copyright;

class AbstractCopyright
{
    private $_value;
    private $_url;

    public function __construct($value, $url = null)
    {
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
} 