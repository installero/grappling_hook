<?php

class Feature extends BaseObjectClass {
	const STATUS_OK = 1;
	const STATUS_FAILED = 2;
	const STATUS_NO_FILE = 3;
	const STATUS_PAUSED = 4;
	const STATUS_WAIT_FOR_RUN = 5;
	const STATUS_NEW = 0;

	//
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
	    'last_message' => 'html',
	    'db_modify' => 'int',
	    'file_modify' => 'int',
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

	function dropCache() {
		Features::getInstance()->dropCache($this->id);
		$this->loaded = false;
	}

	function _create($data) {
		$tableName = Features::getInstance()->tableName;
		$this->dropCache();
		return parent::_create($data, $tableName);
	}

	function _update($data) {
		$tableName = Features::getInstance()->tableName;
		$this->dropCache();
		return parent::_update($data, $tableName);
	}

	function setStatus($status_code, $message) {
		$message = $message ? $message : 'empty message';
		$query = 'UPDATE `features` SET
			`status`=' . (int) $status_code . ',
			`last_run`=' . time() . ',
			`last_message`=' . Database::escape($message) . '
				WHERE
			`id`=' . $this->id;
		$this->data['status'] = $status_code;
		Database::query($query);
	}

	function getFileModifyTime() {
		$this->load();
		return (int) $this->data['file_modify'];
	}

	function getDbModifyTime() {
		$this->load();
		return (int) $this->data['db_modify'];
	}

	function _run() {
		$this->load();
		$command = 'cd ../ && bundle exec cucumber -f progress -r features features/' . $this->getFilePath();
		$f = '../features/' . $this->getFilePath();
		if (!file_exists($f)) {
			$this->setStatus(self::STATUS_PAUSED, 'no file ' . $f);
			return array(false, array('no file ' . $f));
		}

		$file_modify = filemtime($f);
		if ($file_modify > $this->getFileModifyTime()) {
			// file is newer tham db thinks
			$query = 'UPDATE `features` SET `file_modify` = ' . $file_modify . ' WHERE `id`=' . $this->id;
			Database::query($query);
		}

		exec($command, $output, $return_var);
		file_put_contents('log/cucumber.log', implode("\n", $output));
		$recording = false;
		$error_message = '';
		$code = self::STATUS_OK;
		$passed = false;
		foreach ($output as $line) {
			if ($recording)
				$error_message.=$line . "\n";


			if (strstr($line, 'Failing Scenarios:')) {
				$recording = true;
				$code = self::STATUS_FAILED;
			}

			if (strstr($line, '(::) failed steps (::)')) {
				$recording = true;
				$code = self::STATUS_FAILED;
			}
			if (strstr($line, 'No steps')) {
				$code = self::STATUS_NO_FILE;
				$recording = true;
			}

			if (strstr($line, 'scenario')) {
				$recording = false;
			}
			if (strstr($line, 'scenarios (')) {
				$passed = true;
			}
		}

		if (!$passed) {
			$this->setStatus(self::STATUS_PAUSED, 'no scenarios in file ' . $f);
			return array(false, array('no scenarios in file ' . $f));
		}

		if ($code !== self::STATUS_OK) {
			$this->setStatus($code, $error_message);
		} else {
			$om = implode("\n", $output);
			$om = $om ? $om : 'empty output';
			$this->setStatus($code, $om);
		}
		$this->dropCache();
		return array($code == self::STATUS_OK, $output);
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
		    'status_description' => $this->getStatusDescription(),
		    'group_id' => $this->getGroupId(),
		    'filepath' => $this->getFileName(),
		    'last_run' => ($last_run = $this->getLastRun()) ? date('Y/m/d H:i:s', $last_run) : 0,
		    'last_message' => $this->getLastMessage(),
		    'path' => $this->getUrl(),
		    'file_modify' => $this->getFileModifyTime(),
		);
		return $out;
	}

	function getTitle() {
		$this->load();
		return $this->data['title'];
	}

	function getDescription() {
		$this->load();
		$f = '../features/' . $this->getFilePath();
		if (file_exists($f))
			return file_get_contents($f);
		if ($this->data['description']) {
			@mkdir('../features/' . $this->getFolder());
			file_put_contents($f, $this->data['description']);
			clearstatcache();
			return $this->data['description'];
		}
	}

	function getStatus() {
		$this->load();
		return $this->data['status'];
	}

	function getStatusDescription() {
		$this->load();
		$status = $this->data['status'];
		switch ($status) {
			case self::STATUS_NEW:
				return 'new';
				break;
			case self::STATUS_OK:
				return 'ok';
				break;
			case self::STATUS_FAILED:
				return 'failed';
				break;
			case self::STATUS_PAUSED:case self::STATUS_NO_FILE:
				return 'paused';
				break;
			case self::STATUS_WAIT_FOR_RUN:
				return 'waiting';
				break;
		}
		return 'unknown';
	}

	function getGroupId() {
		$this->load();
		return $this->data['group_id'];
	}

	function getFolder() {
		$query = 'SELECT `folder` FROM `feature_groups` WHERE `id`=' . $this->getGroupId();
		return Database::sql2single($query);
	}

	function getFilePath() {
		$this->load();
		return $this->getFolder() . '/' . $this->data['filepath'] . '.feature';
	}

	function getFileName() {
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
