<?php
	namespace DG\API\Photo;

	class PhotoCollection
	{
		protected $_items = [];

		public function __construct()
		{

		}

		public function add(\DG\API\Photo\PhotoItem $item)
		{
			$this->_items[$item->getUID()] = $item;

            return $this;
		}

		public function getItems()
		{
			return $this->_items;
		}

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