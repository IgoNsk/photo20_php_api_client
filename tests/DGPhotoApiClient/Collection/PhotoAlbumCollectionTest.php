<?php

use DG\API\Photo\Collection\PhotoAlbumCollection;
use \DG\API\Photo\Item\RemotePhotoItem;
use \DG\API\Photo\Item\Copyright\TextCopyright;

class PhotoAlbumCollectionTest extends \PHPUnit_Framework_TestCase
{
    private function getItems()
    {
        return [
            new RemotePhotoItem(
                1, 'yandex.ru/i.jpg', 'yandex.ru/i_32_32.jpg', 'Kitten', 1, RemotePhotoItem::STATUS_ACTIVE,
                new TextCopyright('Copyright', '4sqr.com'),
                time(), time(), 'comment'
                ),
            new RemotePhotoItem(
                2, 'yandex.ru/i.jpg', 'yandex.ru/i_32_32.jpg', 'Kitten', 2, RemotePhotoItem::STATUS_HIDDEN,
                new TextCopyright('Copyright', '4sqr.com'),
                time(), time(), 'comment'
            ),
            new RemotePhotoItem(
                3, 'yandex.ru/i.jpg', 'yandex.ru/i_32_32.jpg', 'Kitten', 3, RemotePhotoItem::STATUS_ACTIVE,
                new TextCopyright('Copyright', '4sqr.com'),
                time(), time(), 'comment'
            ),
        ];
    }

    public function testConstructCollection()
    {
        $code = "common";
        $name = "Фото организаций";
        $collection = new PhotoAlbumCollection($code, $name);

        $this->assertEmpty($collection->getItems());
        $this->assertEquals($code, $collection->getCode());
        $this->assertEquals($name, $collection->getName());
        $this->assertEquals(0, $collection->getCount());

        return $collection;
    }

    /**
     * @param PhotoAlbumCollection $collection
     * @return PhotoAlbumCollection
     * @depends testConstructCollection
     */
    public function testAddItems(PhotoAlbumCollection $collection)
    {
        $items = $this->getItems();
        foreach ($items as $item) {
            $collection->add($item);
        }

        $result = $collection->getItems();
        $this->assertCount(count($items), $result);

        foreach ($result as $resultItem) {
            $this->assertInstanceOf('DG\\API\\Photo\\Item\\RemotePhotoItem', $resultItem, 'Result item is not LocalPhotoItem');
        }

        return $collection;
    }
}