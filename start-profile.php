<?php

// If profiling hasn't started, start it
if (!isset($GLOBALS['wpp_profiler'])) {
	declare(ticks=1); // Capture ever user function call
	require_once (realpath(dirname(__FILE__)). '/class.wpp-profiler.php');
	$GLOBALS['wpp_profiler'] = new wpp_profiler(); // Go
}

?>