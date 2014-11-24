<?php
	namespace DG\API\Photo;

    use \DG\API\Photo\Item\RemotePhotoItem;
    use \DG\API\Photo\Collection\LocalPhotoCollection;
    use \DG\API\Photo\Collection\PhotoAlbumCollection;
    use \DG\API\Photo\Transport\TransportInterface;

	class Client
	{
		const API_VERSION = '2.0';
		const API_URL = 'http://photo.local/2.0/';

		const HTTP_POST = 'POST';
		const HTTP_GET = 'GET';

		const FORMAT_JSON = 'json';
		const FORMAT_JSONP = 'jsonp';

		const LOCALE_RU_RU = 'ru_RU';
		const LOCALE_EN_GB = 'en_GB';
		const LOCALE_EN_US = 'en_US';

		const OBJECT_TYPE_BRANCH = 'branch';
		const OBJECT_TYPE_GEO = 'geo';

		const ALBUM_CODE_COMMON = 'common';
		const ALBUM_CODE_VIEW = 'view';
		const ALBUM_CODE_FACILITIES = 'facilities';
        const ALBUM_CODE_DEFAULT = self::ALBUM_CODE_VIEW;

		protected $_apiKey;
		protected $_format;
		protected $_locale;
        protected $_onResult;

        /**
         * @var TransportInterface
         */
        protected $transport;

		public function __construct($apiKey, $format = self::FORMAT_JSON, $locale = self::LOCALE_RU_RU)
		{
			$this->_apiKey = $apiKey;
			$this->_format = $format;
			$this->_locale = $locale;
		}

        public function setTransport(TransportInterface $transport)
        {
            $this->transport = $transport;
        }

        protected function onResult($jsonResult, $methodName, $params, $httpMethod)
        {
            if($this->_onResult && is_callable($this->_onResult))
            {
                return call_user_func_array($this->_onResult, func_get_args());
            }

            return false;
        }

		protected function callMethod($methodName, array $params = [], $httpMethod = self::HTTP_POST)
		{
            $httpCode = 200;

            try
            {
                $result = $this->transport->makeRequest($methodName, $params, $httpMethod);
                if (!$result) {
                    $httpCode = 500;
                }
            } catch (\Exception $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            if ($httpCode != 200) {
                /**
                 * @TODO error
                 */
                throw new Exception('Result code is not 200');
            }

            $res = $result->result;
            $this->onResult($res, $methodName, $params, $httpMethod);

            return $res;
		}

		protected function parseResponse($response, $format = self::FORMAT_JSON)
		{
            $resJson = json_decode($response, true);

            if (!$resJson) {
                throw new Exception('Result is not JSON');
            }

			return $resJson;
		}

		public function makeRequest($methodName, array $params, $httpMethod)
		{
            $res = $this->callMethod($methodName, $params, $httpMethod);

            $resJson = $this->parseResponse($res, $this->_format);

            return $resJson;
		}

		protected function extendParams(array $params = [], array $fields = ['key', 'format', 'locale'])
		{
			$systemFields = [
				'key' => $this->_apiKey,
				'format' => $this->_format,
				'locale' => $this->_locale,
			];

			foreach($fields as $fieldKey)
			{
				$params[$fieldKey] = $systemFields[$fieldKey];
			}

			return $params;
		}

        public function setOnResult($onResult)
        {
            $this->_onResult = $onResult;

            return $this;
        }

        public function getOnResult()
        {
            return $this->_onResult;
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

            $res = $this->makeRequest('get', $params, self::HTTP_GET);

            if (!$res) {
                throw new Exception('No result');
            }

            if (!isset($res['meta']['code'])) {
                throw new Exception('Result code is undefined');
            }

            if ($res['meta']['code'] != 200 ) {
                throw new Exception($res['meta']['message']);
            }

            if (!isset($res['result'])) {
                throw new Exception('No result');
            }

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
					return [
						'uid' => $item->getUID(),
						'options' => $item->getOptions(),
					];
				}, $collection->getItems()) ),
			]);

			$res = $this->makeRequest('add', $params, self::HTTP_POST);

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

        public function upload(LocalPhotoCollection &$collection)
        {
            $items = $collection->getItems();

            $params = $this->extendParams([
                'items' => array_values( array_map(function($item){
                    return [
                        'file' => '@'.$item->getFilePath(),
                        'id' => $item->getId(),
                        'hash' => $item->getHash(),
                    ];
                }, $items) ),
            ]);

            $res = $this->makeRequest('upload', $params, self::HTTP_POST);

            return true;
        }
	}