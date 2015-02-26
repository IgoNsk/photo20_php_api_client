<?php

namespace DG\API\Photo\Item\Copyright;

use \DG\API\Photo\Exception as DGAPIPhotoException;

class CopyrightFactory
{
    const TYPE_IMAGE = 'image';
    const TYPE_TEXT  = 'text';

    /**
     * @param string $type
     * @param string $code
     * @param string $value
     * @param string null $url
     * @throws \DG\API\Photo\Exception
     * @return AbstractCopyright
     */
    public static function create($type, $code, $value, $url = null)
    {
        $type = trim($type);
        $type = strtolower($type);
        switch ($type) {
            case self::TYPE_IMAGE:
                return new ImageCopyright($code, $value, $url);
                break;

            case self::TYPE_TEXT:
                return new TextCopyright($code, $value, $url);
                break;

            default:
                throw new DGAPIPhotoException('Unknown type of copyright provider - "'.$type.'"');
        }
    }
} 