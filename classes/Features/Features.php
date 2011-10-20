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

}