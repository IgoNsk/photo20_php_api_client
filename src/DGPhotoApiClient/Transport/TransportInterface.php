<?php

namespace DG\API\Photo\Transport;

interface TransportInterface
{
    /**
     * @param string $methodName
     * @param array $params
     * @param string $httpMethod
     * @param array $headers
     *
     * @return TransportResult|null
     */
    public function makeRequest($methodName, array $params, $httpMethod, array $headers = []);
} 