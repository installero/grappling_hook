<?php

class FeaturesWriteModule extends BaseWriteModule {

	function process() {
		switch (Request::post('action')) {
			case 'run':
				$this->_run();
				break;
			default :
				$this->_new();
				break;
		}
	}

	function _new() {
		$data = array(
		    'title' => isset(Request::$post['title']) ? prepare_review(Request::$post['title'], '') : false,
		    'description' => isset(Request::$post['description']) ? prepare_review(Request::$post['description']) : false,
		    'filepath' => isset(Request::$post['filepath']) ? prepare_review(Request::$post['filepath'], '') : false,
		    'group_id' => isset(Request::$post['group_id']) ? (int) Request::$post['group_id'] : false,
		    'last_run' => 0,
		    'status' => 0,
		    'last_message' => '',
		);
		Features::getInstance()->_create($data);
	}

	function _run() {
		$id = Request::post('id');
		if ($id) {
			$feature = Features::getInstance()->getByIdLoaded($id);
			list($success, $description) = $feature->_run();
			$this->setWriteParameter('features_module', 'run_result', implode("\n", $description));
		}
	}

}