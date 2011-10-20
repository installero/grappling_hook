<?php

class Feature extends BaseObjectClass {

	public $id;
	public $loaded = false;
	public $data;

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

	function getListData() {
		$out = array(
		    'id' => $this->id,
		    'title' => $this->getTitle(),
		    'status' => $this->getStatus(),
		    'group_id' => $this->getGroupId(),
		    'path' => $this->getPath(),
		    'last_run' => $this->getLastRun(),
		    'last_message' => $this->getLastMessage(),
		);
		return $out;
	}

	function getTitle() {
		$this->load();
		return $this->data['title'];
	}

	function getStatus() {
		$this->load();
		return $this->data['status'];
	}

	function getGroupId() {
		$this->load();
		return $this->data['group_id'];
	}

	function getPath() {
		$this->load();
		return $this->data['path'];
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