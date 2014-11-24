<?php
	namespace DG\API\Photo\Collection;

    use \DG\API\Photo\Item\LocalPhotoItem as PhotoItem;

	class LocalPhotoCollection
	{
		protected $_items = [];
        private $options = [];

		public function __construct()
		{

		}

        /**
         * @param array $options
         * @return $this
         */
        public function setOptions(array $options)
        {
            $this->options = $options;

            return $this;
        }

        /**
         * @return array
         */
        public function getOptions()
        {
            return $this->options;
        }

        /**
         * @param PhotoItem $item
         * @return $this
         */
		public function add(PhotoItem $item)
		{
			$this->_items[$item->getUID()] = $item;

            return $this;
		}

        /**
         * @return PhotoItem[]
         */
		public function getItems()
		{
			return $this->_items;
		}

        /**
         * @param $uid
         * @param $id
         * @param $hash
         * @param $data
         * @return bool
         */
        public function setItemDataByUID($uid, $id, $hash, $data)
        {
            if(!isset($this->_items[$uid]))
            {
                return false;
            }

            $this->_items[$uid]
                ->setId($id)
                ->setHash($hash)
                ->setData($data)
            ;

            return true;
        }
	}