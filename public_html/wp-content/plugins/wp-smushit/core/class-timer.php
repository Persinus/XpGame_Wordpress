<?php

namespace Smush\Core;

class Timer {
	/**
	 * @var float
	 */
	private $time_start;

	public function start() {
		$this->time_start = microtime( true );
	}

	public function end( $round = true ) {
		$time_end = microtime( true );
		$time     = $time_end - $this->time_start;
		if ( $round ) {
			$time = round( $time, 2 );
		}
		return $time;
	}
}
