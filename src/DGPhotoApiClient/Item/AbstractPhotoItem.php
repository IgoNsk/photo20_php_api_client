<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 18.02.15
 * Time: 9:54
 */

namespace DG\API\Photo\Item;

abstract class AbstractPhotoItem
{
    /** @var int */
    protected $_id;

    /** @var bool */
    protected $_isDeleted = false;

    /** @var bool */
    private $_isChanged = false;

    /** @var array */
    protected $error;

    /** @var array */
    private $_changedFields = [];

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

    public function setError(array $error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    protected function wasChanged($fieldName = null)
    {
        $this->_isChanged = true;

        if ($fieldName !== null) {
            $this->_changedFields[] = $fieldName;
        }
    }

    public function isChangedField($fieldName)
    {
        return in_array($fieldName, $this->_changedFields);
    }

    public function flushChanged()
    {
        $this->_isChanged = false;
        $this->_changedFields = [];
    }
}