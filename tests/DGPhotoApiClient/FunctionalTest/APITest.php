<?php

use \DG\API\Photo\Client;
use \DG\API\Photo\Collection\LocalPhotoCollection;
use \DG\API\Photo\Item\LocalPhotoItem;
use \DG\API\Photo\Exception as DGAPIPhotoException;

require_once __DIR__ . '/StubTransport.php';
use DGPhotoApiClient\FunctionalTest\StubTransport as Transport;

class APITest extends \PHPUnit_Framework_TestCase
{
    private $config;

    public function setUp()
    {
        global $config;
        $this->config = $config;

        $this->client = new Client($this->config['apiKey']);
    }

    public function tearDown()
    {
        unset($this->client);
    }

    public function testMethodGet()
    {
        $item = $this->config['items']['100500'];
        $this->client->setTransport(new Transport($item['answer']));

        $r = $this->client->get($item['objectId'], $item['objectType'], $item['albumCode']);

        var_dump($r);
    }

} 