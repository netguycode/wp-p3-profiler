<script type="text/javascript">
	// Set up the tabs
	jQuery(document).ready(function($) {
		$("#toggle-glossary").click(function() {
			$("#glossary-terms").toggle();
			if ("Hide Glossary" == $("#toggle-glossary").html()) {
				$("#toggle-glossary").html("Show Glossary");
			} else {
				$("#toggle-glossary").html("Hide Glossary");
			}
		});
		$("#glossary-terms td.term").click(function() {
			var definition = $("div.definition", $(this)).html();
			$("#wpp-glossary-term-display").html(definition);
			$("#wpp-glossary-table td.term.hover").removeClass("hover");
			$(this).addClass("hover");
		});
		$("#wpp-glossary-table td.term:first").click();
		$("#wpp-hide-glossary").click(function() {
			if ("Hide" == $(this).html()) {
				$("#wpp-glossary-table tbody").hide();
				$("#wpp-glossary-table tfoot").hide();
				$(this).html("Show");
			} else {
				$("#wpp-glossary-table tbody").show();
				$("#wpp-glossary-table tfoot").show();
				$(this).html("Hide");
			}
		});
		// $("#wpp-hide-glossary").trigger("click");
		$("#wpp-glossary-container").dblclick(function() {
			$("#wpp-hide-glossary").trigger("click");
		});
	});
</script>

<div id="wpp-help-toc"></div>

<h2>How do I use this?</h2>
<blockquote>
	Simply click "Start Scan" and run an automated scan on your site.
	The scanner will generate some traffic on your site and monitor your site's
	performance on the server, then show you the results.  You can then decide
	what action to take.
</blockquote>

<h2>What do I do with these results?</h2>
<blockquote>
	If your site's load time is within an acceptable range (usually < 0.5 seconds)
	then you may want to look at other possibilities to explain any sluggish
	loading.  For example, loading large images, large videos, or lots of content
	can lead your page to render slowly.  Tools like <a href="http://www.webpagetest.org/" target="_blank">webpagetest.org</a>,
	<a href="http://getfirebug.com/" target="_blank">Firebug</a>, or Safari or
	Chrome's developer console can show you a connection breakdown of your site's
	content.
</blockquote>


<h2>How does this work?</h2>
<blockquote>
	This plugin is only active when you click "Start Scan."  Once it's active, it
	detects visits from your IP address and actively monitors all function calls
	that happen when the WordPress page is generated on the server.  It then
	records the information in a scan file that you can view later in a report
	format.  When the scan is complete, or you click "Stop Scan," the plugin
	is dormant again.
</blockquote>

<h2>Which loader am I using?</h2>
<blockquote>
	The plugin should be active at the earliest point in the code execution.
	If the plugin can be loaded through an <code>auto_prepend_file</code> configuration
	directive from a .htaccess file or a <a href="http://php.net/manual/en/configuration.file.per-user.php" target="_blank">.user.ini</a>
	file, but be careful.  The .user.ini files are cached, so you must remove the
	entry from your .user.ini file before you remove this plugin.
	<br /><br />
	This plugin will automatically enable itself in .htaccess if possible, and if
	not, it will create a <a href="http://codex.wordpress.org/Must_Use_Plugins" target="_blank">must-use</a>
	plugin to load before other plugins.  If all else fails, it will run like a
	regular plugin.
	<br /><br />
	You are currently using: 
<?php

// .htaccess file test
$htaccess_file = P3_PATH . '/../../../.htaccess';
$htaccess_content = '';
if (file_exists($htaccess_file)) {
	$htaccess_content = extract_from_markers($htaccess_file, 'wp-profiler');
	foreach ($htaccess_content as $k => $v) {
		if ('#' == substr(trim($v), 0, 1)) {
			unset($htaccess_content[$k]); // Get rid of comment lines
		}
	}
	$htaccess_content = implode("\n", $htaccess_content);
}

// must-use plugin file
$mu_file = P3_PATH . '/../../mu-plugins/wp-profiler.php';

// List php ini files
$ini_files = array_filter(
	array_merge(
			array(php_ini_loaded_file()),
			explode(',', php_ini_scanned_files()
		)
	)
);
?>

<?php /* .htaccess file is there, the profiler content is there, hasn't been commented out, and the auto_prepend_file directive is active */ ?>
<?php  if (file_exists($htaccess_file) && !empty($htaccess_content) && false !== strpos($htaccess_content, 'start-profile.php') && false !== strpos(ini_get('auto_prepend_file'), 'start-profile.php')) : ?>
	<a href="http://php.net/manual/en/configuration.changes.php" target="_blank">.htaccess file</a>
	- <code><?php echo realpath($htaccess_file); ?></code>
<?php /* the auto_prepend_file directive is active */ ?>
<?php elseif (false !== strpos(ini_get('auto_prepend_file'), 'start-profile.php')): ?>
	<a href="http://www.php.net/manual/en/configuration.file.php" target="_blank">php.ini</a>
	<?php if (version_compare(phpversion(), '5.3.0') >= 0) : ?>
		or <a href="http://www.php.net/manual/en/configuration.file.per-user.php" target="_blank">.user.ini</a>
	<?php endif; ?>
	entry from one of these files:
	<ul>
		<?php foreach($ini_files as $file) : ?>
			<ol><code><?php echo trim($file); ?></code></ol>
		<?php endforeach; ?>
	</ul>
<?php /* must-use plugin file is there and not-empty */ ?>
<?php elseif (file_exists($mu_file) && filesize($mu_file) > 0): ?>
	<a href="http://codex.wordpress.org/Must_Use_Plugins" target="_blank">must-use plugin</a>
	- <code><?php echo realpath($mu_file); ?></code>
<?php /* default, using this plugin file */ ?>
<?php else: ?>
	<a href="http://codex.wordpress.org/Plugins" target="_blank">plugin</a>
	- <code><?php echo realpath(P3_PATH . '/wp-profiler.php'); ?></code>
<?php endif; ?>
</blockquote>

<h2>How accurate are these results?</h2>
<blockquote>
	These results have an inherent margin of error.  This is using php, inside a
	plugin, inside WordPress, to measure the performance of other plugins inside
	WordPress, in php.  This plugin is changing the environment in order to measure
	it, and that makes it impossible to get completely accurate results.
	<br /><br />
	It does a really close job, though!  You can see the "margin of error" on the
	Advanced Metrics screen to see the discrepancy between the measured results
	(timing from when your site's php code started running to when it was finished)
	and the expected results (adding up all of the plugins, core, theme, profile time)
	and that will show you how accurate this is.
	<br /><br />
	If you want more accurate results, you'll need to resort to a different profiler
	like <a href="http://xdebug.org/" target="_blank">xdebug</a>, but this will
	not break down results by plugin.
</blockquote>

<h2>Why is <em>some plugin name</em> slow?</h2>
<blockquote>
	WordPress is a complex ecosystem of plugins and themes, and it lives on a
	complex ecosystem of software (your web server).
	<br /><br />
	If a plugin is showing as slow only once, this is probably an anomaly,
	a transient hiccup, and can be safely ignored.
	<br /><br />
	If a plugin is showing a slow once on a reguarly basis (e.g. every
	time you run a scan, once a day, once an hour) then it may be due to a
	scheduled task.  Plugins that backup your site, monitor your site for changes,
	contact outside sources (e.g. RSS feeds), warm up caches, etc. can exhibit
	this kind of behavior.
	<br /><br />
	If a plugin is showing as fast-slow-fast-slow-fast-slow, then it may because
	the plugin loads its main code, then a follow-up piece of code, like a
	piece of generated javascript.
	<br /><br />
	If a plugin is consistently showing as slow, then you may want to contact
	the plugin author or try deactivating the plugin temporarily to see if
	it makes a difference on your site.
</blockquote>

<h2>How are these results different from YSlow/PageSpeed/Webpagetest.org?</h2>
<blockquote>
	This plugin measures how your site was generated on the server.  Tools like
	YSlow!, PageSpeed, Webpagetest.org measure how your site looks to the browser.
</blockquote>

<h2>What can interfere with testing?</h2>
<blockquote>
	Opcode caches can interfere with php backtraces.  Leaving opcode caches turned
	on will result in timing that more accurately reflects your site's real performance,
	but the function calls to plugins may be "optimized" out of the backtraces and
	some plugins (especially those with only one hook) may not show up.  Disabling
	opcode caches will result in slower times, but will show all plugins.
	<br /><br />
	By default, this plugin attempts to clear any opcode caches before it runs.
	You can change this setting in the "Advanced Settings" link under "Start Scan."
</blockquote>

<h2>How much room do these profiles take up on my server</h2>
<?php
$total_size = 0;
$dir = opendir(P3_PROFILES_PATH);
while (false !== ($file = readdir($dir))) {
	if ('.' != $file && '..' != $file && '.json' == substr($file, -5)) {
		$total_size += filesize(P3_PROFILES_PATH . "/$file");
	}
}
closedir($dir);

?>
The scans are stored in <code><?php echo realpath(P3_PROFILES_PATH); ?></code> and
take up <?php echo $this->readable_size($total_size); ?> of disk space.  Each time you
run a scan, this storage requirement goes up, and each time you delete a scan, this
goes down.

<h2>Glossary</h2>
<div>
	<div id="wpp-glossary-container">
		<div class="ui-widget-header" id="wpp-glossary-header" style="padding: 8px;">
			<strong>Glossary</strong>
			<div style="position: relative; top: 0px; right: 80px; float: right;">
				<a href="javascript:;" id="wpp-hide-glossary">Hide</a>
			</div>
		</div>
		<div>
		<table class="p3-results-table" id="wpp-glossary-table" cellpadding="0" cellspacing="0" border="0">
			<tbody>
				<tr>
					<td colspan="2" style="border-left-width: 1px !important;">
						<div id="glossary">
							<table width="100%" cellpadding="0" cellspacing="0" border="0" id="glossary-terms">
								<tr>
									<td width="200" class="term"><strong>Total Load Time</strong>
										<div id="total-load-time-definition" style="display: none;" class="definition">
											How long the site took to load.  This is an observed measurement (start timing when the page was requested,
											stop timing when the page was delivered to the browser, calcuate the difference).  Lower is better.
										</div>
									</td>
									<td width="400" rowspan="12" id="wpp-glossary-term-display">&nbsp;</td>
								</tr>
								<tr>
									<td class="term"><strong>Site Load Time</strong>
										<div id="site-load-time-definition" style="display: none;" class="definition">
											The calculated total load time minus the profile overhead.  This is closer to your site's real-life load time.  Lower is better.
										</div>
									</td>
								</tr>
								<tr>
									<td class="term"><strong>Profile Overhead</strong>
										<div id="profile-overhead-definition" style="display: none;" class="definition">
											The load time spent in the profiling code.  Since using the profiler will slow down your load time, it is important
											to know how much impact the profiler is having on your site.  This won't impact your site's real-life load time.
										</div>
									</td>
								</tr>
								<tr>
									<td class="term"><strong>Plugin Load Time</strong>
										<div id="plugin-load-time-definition" style="display: none;" class="definition">
											The load time spent in plugins.  Because of the way WordPress is built, a function call can be traced from a
											plugin through a theme through the core.  The profiler prioritizes plugin calls first, theme calls second, and
											core calls last.  Lower is better.
										</div>
									</td>
								</tr>
								<tr>
									<td class="term"><strong>Theme Load Time</strong>
										<div id="theme-load-time-definition" style="display: none;" class="definition">
											The load time spent in the theme.  Because of the way WordPress is built, a function call can be traced from a
											plugin through a theme through the core.  The profiler prioritizes plugin calls first, theme calls second, and
											core calls last.  Lower is better.
										</div>
									</td>
								</tr>
								<tr>
									<td class="term"><strong>Core Load Time</strong>
										<div id="core-load-time-definition" style="display: none;" class="definition">
											The load time spent in the WordPress core.  Because of the way WordPress is built, a function call can be traced from a
											plugin through a theme through the core.  The profiler prioritizes plugin calls first, theme calls second, and
											core calls last.  This will probably be constant.
										</div>
									</td>
								</tr>
								<tr>
									<td class="term"><strong>Margin of Error</strong>
										<div id="drift-definition" style="display: none;" class="definition">
											This is the difference between the observed runtime (what actually happened) and expected runtime (adding up the plugin
											runtime, theme runtime, core runtime, and profiler overhead).  There are several reasons this margin of error can exist.
											Most likely, the profiler is missing microsends while it's doing math to add up the runtime it just observed.  Using a
											network clock to set the time (NTP) can also cause minute timing changes.  Ideally, this number should be zero, but
											there's nothing you can do to change it.  It will give you an idea of how accurate the other results are.
											</ul>
										</div>
									</td>
								</tr>
								<tr>
									<td class="term"><strong>Observed</strong>
										<div id="observed-definition" style="display: none;" class="definition">
											How long the site took to load.  This is an observed measurement (start timing when the page was requested,
											stop timing when the page was delivered to the browser, calcuate the difference).
										</div>
									</td>
								</tr>
								<tr>
									<td class="term"><strong>Expected</strong>
										<div id="expected-definition" style="display: none;" class="definition">
											The expected site load time calculated by adding plugin load time + core load time + theme load time + profiler overhead.
										</div>
									</td>
								</tr>
								<tr>
									<td class="term"><strong>Plugin Function Calls</strong>
										<div id="plugin-funciton-calls-definition" style="display: none;" class="definition">
											The number of php function calls generated by a plugin.  Lower is better.
										</div>
									</td>
								</tr>
								<tr>
									<td class="term"><strong>Memory Usage</strong>
										<div id="memory-usage-definition" style="display: none;" class="definition">
											The amount of RAM usage observed.  This is reporeted by <a href="http://php.net/memory_get_peak_usage" target="_blank">memory_get_peak_usage()</a>.  Lower is better.
										</div>
									</td>
								</tr>
								<tr>
									<td class="term"><strong>MySQL Queries</strong>
										<div id="mysql-queries-definition" style="display: none;" class="definition">
											The count of queries sent to the database.  This reported by the WordPress function <a href="http://codex.wordpress.org/Function_Reference/get_num_queries" target="_new">get_num_queries()</a>.  Lower is better.
										</div>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
</div>
