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
		exec('chmod -R g+w '.Config::need('features_path').'../features/');
	}

	function _new() {
		if (Request::post('id'))
			return $this->_update();
		$data = array(
		    'title' => isset(Request::$post['title']) ? prepare_review(Request::$post['title'], '') : false,
		    'description' => isset(Request::$post['description']) ? prepare_review(Request::$post['description']) : false,
		    'filepath' => isset(Request::$post['filepath']) ? prepare_review(Request::$post['filepath'], '') : false,
		    'group_id' => isset(Request::$post['group_id']) ? (int) Request::$post['group_id'] : false,
		    'last_run' => 0,
		    'status' => 0,
		    'last_message' => '',
		);
		if ($data['title'])
			$data['id'] = Features::getInstance()->_create($data, false);
		@ob_end_clean();

		if (1 || $data['description']) {
			// пишем в файл
			$f = Config::need('features_path').'../features/' . Features::getInstance()->getByIdLoaded($data['id'])->getFilePath();
			if (!file_exists($f)) {
				@mkdir(Config::need('features_path').'../features/' . Features::getInstance()->getByIdLoaded($data['id'])->getFolder());
				file_put_contents($f, $data['description']);
				$file_modify = (int) @filemtime($f);
				clearstatcache();
				$query = 'UPDATE `features` SET `file_modify` = ' . $file_modify . ' WHERE `id`=' . $data['id'];
				Database::query($query);
			} else {
				$file_modify = (int) @filemtime($f);
				if ($file_modify > Request::post('file_modify')) {
					// файл новее чем в базе 
					$query = 'UPDATE `features` SET `file_modify` = ' . $file_modify . ' WHERE `id`=' . $data['id'];
					Database::query($query);
					throw new Exception(date('Y-m-d H:i:s') . ' File was modified at ' . date('Y-m-d H:i:s', $file_modify) . ', fetched version is ' . date('Y-m-d H:i:s', Request::post('file_modify')) . '. Please refresh page');
				} else {
					file_put_contents($f, $data['description']);
					clearstatcache();
					$file_modify = (int) @filemtime($f);
					clearstatcache();
					$query = 'UPDATE `features` SET `file_modify` = ' . $file_modify . ' WHERE `id`=' . $data['id'];
					Database::query($query);
				}
			}
		}

		header('Location:' . Config::need('www_path') . '/features/' . $data['id']);
		exit(0);
	}

	function _update() {
		$data = array(
		    'id' => isset(Request::$post['id']) ? prepare_review(Request::$post['id'], '') : false,
		    'title' => isset(Request::$post['title']) ? prepare_review(Request::$post['title'], '') : false,
		    'description' => isset(Request::$post['description']) ? prepare_review(Request::$post['description']) : false,
		    'filepath' => isset(Request::$post['filepath']) ? prepare_review(Request::$post['filepath'], '') : false,
		    'group_id' => isset(Request::$post['group_id']) ? (int) Request::$post['group_id'] : false,
		    'db_modify' => time(),
		);
		
		$oldf = Features::getInstance()->getByIdLoaded($data['id']);
		/*@var $oldf Feature*/
		
		$old_group = $oldf->data['group_id'];
		$new_group = $data['group_id'];
		
		$source= Config::need('features_path').'../features/'.$oldf->getFilePath();
			$query = 'SELECT `folder` FROM `feature_groups` WHERE `id`=' . $new_group;
			$new_folder = Database::sql2single($query);
			$dest = Config::need('features_path').'../features/'.$new_folder . '/' . $oldf->data['filepath'] . '.feature';

		$data['description'] = str_replace("\r\n", "\n", $data['description']);

		if ($data['title'] && $data['id'])
			Features::getInstance()->getByIdLoaded($data['id'])->_update($data);

		if (1 || $data['description']) {
			// пишем в файл
			$f = Config::need('features_path').'../features/' . Features::getInstance()->getByIdLoaded($data['id'])->getFilePath();
			if (!file_exists($f)) {
				@mkdir(Config::need('features_path').'../features/' . Features::getInstance()->getByIdLoaded($data['id'])->getFolder());
				file_put_contents($f, $data['description']);
				$file_modify = (int) @filemtime($f);
				clearstatcache();
				$query = 'UPDATE `features` SET `file_modify` = ' . $file_modify . ' WHERE `id`=' . $data['id'];
				Database::query($query);
			} else {
				$file_modify = (int) @filemtime($f);
				if ($file_modify > Request::post('file_modify')) {
					// файл новее чем в базе 
					$query = 'UPDATE `features` SET `file_modify` = ' . $file_modify . ' WHERE `id`=' . $data['id'];
					Database::query($query);
					throw new Exception(date('Y-m-d H:i:s') . ' File was modified at ' . date('Y-m-d H:i:s', $file_modify) . ', fetched version is ' . date('Y-m-d H:i:s', Request::post('file_modify')) . '. Please refresh page');
				} else {
					file_put_contents($f, $data['description']);
					clearstatcache();
					$file_modify = (int) @filemtime($f);
					clearstatcache();
					$query = 'UPDATE `features` SET `file_modify` = ' . $file_modify . ' WHERE `id`=' . $data['id'];
					Database::query($query);
				}
			}
		}
		
		if($old_group != $new_group){
			// move to another folder
			copy($source, $dest);
			exec('chmod -R g+w '. $dest);
			unlink($source);
			Features::getInstance()->dropCache($oldf->id);
		}

		@ob_end_clean();
		header('Location: ' . Config::need('www_path') . '/features');
		exit(0);
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