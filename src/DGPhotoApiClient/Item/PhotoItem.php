<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 18.02.15
 * Time: 9:54
 */

namespace DG\API\Photo\Item;

abstract class PhotoItem {

    protected $_id;

    /** @var bool */
    protected $_isDeleted = false;

    /** @var bool */
    protected $_isChanged = false;

    public function getId()
    {
        return $this->_id;
    }

    public function isDeleted()
    {
        return $this->_isDeleted;
    }

    /**
     * mark item deleted
     */
    public function setDeleted()
    {
        $this->_isDeleted = true;
    }

    public function isChanged()
    {
        return $this->_isChanged;
    }

    protected function wasChanged()
    {
        $this->_isChanged = true;
    }
}