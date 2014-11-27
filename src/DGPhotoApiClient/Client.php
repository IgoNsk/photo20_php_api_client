<?php
	namespace DG\API\Photo;

    use \DG\API\Photo\Item\RemotePhotoItem;
    use \DG\API\Photo\Collection\LocalPhotoCollection;
    use \DG\API\Photo\Collection\PhotoAlbumCollection;

	class Client extends AbstractClient
	{
		const API_VERSION = '2.0';
		const API_URL = 'http://photo.local/2.0/';

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
                throw new Exception($res['meta']['error']['message']);
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
                $result[] = $collection = new PhotoAlbumCollection($resultSet['album_code'], $resultSet['album_name']);
                foreach ($resultSet['items'] as $item) {
                    $itemObj = RemotePhotoItem::createFromAPIResult($item);
                    $collection->add($itemObj);
                }
            }

            return $result;
        }

		public function add(LocalPhotoCollection &$collection, $objectType, $objectId, $albumCode, $userId = null)
		{
			$params = $this->extendParams([
				'object_type' => $objectType,
				'object_id' => $objectId,
				'album_code' => $albumCode,
				'user_id' => $userId,
				'items' => array_values( array_map(function($item){
                    /**
                     * @var $item \DG\API\Photo\Item\LocalPhotoItem
                     */
					return [
						'uid' => $item->getUID(),
						'options' => $item->getOptions(),
					];
				}, $collection->getItems()) ),
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

            foreach ($resData['items'] as $resItem) {
                if (isset($resItem['error'])) {
                    /**
                     * @TODO error
                     */
                    throw new Exception('Item error');
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

            $params = $this->extendParams([
                'items' => array_values( array_map(function($item){
                    /**
                     * @var $item \DG\API\Photo\Item\LocalPhotoItem
                     */
                    return [
                        'file' => '@'.$item->getFilePath(),
                        'id' => $item->getId(),
                        'hash' => $item->getHash(),
                    ];
                }, $items) ),
            ]);

            $itemsById = array();
            foreach ($items as $item) {
                $itemsById[$item->getId()] = $item;
            }

            $res = $this->makeRequest('photo/upload', $params, self::HTTP_POST);

            $this->checkApiResponse($res);
            $albumData = $collection->getOptions();

            $resCollection = new PhotoAlbumCollection($albumData['album_code'], $albumData['album_name']);
            foreach ($res['result']['items'] as $resultSet) {
                $localItem = $itemsById[$resultSet['id']];

                if ($resultSet['code'] != '200') {
                    $localItem->setError($resultSet['error']['type'], $resultSet['error']['message']);
                    continue;
                }

                /**
                 * @todo перенести из старой коллекции Description
                 */
                $newItem = new RemotePhotoItem(
                    $resultSet['id'],
                    $resultSet['url'],
                    $resultSet['preview_url'],
                    null,
                    $resultSet['position']
                );

                $localItem->setRemoteItem($newItem);
                $resCollection->add($newItem);
            }

            return $resCollection;
        }
	}