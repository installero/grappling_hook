<?php

class FeaturesWriteModule {

	function process() {
		$data = array(
		    'title' => isset(Request::$post['title']) ? prepare_review(Request::$post['title'], '') : false,
		    'path' => isset(Request::$post['path']) ? prepare_review(Request::$post['path'], '') : false,
		    'group_id' => isset(Request::$post['group_id']) ? (int) Request::$post['group_id'] : false,
		    'last_run' => 0,
		    'status' => 0,
		    'last_message' => '',
		);
		Features::getInstance()->create($data);
	}

}