<?php

namespace DG\API\Photo\Transport;

interface TransportInterface
{
    /**
     * @param string $methodName
     * @param array $params
     * @param string $httpMethod
     * @return TransportResult
     */
    public function makeRequest($methodName, array $params, $httpMethod);
} 