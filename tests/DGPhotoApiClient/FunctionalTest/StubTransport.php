<?php

namespace DGPhotoApiClient\FunctionalTest;

use DG\API\Photo\Transport\TransportInterface;
use DG\API\Photo\Transport\TransportResult;

class StubTransport implements TransportInterface
{
    private $result;

    public function __construct($result, $returnCode = 200)
    {
        $this->result = new TransportResult($result, [
            'cmd' => null,
            'res' => $result,
            'returnCode' => $returnCode
        ]);
    }

    public function makeRequest($methodName, array $params, $httpMethod)
    {
        return $this->result;
    }
} 