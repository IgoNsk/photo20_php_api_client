<?php

use \DG\API\Photo\Client;

class AbstractPhotoTestCase extends \PHPUnit_Framework_TestCase
{
    /* @var ApiConnection */
    public $client;

    public function setUp()
    {
        $this->client = new Client(API_KEY);
        $this->client->setTransport(new CurlExecTransport());
    }

    public function tearDown()
    {
        unset($this->client);
    }
} 