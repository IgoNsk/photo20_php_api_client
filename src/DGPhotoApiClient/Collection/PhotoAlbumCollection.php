<?php
namespace DG\API\Photo\Collection;

use \DG\API\Photo\Item\RemotePhotoItem;

class PhotoAlbumCollection
{
    private $_items = [];
    private $_code;
    private $_name;

    public function __construct($code, $name)
    {
        $this->_code = $code;
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function add(RemotePhotoItem $item)
    {
        $this->_items[$item->getId()] = $item;

        return $this;
    }

    /**
     * @param array $remotePhotoItems
     */
    public function addItems($remotePhotoItems) {
        foreach ($remotePhotoItems as $item) {
            $this->add($item);
        }
    }

    /**
     * @return RemotePhotoItem[]
     */
    public function getItems()
    {
        return $this->_items;
    }

    public function getCount()
    {
        return count($this->getItems());
    }

    public function isEmpty() {
        return empty($this->_items);
    }

    /**
     * @param string $order = 'asc'|'desc'
     */
    public function sortByPosition($order = 'asc') {
        uasort($this->_items, function ($a, $b) use ($order) {
            /**
             * @var \DG\API\Photo\Item\RemotePhotoItem $a
             * @var \DG\API\Photo\Item\RemotePhotoItem $b
             */
            if ($order = 'asc') {

                return $a->getPosition() < $b->getPosition() ? -1 : $a->getPosition() != $b->getPosition();
            }
            else {
                return $a->getPosition() > $b->getPosition() ? -1 : $a->getPosition() != $b->getPosition();
            }
        });
    }

    public function getFirst() {
        return reset($this->_items);
    }
    public function getLast() {
        return end($this->_items);
    }

    public function moveFirstToEnd() {
        $firstItem = $this->getFirst();
        $lastItem = $this->getLast();
        $firstItem->setPosition($lastItem->getPosition());
        $lastItem->setPosition($lastItem->getPosition() + 1, false);
        $this->sortByPosition();
    }

    protected function removePositionDistances() {

        $this->sortByPosition();
        /* @var \DG\API\Photo\Item\RemotePhotoItem $item*/
        foreach ($this->_items as $index => $item) {
            $item->setPosition($index, false);
        }
    }

    public function prepareForAssertion() {

        /* @var \DG\API\Photo\Item\RemotePhotoItem $item*/
        foreach ($this->_items as $item) {
            $item->prepareForAssertion();
        }
        $this->removePositionDistances();
    }
}