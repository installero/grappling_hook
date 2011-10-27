<?php

class Jfeatures_module extends JBaseModule {

	function process() {
		global $current_user;
		$current_user = new CurrentUser();
		switch ($_POST['action']) {
			case 'run':
				$this->runTest();
				break;
			case 'check':
				$this->checkTest();
				break;
		}
	}

	function error($s = 'ошибка') {
		$this->data['success'] = 0;
		$this->data['error'] = $s;
		return;
	}

	function runTest() {
		global $current_user;
		$this->data['success'] = 0;
		if (!$current_user->authorized) {
			$this->error('Auth');
			return;
		}

		if ($current_user->getRole() < User::ROLE_SITE_ADMIN) {
			$this->error('Must be admin');
			return;
		}

		$id = isset($_POST['id']) ? (int) $_POST['id'] : false;
		if (!$id) {
			$this->error('Illegal id');
			return;
		}

		$feature = Features::getInstance()->getByIdLoaded($id);
		/* @var $feature Feature */
		try {
			list($success, $description) = $feature->_run();
		} catch (Exception $e) {
			$this->error($e->getMessage());
		}

		$this->data = array(
		    'id' => $id,
		    'status_description' => $feature->getStatusDescription(),
		    'last_run' => date('Y/m/d H:i', $feature->getLastRun()),
		    'last_message' => implode("\n", $description),
		    'success' => 1
		);
	}

	function checkTest() {
		global $current_user;
		$this->data['success'] = 0;
		if (!$current_user->authorized) {
			$this->error('Auth');
			return;
		}

		if ($current_user->getRole() < User::ROLE_SITE_ADMIN) {
			$this->error('Must be admin');
			return;
		}

		$id = isset($_POST['id']) ? (int) $_POST['id'] : false;
		if (!$id) {
			$this->error('Illegal id');
			return;
		}

		$feature = Features::getInstance()->getByIdLoaded($id);
		/* @var $feature Feature */
		$this->data = array(
		    'id' => $id,
		    'status_description' => $feature->getStatusDescription(),
		    'last_run' => date('Y/m/d H:i', $feature->getLastRun()),
		    'last_message' => $feature->getLastMessage(),
		    'success' => 1
		);
	}

}