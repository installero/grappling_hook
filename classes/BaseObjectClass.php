<?php

class BaseObjectClass {

	public $exists = false;
	public $fieldsMap = array();

	function _show() {
		throw new Exception('BaseObjectClass::_show must be implemeted');
	}

	function _create($data, $tableName) {
		$q = array();
		foreach ($data as $field => $value) {
			if (isset($this->fieldsMap[$field])) {
				$q[] = '`' . $field . '`=' . Database::escape($value);
			}else
				throw new Exception('_create failed: illegal field #' . $field);
		}
		if (count($q)) {
			Database::query('INSERT INTO `' . $tableName . '` SET ' . implode(',', $q));
			return $lid = Database::lastInsertId();
		}
	}

}