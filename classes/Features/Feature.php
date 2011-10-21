<?php

class Feature extends BaseObjectClass {

	public $id;
	public $loaded = false;
	public $data;
	public $fieldsMap = array(
	    'group_id' => 'int',
	    'title' => 'string',
	    'description' => 'html',
	    'filepath' => 'string',
	    'last_run' => 'int',
	    'status' => 'int',
	    'last_message' => 'html'
	);

	function __construct($id, $data = false) {
		$this->id = $id;
		if ($data) {
			if ($data == 'empty') {
				$this->loaded = true;
				$this->exists = false;
			}
			$this->load($data);
		}
	}

	function _create($data) {
		$tableName = Features::getInstance()->tableName;
		return parent::_create($data, $tableName);
	}

	function _run() {
		$this->load();

		$command = '/usr/local/bin/behat -f progress -c '.Config::need('features_path').'behat.yml ' . Config::need('features_path') . $this->getFilePath();
		exec($command, $output, $return_var);
		if($return_var !== 1)
			throw new Exception ('test failed');
		return $output;
	}

	function load($data = false) {
		if ($this->loaded)
			return false;
		if (!$data) {
			$query = 'SELECT * FROM `features` WHERE `id`=' . $this->id;
			$this->data = Database::sql2row($query);
		}else
			$this->data = $data;
		$this->exists = true;
		$this->loaded = true;
	}

	function _show() {
		return $this->getListData();
	}

	function getUrl($redirect = false) {
		$id = $redirect ? $this->getDuplicateId() : $this->id;
		return Config::need('www_path') . '/features/' . $id;
	}

	function getListData() {
		$out = array(
		    'id' => $this->id,
		    'title' => $this->getTitle(),
		    'description' => $this->getDescription(),
		    'status' => $this->getStatus(),
		    'group_id' => $this->getGroupId(),
		    'filepath' => $this->getFilePath(),
		    'last_run' => $this->getLastRun(),
		    'last_message' => $this->getLastMessage(),
		    'path' => $this->getUrl(),
		);
		return $out;
	}

	function getTitle() {
		$this->load();
		return $this->data['title'];
	}

	function getDescription() {
		$this->load();
		return $this->data['description'];
	}

	function getStatus() {
		$this->load();
		return $this->data['status'];
	}

	function getGroupId() {
		$this->load();
		return $this->data['group_id'];
	}

	function getFilePath() {
		$this->load();
		return $this->data['filepath'];
	}

	function getLastRun() {
		$this->load();
		return $this->data['last_run'];
	}

	function getLastMessage() {
		$this->load();
		return $this->data['last_message'];
	}

}