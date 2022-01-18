<?php

namespace demo {

	use \Frame as Frame;
	use \Table as Table;

	class Launcher
	{
		private static $instance = null;
		private function __construct() {
		}

		public static function getInstance() {
			if (self::$instance == null)
				self::$instance = new Launcher();
			return self::$instance;
		}

		public function run()
		{
			$tabData = Table::read("demo/table/demo.txt");
			print_r($tabData);

			// Loop
			Frame::echos(DAEMON_NAME);
			Frame::echos(" runnning...\n", "green");
			do {
				if (DAEMON_FORK) {

					sleep(SLEEP_SECONDS);
				}
			} while (Frame::$run);

			Frame::echos(DAEMON_NAME);
			Frame::echos(" finished.\n", "yellow");
		}
	}
}
