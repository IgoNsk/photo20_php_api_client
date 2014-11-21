<?php
	namespace DG\API\Photo\Item;

	class LocalPhotoItem
	{
        protected $_id;
        protected $_hash;
		protected $_uid;
		protected $_filePath;
		protected $_options = [];
		protected $_data = [];

		public function __construct($uid, $filePath, array $options = [])
		{
			$this->_uid = $uid;
			$this->_filePath = $filePath;
			$this->_options = $options;
		}

		public function getUID()
		{
			return $this->_uid;
		}

		public function getOptions()
		{
			return $this->_options;
		}

        public function setId($id)
        {
            $this->_id = $id;

            return $this;
        }

        public function setHash($hash)
        {
            $this->_hash = $hash;

            return $this;
        }

        public function setData($data)
        {
            $this->_data = $data;

            return $this;
        }

        public function getId()
        {
            return $this->_id;
        }

        public function getHash()
        {
            return $this->_hash;
        }

        public function getFilePath()
        {
            return $this->_filePath;
        }
	}