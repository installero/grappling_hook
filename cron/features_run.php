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
$test_delay_normal = 8 * 3600;
$failed_cnt = 0;
$max_failed_cnt = 100;
$lockfile = 'cron/features_run.lock';

function _log($s) {
	echo time() . ' ' . $s . "\n";
}

$start = 0;
$end = 0;

function _log_start() {
	global $start;
	$start = time();
}

function _log_end($featurename, $last_run) {
	global $start;
	$end = time();
	$query = 'INSERT INTO `cron_log` SET `start`=' . $start . ', `end`=' . $end . ', `feature`=' . Database::escape($featurename) . ',`feature_state_change`=' . $last_run;
	Database::query($query);
}

_log_start();
_log_end('start', time());

function lock_active() {
	global $lockfile;
	_log('lock');
	file_put_contents($lockfile, time());
}

$last_active = (int) file_get_contents($lockfile);
if (time() - $last_active > 30) {
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
		(`last_run`<(' . (time() - $test_delay) . ') AND (`status`=' . Feature::STATUS_WAIT_FOR_RUN . '))
			OR
		(`last_run`<(' . (time() - $test_delay_normal) . ') AND (`status`=' . Feature::STATUS_OK . '))
		ORDER BY `last_run`';

	$arr = Database::sql2array($query, 'id');
	$features = Features::getInstance()->getByIdsLoaded(array_keys($arr));
	foreach ($features as $feature) {
		/* @var $feature Feature */
		lock_active();
		$feature->dropCache();
		_log_start();
		$lr = $feature->data['last_run'];
		$feature->_run();
		_log_end($feature->getFilePath(), $lr);
		_log($feature->getFilePath());
	}
	lock_active();
	if (!count($features)) {
		_log('no features to process');
		sleep($test_delay);
		$failed_cnt++;
	}else $failed_cnt=0;

	if ($max_failed_cnt < $failed_cnt) {
		_log('nothing to test count ' . $failed_cnt);
		_log_start();
		_log_end('die', time());
		die();
	}
}

