<?php

/**
 * daemon config
 **/

define('DAEMON_NAME', 'daemon-ps');

define('DAEMON_PID', './var/' . DAEMON_NAME . '.pid');
define('DAEMON_LOG', './var/' . DAEMON_NAME . '.log');

define('DAEMON_FORK',  empty($argv[1]) || 'cli' != $argv[1]);

define('SLEEP_SECONDS', 1);

define('TDOUT_FILE', './var/' . DAEMON_NAME . '.stdout');
define('TDERR_FILE', './var/' . DAEMON_NAME . '.stderr');
