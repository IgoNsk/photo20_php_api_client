<?php

use DG\API\Photo\Collection\LocalPhotoCollection;
use DG\API\Photo\Item\LocalPhotoItem;

class LocalPhotoCollectionTest extends \PHPUnit_Framework_TestCase
{
    private function getItems()
    {
        return [
            new LocalPhotoItem(100, '/tmp/1.jpg', [
                'description' => 'Photo 1 description',
            ]),
            new LocalPhotoItem(200, '/tmp/2.jpg', [
                'description' => 'Photo 2 description',
            ]),
            new LocalPhotoItem(300, '/tmp/3.jpg', [
                'description' => 'Photo 3 description',
            ])
        ];
    }

    public function testConstructCollection()
    {
        $collection = new LocalPhotoCollection();

        $this->assertEmpty($collection->getItems());
        return $collection;
    }

    /**
     * @param LocalPhotoCollection $collection
     * @return LocalPhotoCollection
     * @depends testConstructCollection
     */
    public function testAddItems(LocalPhotoCollection $collection)
    {
        $items = $this->getItems();
        foreach ($items as $item) {
            $collection->add($item);
        }

        $result = $collection->getItems();
        $this->assertCount(count($items), $result);

        foreach ($result as $resultItem) {
            $this->assertInstanceOf('DG\\API\\Photo\\Item\\LocalPhotoItem', $resultItem, 'Result item is not LocalPhotoItem');
        }

        return $collection;
    }


    /**
     * @param LocalPhotoCollection $collection
     * @depends testAddItems
     */
    public function testSetItemDataByUID(LocalPhotoCollection $collection)
    {
        $uid = 100;
        $id = '123123';
        $hash  = md5($id);
        $data = "someData";

        $this->assertTrue($collection->setItemDataByUID($uid, $id, $hash, $data), 'Edit existing item is failed');

        $item = $collection->getItems()[$uid];
        $this->assertInstanceOf('DG\\API\\Photo\\Item\\LocalPhotoItem', $item);

        $this->assertEquals($item->getId(), $id);
        $this->assertEquals($item->getHash(), $hash);
        $this->assertEquals($item->getData(), $data);
    }
}