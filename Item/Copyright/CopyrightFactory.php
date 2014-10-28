<?php

namespace DG\API\Photo\Item\Copyright;

use \DG\API\Photo\Exception as DGAPIPhotoException;

class CopyrightFactory
{
    const TYPE_IMAGE = 'image';
    const TYPE_TEXT  = 'text';

    /**
     * @param $type string
     * @param $value string
     * @param null $url string
     * @throws \DG\API\Photo\Exception
     * @return AbstractCopyright
     */
    public static function create($type, $value, $url = null)
    {
        switch ($type) {
            case self::TYPE_IMAGE:
                return new ImageCopyright($value, $url);
                break;

            case self::TYPE_TEXT:
                return new TextCopyright($value, $url);
                break;

            default:
                throw new DGAPIPhotoException('Unknown type of copyright provider - "'.$type.'"');
        }
    }
} 