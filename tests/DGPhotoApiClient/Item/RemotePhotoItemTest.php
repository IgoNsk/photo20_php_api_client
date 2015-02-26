<?php

use \DG\API\Photo\Item\RemotePhotoItem;
use \DG\API\Photo\Item\Copyright\TextCopyright;


class RemotePhotoItemTest extends \PHPUnit_Framework_TestCase
{
    private $data;

    private function getAPIAnswerItem()
    {
        return [
            'id' =>1,
            'url' => 'dgis.ru/i.jpg',
            'preview_urls' => ['100x100'=>'dgis.ru/i_32_32.jpg'],
            'description' => 'Тестовая фотография',
            'status' => 'active',
            'position' => 2,
            'copyright' => [
                'type' => 'text',
                'code' => 'foursquare',
                'value' => 'Copyright by 2GIS',
                'url' => 'http://2gis.com/'
            ],
            'modification_time' => date("d.m.y H:i:s"),
            'creation_time' => date("d.m.y H:i:s"),
            'comment' => 'Фотография заблокированна'
        ];
    }

    public function setUp()
    {
        $this->data = $this->getAPIAnswerItem();
    }

    public function tearDown()
    {
        unset($this->client);
    }

    /**
     * @return RemotePhotoItem
     */
    public function testCreateFromAPIResult()
    {
        $data = $this->data;
        $item = RemotePhotoItem::createFromAPIResult($data);

        $this->assertInstanceOf('\\DG\\API\\Photo\\Item\\RemotePhotoItem', $item);
        $this->assertEquals($data['id'], $item->getId(), 'Id different');
        $this->assertEquals($data['url'], $item->getUrl(), 'Url different');
        $this->assertEquals($data['preview_urls'], $item->getPreviews(), 'Preview different');
        $this->assertEquals($data['description'], $item->getDescription(), 'Description different');
        $this->assertEquals($data['status'], $item->getStatus(), 'Status different');
        $this->assertEquals($data['position'], $item->getPosition(), 'Position different');
        $this->assertEquals($data['modification_time'], $item->getModificatedAt(), 'Modificated date different');
        $this->assertEquals($data['creation_time'], $item->getCreatedAt(), 'Created date different');
        $this->assertEquals($data['comment'], $item->getComment(), 'Comment different');

        return $item;
    }

    /**
     * @param RemotePhotoItem $item
     * @depends testCreateFromAPIResult
     */
    public function testCopyright(RemotePhotoItem $item)
    {
        $data = $this->data;
        $copyright = $item->getCopyright();

        $this->assertInstanceOf('\\DG\\API\\Photo\\Item\\Copyright\\TextCopyright', $copyright);
        $this->assertEquals($data['copyright']['code'], $copyright->getCode());
        $this->assertEquals($data['copyright']['value'], $copyright->getValue());
        $this->assertEquals($data['copyright']['url'], $copyright->getUrl());
    }
} 