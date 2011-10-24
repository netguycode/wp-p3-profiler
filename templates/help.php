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

<h2>How do I use this?</h2>
<blockquote>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ac
turpis erat. Quisque porta metus at velit porta at euismod arcu accumsan. Aenean
lorem risus, pulvinar ac facilisis eu, consectetur vitae lacus. Cras
pellentesque lacinia orci, et dictum erat tempus nec. Cras id tincidunt eros.
Cras dignissim posuere scelerisque. Donec dignissim hendrerit porta. Nullam
interdum libero eget ligula sollicitudin tristique. Curabitur semper ullamcorper
augue quis ullamcorper. Donec rhoncus molestie mi, mollis dapibus metus laoreet
nec. Morbi sit amet eros ipsum. Vivamus dictum magna sed massa pellentesque
varius malesuada odio sodales. Morbi bibendum justo id felis egestas
condimentum. Fusce ac est nec orci mollis blandit sit amet et elit.</blockquote>

<h2>What do I do with these results?</h2>
<blockquote>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ac
turpis erat. Quisque porta metus at velit porta at euismod arcu accumsan. Aenean
lorem risus, pulvinar ac facilisis eu, consectetur vitae lacus. Cras
pellentesque lacinia orci, et dictum erat tempus nec. Cras id tincidunt eros.
Cras dignissim posuere scelerisque. Donec dignissim hendrerit porta. Nullam
interdum libero eget ligula sollicitudin tristique. Curabitur semper ullamcorper
augue quis ullamcorper. Donec rhoncus molestie mi, mollis dapibus metus laoreet
nec. Morbi sit amet eros ipsum. Vivamus dictum magna sed massa pellentesque
varius malesuada odio sodales. Morbi bibendum justo id felis egestas
condimentum. Fusce ac est nec orci mollis blandit sit amet et elit.</blockquote>


<h2>How does this work?</h2>
<blockquote>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ac
turpis erat. Quisque porta metus at velit porta at euismod arcu accumsan. Aenean
lorem risus, pulvinar ac facilisis eu, consectetur vitae lacus. Cras
pellentesque lacinia orci, et dictum erat tempus nec. Cras id tincidunt eros.
Cras dignissim posuere scelerisque. Donec dignissim hendrerit porta. Nullam
interdum libero eget ligula sollicitudin tristique. Curabitur semper ullamcorper
augue quis ullamcorper. Donec rhoncus molestie mi, mollis dapibus metus laoreet
nec. Morbi sit amet eros ipsum. Vivamus dictum magna sed massa pellentesque
varius malesuada odio sodales. Morbi bibendum justo id felis egestas
condimentum. Fusce ac est nec orci mollis blandit sit amet et elit.</blockquote>

<h2>Which loader am I using?</h2>
<blockquote>
<?php

// .htaccess file test
$htaccess_file = WPP_PATH . '/../../../.htaccess';
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
$mu_file = WPP_PATH . '/../../mu-plugins/wp-profiler.php';

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
	- <code><?php echo realpath(WPP_PATH . '/wp-profiler.php'); ?></code>
<?php endif; ?>
</blockquote>

<h2>How accurate are these results?</h2>
<blockquote>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ac
turpis erat. Quisque porta metus at velit porta at euismod arcu accumsan. Aenean
lorem risus, pulvinar ac facilisis eu, consectetur vitae lacus. Cras
pellentesque lacinia orci, et dictum erat tempus nec. Cras id tincidunt eros.
Cras dignissim posuere scelerisque. Donec dignissim hendrerit porta. Nullam
interdum libero eget ligula sollicitudin tristique. Curabitur semper ullamcorper
augue quis ullamcorper. Donec rhoncus molestie mi, mollis dapibus metus laoreet
nec. Morbi sit amet eros ipsum. Vivamus dictum magna sed massa pellentesque
varius malesuada odio sodales. Morbi bibendum justo id felis egestas
condimentum. Fusce ac est nec orci mollis blandit sit amet et elit.</blockquote>

<h2>Why is <em>some plugin name</em>?</h2>
<blockquote>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ac
turpis erat. Quisque porta metus at velit porta at euismod arcu accumsan. Aenean
lorem risus, pulvinar ac facilisis eu, consectetur vitae lacus. Cras
pellentesque lacinia orci, et dictum erat tempus nec. Cras id tincidunt eros.
Cras dignissim posuere scelerisque. Donec dignissim hendrerit porta. Nullam
interdum libero eget ligula sollicitudin tristique. Curabitur semper ullamcorper
augue quis ullamcorper. Donec rhoncus molestie mi, mollis dapibus metus laoreet
nec. Morbi sit amet eros ipsum. Vivamus dictum magna sed massa pellentesque
varius malesuada odio sodales. Morbi bibendum justo id felis egestas
condimentum. Fusce ac est nec orci mollis blandit sit amet et elit.</blockquote>

<h2>How are these results different from YSlow/PageSpeed/Webpagetest.org?</h2>
<blockquote>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ac
turpis erat. Quisque porta metus at velit porta at euismod arcu accumsan. Aenean
lorem risus, pulvinar ac facilisis eu, consectetur vitae lacus. Cras
pellentesque lacinia orci, et dictum erat tempus nec. Cras id tincidunt eros.
Cras dignissim posuere scelerisque. Donec dignissim hendrerit porta. Nullam
interdum libero eget ligula sollicitudin tristique. Curabitur semper ullamcorper
augue quis ullamcorper. Donec rhoncus molestie mi, mollis dapibus metus laoreet
nec. Morbi sit amet eros ipsum. Vivamus dictum magna sed massa pellentesque
varius malesuada odio sodales. Morbi bibendum justo id felis egestas
condimentum. Fusce ac est nec orci mollis blandit sit amet et elit.</blockquote>

<h2>What can interfere with testing?</h2>
<blockquote>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ac
turpis erat. Quisque porta metus at velit porta at euismod arcu accumsan. Aenean
lorem risus, pulvinar ac facilisis eu, consectetur vitae lacus. Cras
pellentesque lacinia orci, et dictum erat tempus nec. Cras id tincidunt eros.
Cras dignissim posuere scelerisque. Donec dignissim hendrerit porta. Nullam
interdum libero eget ligula sollicitudin tristique. Curabitur semper ullamcorper
augue quis ullamcorper. Donec rhoncus molestie mi, mollis dapibus metus laoreet
nec. Morbi sit amet eros ipsum. Vivamus dictum magna sed massa pellentesque
varius malesuada odio sodales. Morbi bibendum justo id felis egestas
condimentum. Fusce ac est nec orci mollis blandit sit amet et elit.</blockquote>

<h2>What can interfere with testing?</h2>
<blockquote>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ac
turpis erat. Quisque porta metus at velit porta at euismod arcu accumsan. Aenean
lorem risus, pulvinar ac facilisis eu, consectetur vitae lacus. Cras
pellentesque lacinia orci, et dictum erat tempus nec. Cras id tincidunt eros.
Cras dignissim posuere scelerisque. Donec dignissim hendrerit porta. Nullam
interdum libero eget ligula sollicitudin tristique. Curabitur semper ullamcorper
augue quis ullamcorper. Donec rhoncus molestie mi, mollis dapibus metus laoreet
nec. Morbi sit amet eros ipsum. Vivamus dictum magna sed massa pellentesque
varius malesuada odio sodales. Morbi bibendum justo id felis egestas
condimentum. Fusce ac est nec orci mollis blandit sit amet et elit.</blockquote>

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
		<table class="wpp-results-table" id="wpp-glossary-table" cellpadding="0" cellspacing="0" border="0">
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
