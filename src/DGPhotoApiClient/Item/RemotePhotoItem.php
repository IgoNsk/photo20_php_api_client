<?php

namespace DG\API\Photo\Item;

use \DG\API\Photo\Item\Copyright\AbstractCopyright;
use \DG\API\Photo\Item\Copyright\CopyrightFactory;

class RemotePhotoItem
{
    const STATUS_BLOCKED  = 'blocked';
    const STATUS_HIDDEN   = 'hidden';
    const STATUS_ACTIVE   = 'active';

    /**
     * @var int
     */
    private $_id;

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

    public function getId()
    {
        return $this->_id;
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
} 