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
$max_failed_cnt = 3600;
$lockfile = 'cron/features_run.lock';

function _log($s) {
	echo time() . ' ' . $s . "\n";
}

$gid = rand(0, 100500);

$start = 0;
$end = 0;
$last_lock = 0;

function _log_start() {
	global $start;
	$start = time();
}

function _log_end($featurename, $last_run) {
	global $start , $gid;
	$end = time();
	$query = 'INSERT INTO `cron_log` SET `cronid`='.((int)$gid).',`start`=' . $start . ', `end`=' . $end . ', `feature`=' . Database::escape($featurename) . ',`feature_state_change`=' . $last_run;
	Database::query($query);
}

function lock_active() {
	global $lockfile, $last_lock;
	_log('lock');
	$last_lock_file = file_get_contents($lockfile);
	// писали в файл, и все сходится
	if ($last_lock && ($last_lock == $last_lock_file)) {
		$last_lock = time();
	} else if ($last_lock) {
		// кто-то пишет в файл кроме нас!
		_log_start();
		_log_end('another demon - lock', time());
		die('another demon - lock');
	}else// начинаем писать в лок файл
		$last_lock = time();
	file_put_contents($lockfile, $last_lock);
}

$last_active = (int) file_get_contents($lockfile);
if (time() - $last_active > 30) {
	_log_start();
	_log_end('start', time());
	while (true) {
		lock_active();
		work();
	}
} else {
	_log_start();
	_log_end('another demon', time());
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
	}else
		$failed_cnt = 0;

	if ($max_failed_cnt < $failed_cnt) {
		_log('nothing to test count ' . $failed_cnt);
		_log_start();
		_log_end('die', time());
		die();
	}
}

