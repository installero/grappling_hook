<?php

class Features extends Collection {

	public $className = 'Feature';
	public $tableName = 'features';
	public $itemName = 'feature';
	public $itemsName = 'features';

	public static function getInstance() {
		if (!self::$features_instance) {
			self::$features_instance = new Features();
		}
		return self::$features_instance;
	}

	public function create($data) {
		$q = array();
		foreach ($data as $field => $value) {
			$q[] = $field . '=' . Database::escape($value);
		}
		return Database::query('INSERT INTO `features` SET ' . implode(',', $q));
	}

}