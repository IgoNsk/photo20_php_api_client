<?php

use \DG\API\Photo\Client;
use \DG\API\Photo\Collection\LocalPhotoCollection;
use \DG\API\Photo\Item\LocalPhotoItem;
use \DG\API\Photo\Exception as DGAPIPhotoException;

require_once __DIR__ . '/StubTransport.php';
use DGPhotoApiClient\FunctionalTest\StubTransport as Transport;

class APITest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var \DG\API\Photo\Client
     */
    private $client;

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
        $item = $this->config['get']['100500'];
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
            foreach ($album->getItems() as $photoItem) {
                $originPhoto = $originData['items'][$photoIndex];

                $this->assertInstanceOf('DG\\API\\Photo\\Item\\RemotePhotoItem', $photoItem);
                $this->assertEquals($originPhoto['id'], $photoItem->getId(), 'Id different');
                $this->assertEquals($originPhoto['url'], $photoItem->getUrl(), 'URL different');
                $this->assertEquals($originPhoto['preview_urls'], $photoItem->getPreviews(), 'Preview different');
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

    public function testAddMethod()
    {
        $stub = $this->config['add']['100500'];
        $this->client->setTransport(new Transport($stub['answer']));

        $collection = new LocalPhotoCollection();
        $collection
            ->add( new LocalPhotoItem(100, '/tmp/1.jpg', [
                'description' => 'Photo 1 description',
            ]) )
            ->add( new LocalPhotoItem(200, '/tmp/2.jpg', [
                'description' => 'Photo 2 description',
            ]) )
        ;
        $count = count($collection->getItems());

        $this->assertTrue($this->client->add($collection, $stub['objectType'], $stub['objectId'], $stub['albumCode']));
        $this->assertCount($count, $collection->getItems());

        $originData = json_decode($stub['answer'], true);
        $photoIndex = 0;
        foreach ($collection->getItems() as $item) {
            $originPhoto = $originData['result']['items'][$photoIndex];
            $this->assertInstanceOf('\\DG\\API\\Photo\\Item\\LocalPhotoItem', $item);

            $this->assertEquals($originPhoto['id'], $item->getId());
            $this->assertEquals($originPhoto['uid'], $item->getUID());
            $this->assertEquals($originPhoto['hash'], $item->getHash());
            $photoIndex++;
        }

        return $collection;
    }

    /**
     * @param LocalPhotoCollection $collection
     * @depends testAddMethod
     */
    public function testUploadMethod(LocalPhotoCollection $collection)
    {
        $stub = $this->config['upload']['100500'];
        $this->client->setTransport(new Transport($stub['answer']));

        $result = $this->client->upload($collection);
        $this->assertCount($result->getCount(), $collection->getItems());

        $originData = json_decode($stub['answer'], true);
        $photoIndex = 0;
        foreach ($result->getItems() as $item) {
            $originPhoto = $originData['result']['items'][$photoIndex];

            $this->assertInstanceOf('DG\\API\\Photo\\Item\\RemotePhotoItem', $item);
            $this->assertEquals($originPhoto['id'], $item->getId(), "Id different");
            $this->assertEquals($originPhoto['url'], $item->getUrl(), "Url different");
            $this->assertEquals($originPhoto['preview_urls'], $item->getPreviews(), "PreviewUrl different");
            $this->assertEquals($originPhoto['position'], $item->getPosition(), "Position different");
            $photoIndex++;
        }
    }

    /**
     * @depends testAddMethod
     */
    public function testDeleteMethod()
    {
        $getStub = $this->config['get']['100500'];
        $this->client->setTransport(new Transport($getStub['answer']));
        $collection = $this->client->get($getStub['objectId'], $getStub['objectType'], $getStub['albumCode'])[0];
        $collectionCnt = $collection->getCount();

        $delStub = $this->config['delete']['100500'];

        $item1 = $collection->getFirst();
        $item1->setDeleted();
        $item2 = $collection->getLast();
        $item2->setDeleted();

        $delStub['answer']['result']['total'] = 2;
        $delStub['answer']['result']['items'] = [
            ['code' => 200, 'id' => $item1->getId()],
            ['code' => 200, 'id' => $item2->getId()]
        ];

        $this->client->setTransport(new Transport(json_encode($delStub['answer'])));
        $resCollection = $this->client->delete($collection, $delStub['objectType'], $delStub['objectId']);

        $this->assertInstanceOf('DG\\API\\Photo\\Collection\\PhotoAlbumCollection', $resCollection);
        $this->assertEquals($collectionCnt-2, $resCollection->getCount());
        $this->assertFalse($collection->getItemByUID($item1->getId()));
    }
} 