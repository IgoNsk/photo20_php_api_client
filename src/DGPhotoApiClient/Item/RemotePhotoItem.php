<?php

namespace DG\API\Photo\Item;

use \DG\API\Photo\Item\Copyright\AbstractCopyright;
use \DG\API\Photo\Item\Copyright\CopyrightFactory;

class RemotePhotoItem extends AbstractPhotoItem
{
    const STATUS_BLOCKED  = 'blocked';
    const STATUS_HIDDEN   = 'hidden';
    const STATUS_ACTIVE   = 'active';

    /**
     * @var string
     */
    private $_url;

    /**
     * @var array
     */
    private $_previews;

    /**
     * @var string
     */
    private $_description;
    /**
     * @var string
     */
    private $_status;

    /**
     * @var integer
     */
    private $_position;

    /**
     * @var AbstractCopyright
     */
    private $_copyright;

    /**
     * @var string
     */
    private $_createdAt;

    /**
     * @var string
     */
    private $_modificatedAt;

    /**
     * @var string
     */
    private $_comment;

    /**
     * @var bool
     */
    private $_isMain;

    /**
     * Создаем объект фотографии на основании данных JSON переданных из API
     * @param array $result
     * @return RemotePhotoItem
     */
    public static function createFromAPIResult(array $result)
    {
        $copyrightData = $result['copyright'];
        $copyright = CopyrightFactory::create($copyrightData['type'], $copyrightData['code'], $copyrightData['value'], $copyrightData['url']);
        return new static(
            $result['id'],
            $result['url'],
            $result['preview_urls'],
            $result['description'],
            $result['position'],
            $result['status'],
            $copyright,
            $result['creation_time'],
            $result['modification_time'],
            isset($result['comment']) ? $result['comment'] : null
        );
    }

    /**
     * Костыльный метод для локального создания существующего фото.
     * @param $id
     * @param $position
     * @param $status
     * @param string $description
     * @return static
     */
    public static function createFromLocalValues($id, $position, $status, $description = '') {
        return new static(
            $id,
            null,
            null,
            $description,
            $position,
            $status,
            null,
            null,
            null,
            null
        );
    }

    /**
     * @param int $id
     * @param string $url
     * @param string $preview
     * @param int $position
     * @param string $description
     * @param string $status
     * @param AbstractCopyright $copyright
     * @param int $createdAt
     * @param int $modificatedAt
     * @param string $comment
     */
    public function __construct(
        $id,
        $url,
        $preview,
        $description = null,
        $position = null,
        $status = null,
        AbstractCopyright $copyright = null,
        $createdAt = null,
        $modificatedAt = null,
        $comment = null
    )
    {
        $this->_id = $id;
        $this->_url = $url;
        $this->_previews = $preview;
        $this->_description = $description;
        $this->_position = $position;
        $this->_status = $status;
        $this->_copyright = $copyright;
        $this->_createdAt = $createdAt;
        $this->_modificatedAt = $modificatedAt;
        $this->_comment = $comment;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function getPreviews()
    {
        return $this->_previews;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function getPosition()
    {
        return $this->_position;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function getCopyright()
    {
        return $this->_copyright;
    }

    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    public function getModificatedAt()
    {
        return $this->_modificatedAt;
    }

    public function getComment()
    {
        return $this->_comment;
    }

    public function setId($id)
    {
        $this->_id = $id;
        $this->wasChanged();
    }

    public function setPosition($position, $changed = true)
    {
        $this->_position = $position;
        if ($changed) {
            $this->wasChanged('position');
        }
    }

    public function setStatus($status)
    {
        $this->_status = $status;
        $this->wasChanged('status');
    }

    public function setDescription($description)
    {
        $this->_description= $description;
        $this->wasChanged('description');
    }

    public function prepareForAssertion()
    {
        $this->_modificatedAt = false;
        $this->flushChanged();
    }

    public function getIsMain()
    {
        return $this->_isMain;
    }

    public function setIsMain()
    {
        $this->_isMain = true;
        $this->wasChanged('is_main');
    }
}
