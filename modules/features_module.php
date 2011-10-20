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
		$data = $this->_list($where, false, 1);
		$this->data['feature_groups'] = array();
		$this->data['feature_groups'] = $this->getInGroup($data);

		$this->data['features']['title'] = 'Тесты';
		$this->data['features']['count'] = $this->getCountBySQL($where);
	}

	function getInGroup($data) {
		$groups = array();
		foreach ($data['features'] as $item) {
			$groups[$item['group_id']] = $item['group_id'];
		}
		$query = 'SELECT * FROM `feature_groups` WHERE `id` IN(' . implode(',', $groups) . ')';
		$groups = Database::sql2array($query, 'id');
		$groups[0] = array('title' => 'без группы');

		foreach ($data['features'] as $feature) {
			$groups[$feature['group_id']]['features'][] = $feature;
		}

		return $groups;
	}

}