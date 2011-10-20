<?php

class features_module extends CommonModule {

	function setCollectionClass() {
		$this->Collection = Features::getInstance();
	}

	function _process($action, $mode) {
		switch ($action) {
			case 'list':
				switch ($mode) {
					default:
						$this->getFeaturesList();
						break;
				}
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function getFeaturesList() {
		$where = '';
		$this->_list($where);
		$this->data['features']['title'] = 'Тесты';
		$this->data['features']['count'] = $this->getCountBySQL($where);
	}

}