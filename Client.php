<?php
	namespace DG\API\Photo;

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

		protected $_apiKey;
		protected $_format;
		protected $_locale;

		public function __construct($apiKey, $format = self::FORMAT_JSON, $locale = self::LOCALE_RU_RU)
		{
			$this->_apiKey = $apiKey;
			$this->_format = $format;
			$this->_locale = $locale;
		}

		protected function getCurlExecString($methodName, array $params = [], $httpMethod = self::HTTP_POST)
		{
			$fx = function($fx, $prefix, $ar) {
				$res = [];

				foreach($ar as $k => $v)
				{
					if(is_array($v))
					{
						$r = $fx($fx, ($prefix ? $prefix.'['.$k.']' : $k), $v);
						foreach($r as $vx)
						{
							$res[] = $vx;
						}
					}else
					{
						$res[] = '--form '.($prefix ? $prefix.'['.$k.']' : $k).'='."'".str_replace(["'", '\\'], ["\\'", '\\\\'], $v)."'";
					}
				}

                return $res;
			};

			$args = $fx($fx, '', $params);

			$cmd = '/usr/bin/curl -s -X '.$httpMethod.' \''.self::API_URL.$methodName.'\' '.implode(' ', $args);

            return $cmd;
		}

		protected function callMethod($methodName, array $params = [], $httpMethod = self::HTTP_POST)
		{
            $httpCode = 200;

            try
            {
                /**
                 * @DESC php curl cant work with multi-array arguments normally with file attache
                 */
                $cmd = $this->getCurlExecString($methodName, $params, $httpMethod);

                exec($cmd, $res, $returnCode);

                if($returnCode != 0)
                {
                    $httpCode = 500;
                }

                $res = implode('', $res);
            } catch (\Exception $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            var_dump($res);

            if($httpCode != 200)
            {
                /**
                 * @TODO error
                 */
                throw new Exception('Result code is not 200');
            }

            return $res;
		}

		protected function parseResponse($response, $format = self::FORMAT_JSON)
		{
            $resJson = json_decode($response, true);

            if(!$resJson)
            {
                throw new Exception('Result is not JSON');
            }

			return $resJson;
		}

		protected function makeRequest($methodName, array $params, $httpMethod)
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

		public function add(PhotoCollection &$collection, $objectType, $objectId, $albumCode, $userId = null)
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

            if(!$res)
            {
                throw new Exception('No result');
            }

            if(!isset($res['meta']['code']) || $res['meta']['code'] != 200 )
            {
                /**
                 * @TODO error
                 */
                throw new Exception('Result code is not 200');
            }

            if(!isset($res['result']))
            {
                throw new Exception('No result');
            }

            $result = $res['result'];

            $fields = ['total', 'album_code', 'album_name', 'items'];

            $resData = [];

            foreach($fields as $field)
            {
                if(!isset($result[$field]))
                {
                    throw new Exception('No field "'.$field.'" in result');
                }
                $resData[$field] = $result[$field];
            }

            if(!is_array($resData['items']))
            {
                throw new Exception('Items are not array');
            }

            foreach($resData['items'] as $resItem)
            {
                if(isset($resItem['error']))
                {
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

                if(!$r)
                {
                    /**
                     * @TODO error
                     */
                    throw new Exception('Item set data error');
                }
            }

            return true;
		}

        public function upload(PhotoCollection &$collection)
        {
            $items = $collection->getItems();

            $params = $this->extendParams([
                'items' => array_values( array_map(function($item){
                    return [
                        'file' => '@'.$item->getFilePath(),
                        'id' => $item->getId(),
                        'hash' => $item->getHash(),
                    ];
                }, $collection->getItems()) ),
            ]);

            $res = $this->makeRequest('upload', $params, self::HTTP_POST);

            var_dump('!!!', $res);
        }
	}