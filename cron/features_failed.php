<pre><?php
ini_set('display_errors', 1);
require '../config.php';

if (file_exists('../localconfig.php'))
	require_once '../localconfig.php';
else
	$local_config = array();
Config::init($local_config);

chdir(Config::need('base_path'));

echo Config::need('base_path');

require 'include.php';
$test_delay = 1;
$test_delay_normal = 1800;
$failed_cnt = 0;
$max_failed_cnt = 100;
$lockfile = 'cron/features_failed.lock';

function _log($s) {
	echo time() . ' ' . $s . "\n";
}

function lock_active() {
	global $lockfile;
	_log('lock');
	file_put_contents($lockfile, time());
}

$last_active = (int) file_get_contents($lockfile);
if (time() - $last_active > $test_delay) {
	while (true) {
		lock_active();
		work();
	}
} else {
	file_put_contents($lockfile, 0);
	die('another demon');
}

function work() {
	global $test_delay, $test_delay_normal, $failed_cnt, $max_failed_cnt;
	$query = 'SELECT `id` FROM `features` WHERE 
		(`status`=' . Feature::STATUS_FAILED . ' AND `last_run`<(' . (time() - 10) . '))
		ORDER BY `last_run`';

	$arr = Database::sql2array($query, 'id');
	$features = Features::getInstance()->getByIdsLoaded(array_keys($arr));
	foreach ($features as $feature) {
		/* @var $feature Feature */
		lock_active();
		$feature->dropCache();
		$feature->_run();
		_log($feature->getFilePath());
	}
	lock_active();
	if (!count($features)) {
		_log('no features to process');
		sleep($test_delay);
		$failed_cnt++;
	}

	if ($max_failed_cnt < $failed_cnt) {
		_log('nothing to test count ' . $failed_cnt);
		die();
	}
}

