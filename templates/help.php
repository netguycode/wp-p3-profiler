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

<h2>Why is plugin <em>Xyz</em> slow?</h2>
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

<h2>These results are different than YSlow/PageSpeed/Webpagetest.org</h2>
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

<h2>Why is plugin <em>Xyz</em> slow??</h2>
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
