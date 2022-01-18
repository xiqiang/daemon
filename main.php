<?php

/**
 * daemon-main
 * 
 * reference:
 * http://www.xarg.org/2016/07/how-to-write-a-php-daemon/
 **/

// timezone
date_default_timezone_set('Asia/Shanghai');

// work dir
chdir('../../../daemon');

require_once 'config.php';
require_once 'core/frame.php';
require_once 'core/table.php';

require_once 'demo/launcher.php';

// cli only
if (php_sapi_name() != 'cli') {
	die('Should run in CLI');
}

// fork child process
$pid = pcntl_fork();
if ($pid < 0) {
	die("Can't Fork!");
} else if ($pid > 0) {
	exit();
}

Frame::log(E_NOTICE, DAEMON_NAME . ' started at ' . date(DATE_RFC822));

// process title
cli_set_process_title(DAEMON_NAME);

// group leader
if (posix_setsid() === -1) {
	die('Could not detach');
}

// signnals
Frame::registerSignal();

// save pid, close terminal
if (DAEMON_FORK) {
	Frame::registerEnv();
}

// run
demo\Launcher::getInstance()->run();

// delete pid
unlink(DAEMON_PID);
Frame::log(E_NOTICE, DAEMON_NAME . ' shut down normally at ' . date(DATE_RFC822));
