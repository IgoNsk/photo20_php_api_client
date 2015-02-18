<?php
namespace DG\API\Photo;

use \DG\API\Photo\Item\RemotePhotoItem;
use \DG\API\Photo\Collection\LocalPhotoCollection;
use \DG\API\Photo\Collection\PhotoAlbumCollection;

class Client extends AbstractClient
{
    const API_VERSION = '2.0';
    const API_URL = 'http://slave.app.photo.local/2.0/';

    const OBJECT_TYPE_BRANCH = 'branch';
    const OBJECT_TYPE_GEO = 'geo';

    const ALBUM_CODE_COMMON = 'common';
    const ALBUM_CODE_VIEW = 'view';
    const ALBUM_CODE_FACILITIES = 'facilities';
    const ALBUM_CODE_DEFAULT = self::ALBUM_CODE_VIEW;

    /**
     * @param $res
     * @return $this
     * @throws Exception
     */
    protected function checkApiResponse($res)
    {
        if (!$res) {
            throw new Exception('No result');
        }

        if (!isset($res['meta']['code'])) {
            throw new Exception('Result code is undefined');
        }

        if ($res['meta']['code'] != 200 ) {
            throw new Exception($res['error']['message']);
        }

        if (!isset($res['result'])) {
            throw new Exception('No result');
        }

        return $this;
    }

    /**
     * @param $objectId
     * @param $objectType
     * @param $albumCode
     * @param null $previewSize
     * @param null $status
     * @throws \DG\API\Photo\Exception
     * @return PhotoAlbumCollection[]
     */
    public function get($objectId, $objectType, $albumCode, $previewSize = null, $status = null)
    {
        $params = $this->extendParams([
            'object_id' => $objectId,
            'object_type' => $objectType,
            'album_code' => $albumCode,
            'preview_size' => $previewSize,
            'status' => $status,
        ]);

        $res = $this->makeRequest('photo/get', $params, self::HTTP_GET);

        $this->checkApiResponse($res);

        $result = [];
        foreach ($res['result'] as $resultSet) {
            if (!$resultSet['total']) {
                continue;
            }

            $result[] = $collection = new PhotoAlbumCollection($resultSet['album_code'], $resultSet['album_name']);
            foreach ($resultSet['items'] as $item) {
                $itemObj = RemotePhotoItem::createFromAPIResult($item);
                $collection->add($itemObj);
            }
        }

        return $result;
    }

    public function delete(PhotoAlbumCollection $collection, $objectType, $objectId)
    {

        $items = $collection->getItems();

        $requestItems = [];
        foreach ($items as $item) {
            /** @var $item \DG\API\Photo\Item\RemotePhotoItem */
            if ($item->isDeleted()) {
                $requestItems[] = $item->getId();
            }
        }
        $params = $this->extendParams([
            'object_type' => $objectType,
            'object_id' => $objectId,
            'id' => $requestItems,
        ]);

        $res = $this->makeRequest('photo/delete', $params, self::HTTP_POST);
        $this->checkApiResponse($res);

        return $res;
    }

    public function add(LocalPhotoCollection &$collection, $objectType, $objectId, $albumCode, $userId = null)
    {
        $items = $collection->getItems();

        $requestItems = [];
        foreach ($items as $item) {
            /**
             * @var $item \DG\API\Photo\Item\LocalPhotoItem
             */
            if ($item->getError()) {
                continue;
            }

            $requestItems[] = [
                'uid' => $item->getUID(),
                'options' => $item->getOptions(),
            ];
        }
        $params = $this->extendParams([
            'object_type' => $objectType,
            'object_id' => $objectId,
            'album_code' => $albumCode,
            'user_id' => $userId,
            'items' => $requestItems,
        ]);

        $res = $this->makeRequest('photo/add', $params, self::HTTP_POST);

        if (!$res) {
            throw new Exception('No result');
        }

        if (!isset($res['meta']['code']) || $res['meta']['code'] != 200 ) {
            /**
             * @TODO error
             */
            throw new Exception('Result code is not 200');
        }

        if (!isset($res['result'])) {
            throw new Exception('No result');
        }

        $result = $res['result'];
        $fields = ['total', 'album_code', 'album_name', 'items'];
        $resData = [];

        foreach ($fields as $field) {
            if (!isset($result[$field])) {
                throw new Exception('No field "'.$field.'" in result');
            }
            $resData[$field] = $result[$field];
        }

        if (!is_array($resData['items'])) {
            throw new Exception('Items are not array');
        }

        $collection->setOptions([
            'album_code' => $resData['album_code'],
            'album_name' => $resData['album_name'],
        ]);

        $itemsByIndex = array_values($items);
        foreach ($resData['items'] as $index=>$resItem) {
            if (isset($resItem['error'])) {
                $localItem = $itemsByIndex[$index];
                $localItem->setError($resItem['error']['type'], $resItem['error']['message']);
                continue;
            }

            $data = $resItem;
            $uid = isset($resItem['uid']) ? $resItem['uid'] : '';
            $id = isset($resItem['id']) ? $resItem['id'] : '';
            $hash = isset($resItem['hash']) ? $resItem['hash'] : '';

            $r = $collection->setItemDataByUID($uid, $id, $hash, $data);

            if (!$r) {
                /**
                 * @TODO error
                 */
                throw new Exception('Item set data error');
            }
        }

        return true;
    }

    /**
     * @param LocalPhotoCollection $collection
     * @return PhotoAlbumCollection
     * @throws Exception
     */
    public function upload(LocalPhotoCollection $collection)
    {
        $items = $collection->getItems();
        $requestItems = [];
        foreach ($items as $item) {
            /**
             * @var $item \DG\API\Photo\Item\LocalPhotoItem
             */
            if ($item->getError()) {
                continue;
            }

            $requestItems[] = [
                'file' => '@'.$item->getFilePath(),
                'id' => $item->getId(),
                'hash' => $item->getHash(),
            ];
        }
        $params = $this->extendParams([
            'items' => $requestItems,
        ]);

        $res = $this->makeRequest('photo/upload', $params, self::HTTP_POST);

        $this->checkApiResponse($res);
        $albumData = $collection->getOptions();

        $resCollection = new PhotoAlbumCollection($albumData['album_code'], $albumData['album_name']);

        $itemsByIndex = array_values($items);
        foreach ($res['result']['items'] as $index=>$resultSet) {
            $localItem = $itemsByIndex[$index];

            if (!empty($resultSet['error'])) {
                $localItem->setError($resultSet['error']['type'], $resultSet['error']['message']);
                continue;
            }

            /**
             * @todo перенести из старой коллекции Description
             */
            $newItem = new RemotePhotoItem(
                $resultSet['id'],
                $resultSet['url'],
                $resultSet['preview_urls'],
                null,
                $resultSet['position']
            );

            $localItem->setRemoteItem($newItem);
            $resCollection->add($newItem);
        }

        return $resCollection;
    }

}