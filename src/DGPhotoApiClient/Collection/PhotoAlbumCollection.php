<?php
namespace DG\API\Photo\Collection;

use \DG\API\Photo\Item\RemotePhotoItem;

class PhotoAlbumCollection extends AbstractAlbumCollection
{
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
    public function addItems(array $remotePhotoItems) {
        foreach ($remotePhotoItems as $item) {
            $this->add($item);
        }
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

    /**
     * @param $position
     * @return RemotePhotoItem|null
     */
    public function getItemByPosition($position) {
        /* @var \DG\API\Photo\Item\RemotePhotoItem $item*/
        foreach ($this->_items as $item) {
            if ($item->getPosition() == $position) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param $number
     * @return RemotePhotoItem|null
     */
    public function getItemByNumber($number) {
        $item = array_slice($this->_items, $number, 1);
        return !empty($item) ? reset($item) : null;
    }

    public function moveFirstToEnd() {
        $firstItem = $this->getFirst();
        $lastItem = $this->getLast();
        $firstItem->setPosition($lastItem->getPosition());
        $lastItem->setPosition($lastItem->getPosition() + 1, false);
        $this->sortByPosition();
    }

    public function moveLastToStart() {
        $lastItem = $this->getLast();
        $this->incrementPositions(0);
        $lastItem->setPosition(0);
        $this->sortByPosition();
    }

    public function inversePositions() {
        $newPositions = [];
        /* @var \DG\API\Photo\Item\RemotePhotoItem $item*/
        foreach ($this->_items as $item) {
            $newPositions[$item->getId()] = $item->getPosition();
        }
        $this->sortByPosition('desc');
        foreach ($this->_items as $item) {
            $item->setPosition($newPositions[$item->getId()]);
        }
    }

    /**
     * @param $incrementPositionStart
     */
    public function incrementPositions($incrementPositionStart) {

        $nextPosition = $incrementPositionStart;
        /* @var \DG\API\Photo\Item\RemotePhotoItem $item*/
        while (($item = $this->getItemByPosition($nextPosition)) && $this->getLast()->getPosition() >= $nextPosition) {
            if ($item) {
                $item->setPosition($nextPosition + 1, false);
                break;
            }
            $nextPosition++;
        }
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

    public function removeItemByUID($uid)
    {
        if (!isset($this->_items[$uid])) {
            return false;
        }

        unset ($this->_items[$uid]);
    }

}