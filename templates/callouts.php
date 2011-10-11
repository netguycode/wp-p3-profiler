<script type="text/javascript">

	/*****************************************************************/
	/**  AUTO SCANNER HELPER OBJECT                                 **/
	/*****************************************************************/
	// This will load all of the pages in the list, then turn off
	// the profile mode and view the results when complete.
	var WPP_Scan = {

		// List of pages to scan
		pages: <?php echo json_encode($this->list_of_pages()); ?>,

		// Current page
		current_page: 0,

		// Pause flag
		paused: false,

		// Start
		start: function() {
			
			// Form data
			data = {
				'wpp_ip' : jQuery('#wpp-ip').val(),
				'wpp_scan_name' : jQuery("#wpp-scan-name").val(),
				'action' : 'wpp_start_scan',
				'wpp_nonce' : jQuery("#wpp_nonce").val()
			}

			// Turn on the profiler
			jQuery.post(ajaxurl, data, function(response) {
				if (1 != response) {
					alert("Error response.  Code: " + response);
				} else {

					// Start scanning pages
					jQuery("#wpp-scan-frame").attr("onload", "WPP_Scan.next_page();");
					jQuery("#wpp-scan-frame").attr("src", WPP_Scan.pages[0]);
					WPP_Scan.current_page = 0;
					WPP_Scan.update_display();
					
				}
			});
		},
		
		// Pause
		pause: function() {
			
			// Turn off the profiler
			data = {
				'action' : 'wpp_stop_scan',
				'wpp_nonce' : '<?php echo wp_create_nonce('wpp_ajax_stop_scan'); ?>'
			}
			jQuery.post(ajaxurl, data, function(response) {
				if (response.indexOf('.json') < 0) {
					alert("Error response.  Code: " + response);
				}

				// Hide the cancel button
				jQuery("#wpp-cancel-scan-buttonset").hide();
				jQuery("#wpp-resume-scan-buttonset").show();
				jQuery("#wpp-view-results-buttonset").hide();
				
				// Show the view results button
				jQuery("#wpp-view-incomplete-results-submit").attr("data-scan-name", response);
				
				// Pause
				WPP_Scan.paused = true;
				
				// Update the caption
				jQuery("#wpp-scanning-caption").html("Scanning is paused.");
			});
		},

		// Resume
		resume: function() {
			
			data = {
				'wpp_ip' : jQuery('#wpp-ip').val(),
				'wpp_scan_name' : jQuery("#wpp-scan-name").val(),
				'action' : 'wpp_start_scan',
				'wpp_nonce' : jQuery("#wpp_nonce").val()
			}

			// Turn on the profiler
			jQuery.post(ajaxurl, data, function(response) {
				if (1 != response) {
					alert("Error response.  Code: " + response);
				} else {

					// Show the cancel button
					WPP_Scan.paused = false;
					jQuery("#wpp-cancel-scan-buttonset").show();
					jQuery("#wpp-resume-scan-buttonset").hide();
					jQuery("#wpp-view-results-buttonset").hide();
					WPP_Scan.update_display();
					WPP_Scan.next_page();
				}
			});
		},

		// Stop
		stop: function() {
			
			// Turn off the profiler
			data = {
				'action' : 'wpp_stop_scan',
				'wpp_nonce' : '<?php echo wp_create_nonce('wpp_ajax_stop_scan'); ?>'
			}
			jQuery.post(ajaxurl, data, function(response) {
				if (response.indexOf('.json') < 0) {
					alert("Error response.  Code: " + response);
				}
				
				// Hide the cancel button
				jQuery("#wpp-cancel-scan-buttonset").hide();
				jQuery("#wpp-resume-scan-buttonset").hide();
				jQuery("#wpp-view-results-buttonset").show();
				
				// Show the view results button
				jQuery("#wpp-view-results-submit").attr("data-scan-name", response);
				
				// Update the caption
				jQuery("#wpp-scanning-caption").html("Scanning is complete.");
			});
		},

		// Update the display
		update_display : function() {
			jQuery("#wpp-scanning-caption").html('<em class="wpp-em">Scanning ' + WPP_Scan.pages[WPP_Scan.current_page] + '</em>');
			jQuery("#wpp-progress").progressbar("value", (WPP_Scan.current_page / (WPP_Scan.pages.length - 1)) * 100);
		},

		// Look at the next page
		next_page : function() {

			// Paused?
			if (WPP_Scan.paused) {
				return true;
			}

			// Is it time to stop?
			if (WPP_Scan.current_page >= WPP_Scan.pages.length - 1) {
				WPP_Scan.stop();
				return true;
			}

			// Next page
			jQuery("#wpp-scan-frame").attr("src", WPP_Scan.pages[++WPP_Scan.current_page]);

			// Update the display
			WPP_Scan.update_display();
		}
	};


	// Onload functionality
	jQuery(document).ready(function($) {

		/*****************************************************************/
		/**  DIALOGS                                                    **/
		/*****************************************************************/

		// IP settings
		$("#wpp-ip-dialog").dialog({
			'autoOpen' : false,
			'closeOnEscape' : true,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width' : 400,
			'height' : 180,
			'title' : "Advanced Settings",
			'buttons' :
			[
				{
					text: 'OK',
					'class' : 'button-secondary',
					click: function() {
						$("#wpp-ip").val($("#wpp-advanced-ip").val());
						$(this).dialog("close");
					}
				},
				{
					text: 'Cancel',
					'class': 'wpp-cancel-button',
					click: function() {
						$(this).dialog("close");
					}
				}
			]
		});

		// Iframe scanner
		$("#wpp-scanner-dialog").dialog({
			'autoOpen' : false,
			'closeOnEscape' : true,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width': 800,
			'height' : 600,
			'title' : "Performance Scan",
			'dialogClass' : 'noPadding'
		});

		// Auto scan or manual scan 
		$("#wpp-scan-name-dialog").dialog({
			'autoOpen' : false,
			'closeOnEscape' : true,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width' : 325,
			'height' : 170,
			'title' : 'Scan Name'
			// 'dialogClass' : 'noTitle'
		});

		// Progress dialog
		$("#wpp-progress-dialog").dialog({
			'autoOpen' : false,
			'closeOnEscape' : false,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width' : 450,
			'height' : 110,
			'dialogClass' : 'noTitle'
		});



		/*****************************************************************/
		/**  LINKS                                                      **/
		/*****************************************************************/
		
		// Advanced settings link
		$("#wpp-advanced-settings").click(function() {
			$("#wpp-advanced-ip").val($("#wpp-ip").val());
			$("#wpp-ip-dialog").dialog("open");
		});



		/*****************************************************************/
		/**  BUTTONS                                                    **/
		/*****************************************************************/
		
		// Start scan button
		$("#wpp-start-scan-submit").click(function() {
			
			// Stay checked to keep the styling
			$(this).prop("checked", true);
			$(this).button("refresh");
			
			$("#wpp-scanner-dialog").dialog("open");
			$("#wpp-scan-name-dialog").dialog("open");
		});
		
		// Stop scan button
		$("#wpp-stop-scan-submit").click(function() {

			// Stay checked to keep the styling
			$(this).prop("checked", true);
			$(this).button("refresh");

			// Turn off the profiler
			data = {
				'action' : 'wpp_stop_scan',
				'wpp_nonce' : '<?php echo wp_create_nonce('wpp_ajax_stop_scan'); ?>'
			}
			jQuery.post(ajaxurl, data, function(response) {
				if (response.indexOf('.json') < 0) {
					alert("Error response.  Code: " + response);
				}
				location.reload();
			});
		});

		// Auto scan button
		$("#wpp-auto-scan-submit").click(function() {
			
			// Stay checked to keep the styling
			$(this).prop("checked", true);
			$(this).button("refresh");

			// Close the "auto or manual" dialog
			$("#wpp-scan-name-dialog").dialog("close");

			// Open the progress bar dialog
			$("#wpp-progress-dialog").dialog("open");

			// Initialize the progress bar to 0%
			$("#wpp-progress").progressbar({
				'value': 0
			});

			WPP_Scan.start();
		});

		// Manual scan button
		$("#wpp-manual-scan-submit").click(function() {
			
			// Stay checked to keep the styling
			$(this).prop("checked", true);
			$(this).button("refresh");

			
			// Form data
			data = {
				'wpp_ip' : jQuery('#wpp-ip').val(),
				'wpp_scan_name' : jQuery("#wpp-scan-name").val(),
				'action' : 'wpp_start_scan',
				'wpp_nonce' : jQuery("#wpp_nonce").val()
			}

			// Turn on the profiler
			jQuery.post(ajaxurl, data, function(response) {
				if (1 != response) {
					alert("Error response.  Code: " + response);
				}
			});

			$("#wpp-scan-name-dialog").dialog("close");
			$("#wpp-scan-caption").hide();
			$("#wpp-manual-scan-caption").show();
		});
		
		// Manual scan "I'm done" button
		$("#wpp-manual-scan-done-submit").click(function() {
			data = {
				'action' : 'wpp_stop_scan',
				'wpp_nonce' : '<?php echo wp_create_nonce('wpp_ajax_stop_scan'); ?>'
			}
			jQuery.post(ajaxurl, data, function(response) {
				if (response.indexOf('.json') < 0) {
					alert("Error response.  Code: " + response);
				} else {
					location.href = "<?php echo add_query_arg(array('wpp_action' => 'view-scan')); ?>&name=" + response;
				}
			})
			$("#wpp-scanner-dialog").dialog("close");
		});
		
		// Manual scan cancel link
		$("#wpp-manual-scan-cancel").click(function() {
			WPP_Scan.pause();
			$("#wpp-scanner-dialog").dialog("close");
		});

		// Cancel scan button
		$("#wpp-cancel-scan-submit").click(function() {
			
			// Stay checked to keep the styling
			$(this).prop("checked", true);
			$(this).button("refresh");

			WPP_Scan.pause();
		});
		
		// Resume
		$("#wpp-resume-scan-submit").click(function() {
			
			// Stay checked to keep the styling
			$(this).prop("checked", true);
			$(this).button("refresh");

			WPP_Scan.resume();
		});
		
		// View results button
		$("#wpp-view-results-submit").click(function() {

			// Stay checked to keep the styling
			$(this).prop("checked", true);
			$(this).button("refresh");

			// Close the dialogs
			jQuery("#wpp-scanner-dialog").dialog("close");
			jQuery("#wpp-progress-dialog").dialog("close");

			// View the scan
			location.href = "<?php echo add_query_arg(array('wpp_action' => 'view-scan')); ?>&name=" + $(this).attr("data-scan-name");
		});
		$("#wpp-view-incomplete-results-submit").click(function() {
			$("#wpp-view-results-submit").trigger("click");
		});


		/*****************************************************************/
		/**  OTHER                                                      **/
		/*****************************************************************/
		// Enable / disable buttons based on scan name input
		$("#wpp-scan-name").live("keyup", function() {
			if ($(this).val().match(/^[a-zA-Z0-9_\.-]+$/)) {
				$("#wpp-auto-scan-submit").button("enable")
				$("#wpp-manual-scan-submit").button("enable");
			} else {
				$("#wpp-auto-scan-submit").button("disable");
				$("#wpp-manual-scan-submit").button("disable");
			}
		});
		
		// Callouts
		$("div.wpp-callout-inner-wrapper")
		.corner("round 8px")
		.parent()
		.css("padding", "4px")
		.corner("round 10px");

		// Start / stop buttons
		$("#wpp-scan-form-wrapper").corner("round 8px");
	});
</script>
<table id="wpp-quick-report" cellpadding="0" cellspacing="0">
	<tr>

		<td>
			<div class="ui-widget-header" id="wpp-scan-form-wrapper">
				<?php if (file_exists(WPP_FLAG_FILE)) : ?>
					<!-- Stop scan button -->

					<?php $json = json_decode(file_get_contents(WPP_FLAG_FILE)); ?>
					<strong>IP:</strong><?php echo htmlentities($json->ip); ?>
					<div class="wpp-big-button"><input type="checkbox" checked="checked" id="wpp-stop-scan-submit" /><label for="wpp-stop-scan-submit">Stop Scan</label></div>
					<?php echo htmlentities($json->name); ?>

				<?php else : ?>

					<!-- Start scan button -->
					<?php echo wp_nonce_field('wpp_ajax_start_scan', 'wpp_nonce'); ?>
					<input type="hidden" id="wpp-ip" name="wpp_ip" value="<?php echo $GLOBALS['wpp_profiler']->get_ip(); ?>" />
					<strong>My IP:</strong><?php echo htmlentities($GLOBALS['wpp_profiler']->get_ip()); ?>
					<div class="wpp-big-button"><input type="checkbox" checked="checked" id="wpp-start-scan-submit" /><label for="wpp-start-scan-submit">Start Scan</label></div>
					<a href="javascript:;" id="wpp-advanced-settings">Advanced Settings</a>

				<?php endif; ?>
			</div>
		</td>

		<!-- First callout cell -->
		<td class="wpp-callout">
			<div class="wpp-callout-outer-wrapper">
				<div class="wpp-callout-inner-wrapper">
					<div class="wpp-callout-caption">Total plugins:</div>
					<div class="wpp-callout-data">
						<?php
						// Get the total number of plugins
						$active_plugins = count(get_mu_plugins());
						foreach (get_plugins() as $plugin => $junk) {
							if (is_plugin_active($plugin)) {
								$active_plugins++;
							}
						}
						echo $active_plugins;
						?>
					</div>
					<div class="wpp-callout-caption">(currently active)</div>
				</div>
			</div>
		</td>

		<!-- Second callout cell -->
		<td class="wpp-callout">
			<div class="wpp-callout-outer-wrapper" <?php if (!empty($scan)) : ?>title="From <?php echo basename($scan); ?><?php endif; ?>">
				<div class="wpp-callout-inner-wrapper">
					<div class="wpp-callout-caption">Plugin load time</div>
					<div class="wpp-callout-data">
						<?php if (null === $profile) : ?>
							<span class="wpp-faded-grey">n/a</span>
						<?php else : ?>
							<?php printf('%.3f', $profile->averages['plugins']); ?>
						<?php endif; ?>
					</div>
					<div class="wpp-callout-caption">(sec. per visit)</div>
				</div>
			</div>
		</td>

		<!-- Third callout cell -->
		<td class="wpp-callout">
			<div class="wpp-callout-outer-wrapper" <?php if (!empty($scan)) : ?>title="From <?php echo basename($scan); ?><?php endif; ?>">
				<div class="wpp-callout-inner-wrapper">
					<div class="wpp-callout-caption">Plugin impact</div>
					<div class="wpp-callout-data">
						<?php if (null === $profile) : ?>
							<span class="wpp-faded-grey">n/a</span>
						<?php else : ?>
							<?php printf('%.1f%%', $profile->averages['plugin_impact']); ?>
						<?php endif; ?>
					</div>
					<div class="wpp-callout-caption">(of page load time)</div>
				</div>
			</div>
		</td>

		<!-- Fourth callout cell -->
		<td class="wpp-callout">
			<div class="wpp-callout-outer-wrapper" <?php if (!empty($scan)) : ?>title="From <?php echo basename($scan); ?><?php endif; ?>">
				<div class="wpp-callout-inner-wrapper">
					<div class="wpp-callout-caption">MySQL Queries</div>
					<div class="wpp-callout-data">
						<?php if (null === $profile) : ?>
							<span class="wpp-faded-grey">n/a</span>
						<?php else : ?>
							<?php echo round($profile->averages['queries']); ?>
						<?php endif; ?>
					</div>
					<div class="wpp-callout-caption">Per visit</div>
				</div>
			</div>
		</td>

	</tr>
</table>

<!-- Dialog for IP settings -->
<div id="wpp-ip-dialog" class="wpp-dialog">
	IP address or pattern:<br />
	<input type="text" id="wpp-advanced-ip" style="width:90%;" size="35" value="<?php echo $GLOBALS['wpp_profiler']->get_ip(); ?>" title="Enter IP address or regular expression pattern" />
	<br />
	<em class="wpp-em">Example: 1.2.3.4 or (1.2.3.4|4.5.6.7)</em>
</div>

<!-- Dialog for iframe scanner -->
<div id="wpp-scanner-dialog" class="wpp-dialog">
	<iframe id="wpp-scan-frame" frameborder="0" src="<?php if (true === force_ssl_admin()) {echo str_replace('http://', 'https://', home_url());} else {echo home_url();} ?>"></iframe>
	<div id="wpp-scan-caption">
		The scanner will analyze the speed and resource usage of all active plugins on your website.
		It may take several minutes, and this window must remain open for the scan to finish successfully. 
	</div>
	<div id="wpp-manual-scan-caption" style="display: none;">
		<table>
			<tr>
				<td>
					Click the links and pages of your site, and the scanner will
					analyze the speed and resource usage of all of your active
					plugins.
				</td>
				<td width="220">
					<a href="javascript:;" id="wpp-manual-scan-cancel">Cancel</a>
					&nbsp;&nbsp;&nbsp;
					<span class="wpp-big-button"><input type="checkbox" id="wpp-manual-scan-done-submit" checked="checked" /><label for="wpp-manual-scan-done-submit">I'm Done</label></span>
				</td>
			</tr>
		</table>
	</div>
</div>

<!-- Dialog for choose manual or auto scan  -->
<div id="wpp-scan-name-dialog" class="wpp-dialog">
	Scan name:<br />
	<input type="text" name="wpp_scan_name" id="wpp-scan-name" title="Enter scan name here" value="scan_<?php echo date('Y-m-d'); ?>_<?php echo substr(md5(uniqid()), -8);?>" size="35" maxlength="100" />
	<br /><br />
	<em class="wpp-em">Select one:</em><br />
	<div class="wpp-big-button">
		<input type="checkbox" id="wpp-auto-scan-submit" checked="checked" /><label for="wpp-auto-scan-submit">Auto Scan</label>
		<input type="checkbox" id="wpp-manual-scan-submit" checked="checked" /><label for="wpp-manual-scan-submit">Manual Scan</label>
	</div>
</div>

<!-- Dialog for progress bar -->
<div id="wpp-progress-dialog" class="wpp-dialog">
	<div id="wpp-scanning-caption" style="height: 20px;">
		Scanning ...
	</div>
	<div id="wpp-progress"></div>
	
	<!-- Cancel button -->
	<div class="wpp-big-button" id="wpp-cancel-scan-buttonset">
		<input type="checkbox" id="wpp-cancel-scan-submit" checked="checked" /><label for="wpp-cancel-scan-submit">Stop Scan</label>
	</div>

	<!-- View / resume buttons -->
	<div class="wpp-big-button" id="wpp-resume-scan-buttonset" style="display: none;">
		<input type="checkbox" id="wpp-resume-scan-submit" checked="checked" /><label for="wpp-resume-scan-submit">Resume</label>
		<input type="checkbox" id="wpp-view-incomplete-results-submit" checked="checked" data-scan-name="" /><label for="wpp-view-incomplete-results-submit">View Results</label>
	</div>
	
	<!-- View results button -->
	<div class="wpp-big-button" id="wpp-view-results-buttonset" style="display: none;">
		<input type="checkbox" id="wpp-view-results-submit" checked="checked" data-scan-name="" /><label for="wpp-view-results-submit">View Results</label>
	</div>	
</div>