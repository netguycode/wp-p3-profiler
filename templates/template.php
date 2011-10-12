<?php
$wpp_action = '';
if (!empty($_REQUEST['wpp_action'])) {
	$wpp_action = $_REQUEST['wpp_action'];
}
$scan = $this->get_latest_profile();
if (empty($wpp_action) || 'current-scan' == $wpp_action) {
	$wpp_action = 'current-scan';
} elseif ('view-scan' == $wpp_action && !empty($_REQUEST['name'])) {
	$scan = sanitize_file_name($_REQUEST['name']);
	if (!file_exists(WPP_PROFILES_PATH . "/$scan")) {
		wp_die('<div id="message" class="error"><p>Scan does not exist</p></div>');
	}
	$scan = WPP_PROFILES_PATH . "/$scan";
}
$button_current_checked = '';
$button_history_checked = '';
$button_help_checked    = '';
if ('current-scan' == $wpp_action) {
	$button_current_checked = 'checked="checked"';
} elseif ('help' == $wpp_action) {
	$button_help_checked    = 'checked="checked"';
} else {
	$button_history_checked = 'checked="checked"';
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$("#button-current-scan").click(function() {
			location.href = "<?php echo add_query_arg(array('wpp_action' => 'current-scan')); ?>";
		});
		$("#button-history-scans").click(function() {
			location.href = "<?php echo add_query_arg(array('wpp_action' => 'list-scans')); ?>";
		});
		$("#button-help").click(function() {
			location.href = "<?php echo add_query_arg(array('wpp_action' => 'help')); ?>";
		})
		$(".wpp-button").button();
		$("#wpp-navbar").buttonset();
		$("#wpp-navbar").corner("round 8px");
		$(".wpp-big-button").buttonset();
		$("#wpp-results-table tr:even").addClass("even");
	});
</script>
<div id="wpp-wrap">

	<!-- Header icon / title -->
	<div id="icon-plugins" class="icon32"><br/></div>
	<h2>Plugin Performance</h2>
	
	<!-- Header navbar -->
	<div class="ui-widget-header" id="wpp-navbar">
		<input type="radio" name="wpp-nav" id="button-current-scan" <?php echo $button_current_checked ?> /><label for="button-current-scan">Current</label>
		<input type="radio" name="wpp-nav" id="button-history-scans" <?php echo $button_history_checked; ?> /><label for="button-history-scans">History</label>
		<input type="radio" name="wpp-nav" id="button-help" <?php echo $button_help_checked; ?> /><label for="button-help">Help</label>
	</div>

	<!-- Start / stop button and callouts -->
	<?php

		// If there's a scan, create a viewer object
		if (!empty($scan)) {
			try {
				$profile = new wpp_profile_reader($scan);
			} catch (Exception $e) {
				wp_die('<div id="message" class="error"><p>Error reading scan</p></div>');
			}
		} else {
			$profile = null;
		}
		
		// Show the callouts bar
		require_once(WPP_PATH . '/templates/callouts.php');
	?>

	<!-- View scan or show a list of scans -->
	<?php if (('current-scan' == $wpp_action && !empty($scan)) || 'view-scan' == $wpp_action) : ?>
		<?php require_once(WPP_PATH . '/templates/view-scan.php'); ?>
	<?php elseif ('help' == $wpp_action) : ?>
		<?php require_once(WPP_PATH . '/templates/help.php'); ?>
	<?php else : ?>
		<?php require_once(WPP_PATH . '/templates/list-scans.php'); ?>
	<?php endif; ?>

	<div id="wpp-copyright">
		Copyright &copy; 2011 <a href="http://www.godaddy.com/" target="_blank">GoDaddy.com</a>
	</div>
</div>
