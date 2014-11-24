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
}