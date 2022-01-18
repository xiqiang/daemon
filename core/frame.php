<?php

/**
 * daemon-frame
 **/

class Frame {

	// Flag if daemon is still running
	public static $run = true;

	// The screen terminal connected
	private static $screen = null;

	/**
	Sets up the signal handlers
	*/
	public static function registerSignal() {
		self::log(E_NOTICE, "registerSignal...");

		pcntl_async_signals(true);
		pcntl_signal(SIGTERM, 'Frame::_handleSignal');
		pcntl_signal(SIGHUP,  'Frame::_handleSignal');
		pcntl_signal(SIGUSR1, 'Frame::_handleSignal');
	}

	/**
	Registering the environment
	*/
	public static function registerEnv() {
		self::log(E_NOTICE, "registerEnv...");

		set_error_handler("Frame::_handleError");
		file_put_contents(DAEMON_PID, getmypid());
		self::_openConsole(posix_ttyname(STDOUT));

		//fclose(STDIN);
		//fclose(STDOUT);
		//fclose(STDERR);

		//$STDIN = fopen('/dev/null', 'r');
		//$STDOUT = fopen(TDOUT_FILE, 'wb');
		//$STDERR = fopen(TDERR_FILE, 'wb');		
	}

	/**
	The system log function
	*/
	public static function log($code, $msg, $var = null) {

		static $codeMap = array(
			E_ERROR   => "Error",
			E_WARNING => "Warning",
			E_NOTICE  => "Notice"
		);

		$msg = date('[d-M-Y H:i:s] ') . $codeMap[$code] . ': ' . $msg;

		if (null !== $var) {

			$msg.= "\n";
			$msg.= var_export($var, true);
			$msg.= "\n";
			$msg.="\n";
		}
		file_put_contents(DAEMON_LOG, $msg . "\n", FILE_APPEND);
	}

	public static function echos($text, $color="normal") {

		static $colors = array(
			'light_red' => "[1;31m",
			'light_green' => "[1;32m",
			'yellow' => "[1;33m",
			'light_blue' => "[1;34m",
			'magenta' => "[1;35m",
			'light_cyan' => "[1;36m",
			'white' => "[1;37m",
			'normal' => "[0m",
			'black' => "[0;30m",
			'red' => "[0;31m",
			'green' => "[0;32m",
			'brown' => "[0;33m",
			'blue' => "[0;34m",
			'cyan' => "[0;36m",
			'bold' => "[1m",
			'underscore' => "[4m",
			'reverse' => "[7m"
		);
	
		$str = chr(27) . $colors[$color] . $text . chr(27) . "[0m";
	
		if (false === DAEMON_FORK) {
			echo $str;
			return;
		}
	
		if (self::$screen === null) {
			return;
		}
	
		if (false === @fwrite(self::$screen, $str)) {
			self::$screen = null;
		}
	}
	
	/**
	Opens the console
	*/
	private static function _openConsole($screen) {
		if (!empty($screen) && false !== ($fd = fopen($screen, "c"))) {
			self::$screen = $fd;
		}
	}

	/**
	The error handler for PHP
	*/
	public static function _handleError($errno, $errstr, $errfile = "", $errline = 0, $errctx = array()) {
		if (error_reporting() == 0) {
			return;
		}

		self::log($errno, $errstr . " on line " . $errline . "(" . $errfile . ") -> " . var_export($errctx, true));

		/* Don't execute PHP's internal error handler */
		return true;
	}

	/**
	The signal handler function
	*/
	public static function _handleSignal($signo) {
		switch ($signo) {
			/*
			 * Attention: The sigterm is only recognized outside a mysqlnd poll()
			 */
			case SIGTERM:
				self::log(E_NOTICE, 'Received SIGTERM, dying...');
				self::$run = false;
				return;
			case SIGHUP:
				self::log(E_NOTICE, 'Received SIGHUP, rotate...');
				return;
			case SIGUSR1:
				self::log(E_NOTICE, 'Received SIGUSR1, ...');

				if (null !== self::$screen) {
					@fclose(self::$screen);
				}

				self::$screen = null;

				if (preg_match('|pts/([0-9]+)|', `who`, $out) && !empty($out[1])) {
					self::_openConsole('/dev/pts/' . $out[1]);
				}
		}
	}

}

