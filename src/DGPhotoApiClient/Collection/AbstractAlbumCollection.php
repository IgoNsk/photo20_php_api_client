<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 18.02.15
 * Time: 10:01
 */

namespace DG\API\Photo\Collection;

use \DG\API\Photo\Item\AbstractPhotoItem as PhotoItem;

abstract class AbstractAlbumCollection {

    protected $_items = [];

    /**
     * @return PhotoItem[]
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this->_items);
    }

    /**
     * @return PhotoItem
     */
    public function getLast()
    {
        return end($this->_items);
    }

    /**
     * @return PhotoItem
     */
    public function getFirst()
    {
        return reset($this->_items);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->_items);
    }

    /**
     * @return bool|PhotoItem
     */
    public function getItemByUID($uid)
    {
        if (!isset($this->_items[$uid])) {
            return false;
        }

        return $this->_items[$uid];
    }

}