<?php

namespace DG\API\Photo\Item;

use \DG\API\Photo\Item\Copyright\AbstractCopyright;
use \DG\API\Photo\Item\Copyright\CopyrightFactory;

class RemotePhotoItem
{
    const STATUS_BLOCKED  = 'blocked';
    const STATUS_EDITABLE = 'editable';
    const STATUS_ACTIVE   = 'active';
    const STATUS_ALL      = 'all';

    /**
     * @var int
     */
    private $_id;

    /**
     * @var string
     */
    private $_url;

    /**
     * @var string
     */
    private $_preview;

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
        $copyright = CopyrightFactory::create($copyrightData['type'], $copyrightData['value'], $copyrightData['url']);
        return new RemotePhotoItem(
            $result['id'],
            $result['url'],
            $result['preview_url'],
            $result['description'],
            $result['status'],
            $result['position'],
            $copyright,
            $result['modification_time'],
            $result['creation_time'],
            $result['comment']
        );
    }

    public function __construct(
        $id,
        $url,
        $preview,
        $position,
        $status,
        AbstractCopyright $copyright,
        $createdAt,
        $modificatedAt,
        $comment
    )
    {
        $this->_id = $id;
        $this->_url = $url;
        $this->_preview = $preview;
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

    public function getPreview()
    {
        return $this->_preview;
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