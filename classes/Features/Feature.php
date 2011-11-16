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
		// bundle exec cuke4php features/authorization/sign_in.feature -r features
		$command = 'cd ../ && bundle exec cuke4php -f progress features/' . $this->getFilePath() . ' -r features';
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
			if (strstr($line, 'You can implement')) {
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
			
			if (strstr($line, ' undefined)')) {
				$code = self::STATUS_NO_FILE;
				$recording = true;
			}

			if (strstr($line, 'scenario')) {
				$recording = false;
			}
			if (strstr($line, 'scenarios (')) {
				$passed = true;
			}
			if (strstr($line, 'scenario (')) {
				$passed = true;
			}
		}

		if (!$passed) {
			$this->setStatus(self::STATUS_PAUSED, 'no scenarios in file ' . $f . "\n" . implode("\n", $output));
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
		$t = $this->getDescription();
		$t = explode("\n", $t);
		$tt = '';
		$ttt = '';
		foreach ($t as $tt) {
			if ($ttt)
				continue;
			if (isset($tt[0]) && $tt[0] != '#' && $tt[0] != '@') {
				$ttt = $tt;
			}
		}
		$pattern = '/(Feature\:|Функция\:|Функционал\:|Свойство\:)(.+)$/isU';
		preg_match_all($pattern, $ttt, $matchesarray);
//		die(print_r($matchesarray));
		return isset($matchesarray[2][0]) && $matchesarray[2][0] ? $matchesarray[2][0] : $this->data['title'];
	}

	function getDescription() {
		$this->load();
		if (isset($this->descr))
			return$this->descr;
		$f = '../features/' . $this->getFilePath();
		if (file_exists($f)) {
			$this->descr = file_get_contents($f);
			return $this->descr;
		}
		if ($this->data['description']) {
			@mkdir('../features/' . $this->getFolder());
			file_put_contents($f, $this->data['description']);
			clearstatcache();
			$this->descr = $this->data['description'];
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
