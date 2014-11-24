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

        $originDatas = json_decode($item['answer'], true);
        foreach ($r as $index=>$album) {
            $originData = $originDatas['result'][$index];

            $this->assertInstanceOf('DG\\API\\Photo\\Collection\\PhotoAlbumCollection', $album);
            $this->assertEquals($originData['album_name'], $album->getName());
            $this->assertEquals($originData['album_code'], $album->getCode());
            $this->assertEquals($originData['total'], $album->getCount());

            $photoIndex = 0;
            foreach ($album->getItems() as $photoItem){
                $originPhoto = $originData['items'][$photoIndex];
                /**
                 * @var $photoItem DG\API\Photo\Item\RemotePhotoItem
                 */
                $this->assertInstanceOf('DG\\API\\Photo\\Item\\RemotePhotoItem', $photoItem);
                $this->assertEquals($originPhoto['id'], $photoItem->getId(), 'Id different');
                $this->assertEquals($originPhoto['url'], $photoItem->getUrl(), 'URL different');
                $this->assertEquals($originPhoto['preview_url'], $photoItem->getPreview(), 'Preview different');
                $this->assertEquals($originPhoto['description'], $photoItem->getDescription(), 'Description different');
                $this->assertEquals($originPhoto['status'], $photoItem->getStatus(), 'Status different');
                $this->assertEquals($originPhoto['position'], $photoItem->getPosition(), 'Position different');
                $this->assertEquals($originPhoto['modification_time'], $photoItem->getModificatedAt(), "Modification time different");
                $this->assertEquals($originPhoto['creation_time'], $photoItem->getCreatedAt(), "Created time different");
                $this->assertEquals($originPhoto['comment'], $photoItem->getComment(), "Comment different");

                $copyright = $photoItem->getCopyright();
                $this->assertInstanceOf('DG\\API\\Photo\\Item\\Copyright\\AbstractCopyright', $copyright);
                $this->assertEquals($originPhoto['copyright']['url'], $copyright->getUrl());
                $this->assertEquals($originPhoto['copyright']['value'], $copyright->getValue());
                $photoIndex++;
            }
        }
    }

} 