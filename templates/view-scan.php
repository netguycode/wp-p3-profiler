<?php
	$url_stats = array();
	$domain = '';
	if (!empty($profile)) {
		$url_stats = $profile->get_stats_by_url();
		$domain = @parse_url($profile->report_url, PHP_URL_HOST);
	}
	$pie_chart_id                 = substr(md5(uniqid()), -8);
	$runtime_chart_id             = substr(md5(uniqid()), -8);
	$query_chart_id               = substr(md5(uniqid()), -8);
	$component_breakdown_chart_id = substr(md5(uniqid()), -8);
	$component_runtime_chart_id   = substr(md5(uniqid()), -8);
?>
<script type="text/javascript">

	/**************************************************************/
	/**  Init                                                    **/
	/**************************************************************/

	// Raw json data (used in the charts for tooltip data
	var _data = [];
	<?php if (!empty($scan) && file_exists($scan)): ?>
		<?php foreach(file($scan, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) : ?>
			_data.push(<?php echo $line; ?>);
		<?php endforeach; ?>
	<?php endif; ?>

	// Set up the tabs
	jQuery(document).ready(function($) {
		$("#wpp-tabs").tabs();
		$("#results-table tr:even").addClass("even");
		$("#wpp-email-sending-dialog").dialog({
			'autoOpen' : false,
			'closeOnEscape' : false,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width' : 300,
			'height' : 115,
			'dialogClass' : 'noTitle'
		});
		$("#wpp-email-results-dialog").dialog({
			'autoOpen' : false,
			'closeOnEscape' : true,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width' : 500,
			'height' : 500,
			'title' : "Email Report",
			'buttons' :
			[
				{
					text: 'Send',
					'class' : 'button-secondary',
					click: function() {
						data = {
							'wpp_to'      : jQuery('#wpp-email-results-to').val(),
							'wpp_subject' : jQuery('#wpp-email-results-subject').val(),
							'wpp_results' : jQuery("#wpp-email-results-results").val(),
							'wpp_message' : jQuery("#wpp-email-results-message").val(),
							'action'      : 'wpp_send_results',
							'wpp_nonce'   : '<?php echo wp_create_nonce('wpp_ajax_send_results'); ?>'
						}
						
						// Open the "loading" dialog
						$("#wpp-email-sending-success").hide();
						$("#wpp-email-sending-error").hide();
						$("#wpp-email-sending-loading").show();
						$("#wpp-email-sending-close").hide();
						$("#wpp-email-sending-dialog").dialog("open");

						// Send the data
						jQuery.post(ajaxurl, data, function(response) {
							if ("1" == response) {
								$("#wpp-email-success-recipient").html(jQuery('#wpp-email-results-to').val());
								$("#wpp-email-sending-success").show();
								$("#wpp-email-sending-error").hide();
								$("#wpp-email-sending-loading").hide();
								$("#wpp-email-sending-close").show();
							} else {
								if ("-1" == response) {
									$("#wpp-email-error").html("nonce error");
								} else if ("0" == response.charAt(0)) {
									$("#wpp-email-error").html(response.substr(2));
								} else {
									$("#wpp-email-error").html("unknown error");
								}
								$("#wpp-email-sending-success").hide();
								$("#wpp-email-sending-error").show();
								$("#wpp-email-sending-loading").hide();
								$("#wpp-email-sending-close").show();
							}
						});
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
		$("#wpp-email-sending-close-submit").click(function() {
			$(this).prop("checked", true);
			$(this).button("refresh");
			$("#wpp-email-sending-dialog").dialog("close");
			$("#wpp-email-results-dialog").dialog("close");
		});
		$("#wpp-email-results").click(function() {
			$("#wpp-email-results-dialog").dialog("open");
		});
		$("#wpp-email-sending-close").buttonset();
	});



	/**************************************************************/
	/**  Hover function for charts                               **/
	/**************************************************************/
	var previousPoint = null;
	function showTooltip(x, y, contents) {
		jQuery('<div id="wpp-tooltip">' + contents + '</div>').css(
			{
				position: 'absolute',
				display: 'none',
				top: y + 5,
				left: x + 5,
				border: '1px solid #fdd',
				padding: '2px',
				'background-color': '#fee',
				opacity: 0.80
			}
		).appendTo("body").fadeIn(200);
	}



	/**************************************************************/
	/**  Plugin pie chart                                        **/
	/**************************************************************/
	var data_<?php echo $pie_chart_id; ?> = [
		<?php if (!empty($profile)): ?>
			<?php foreach ($profile->plugin_times as $k => $v) : ?>
				{ label: "<?php echo $k; ?>",  data: <?php echo $v; ?>},
			<?php endforeach; ?>
		<?php else : ?>
			{ label: 'No plugins', data: 1}
		<?php endif; ?>
	];
	jQuery(document).ready(function($) {
		$.plot($("#wpp-holder_<?php echo $pie_chart_id; ?>"), data_<?php echo $pie_chart_id; ?>,
		{
				series: {
					pie: { 
						show: true,
						combine: {
							threshold: .03 // 3% or less
						}
					}
				},
				grid: {
					hoverable: true,
					clickable: true
				},
				legend: {
					container: $("#wpp-legend_<?php echo $pie_chart_id; ?>")
				}
		});

		$("#wpp-holder_<?php echo $pie_chart_id; ?>").bind("plothover", function (event, pos, item) {
			if (item) {
				$("#wpp-tooltip").remove();
				showTooltip(pos.pageX, pos.pageY,
					item.series.label + "<br />" + Math.round(item.series.percent) + "%"
				);
			} else {
				$("#wpp-tooltip").remove();
			}
		});
	});



	/**************************************************************/
	/**  Runtime line chart data                                 **/
	/**************************************************************/
	var chart_<?php echo $runtime_chart_id; ?> = null;
	var data_<?php echo $runtime_chart_id; ?> = [
		{
			label: "WP Core time",
			data: [
			<?php foreach (array_values($url_stats) as $k => $v) : ?>
				[<?php echo $k+1; ?>,  <?php echo $v['core']; ?>],
			<?php endforeach; ?>
			]
		},
		{
			label: "Theme time",
			data: [
			<?php foreach (array_values($url_stats) as $k => $v) : ?>
				[<?php echo $k+1; ?>,  <?php echo $v['theme']; ?>],
			<?php endforeach; ?>
			]
		},
		{
			label: "Plugin time",
			data: [
			<?php foreach (array_values($url_stats) as $k => $v) : ?>
				[<?php echo $k+1; ?>,  <?php echo $v['plugins']; ?>],
			<?php endforeach; ?>
			]
		}
	];
	jQuery(document).ready(function($) {
		chart_<?php echo $runtime_chart_id; ?> = $.plot($("#wpp-holder_<?php echo $runtime_chart_id; ?>"), data_<?php echo $runtime_chart_id; ?>,
		{
				series: {
					lines: { show: true },
					points: { show: true },
				},
				grid: {
					hoverable: true,
					clickable: true
				},
				legend : {
					container: $("#wpp-legend_<?php echo $runtime_chart_id; ?>")
				},
				zoom: {
					interactive: true
				},
				pan: {
					interactive: true
				}
		});

		// zoom buttons
		$('<div class="button" style="float: left; position: relative; left: 440px; top: -290px;">-</div>').appendTo($("#wpp-holder_<?php echo $runtime_chart_id; ?>").parent()).click(function (e) {
			e.preventDefault();
			chart_<?php echo $runtime_chart_id; ?>.zoomOut();
		});
		$('<div class="button" style="float: left; position: relative; left: 440px; top: -290px;">+</div>').appendTo($("#wpp-holder_<?php echo $runtime_chart_id; ?>").parent()).click(function (e) {
			e.preventDefault();
			chart_<?php echo $runtime_chart_id; ?>.zoom();
		});

		$("#wpp-holder_<?php echo $runtime_chart_id; ?>").bind("plothover", function (event, pos, item) {
			if (item) {
				if (previousPoint != item.dataIndex) {
					previousPoint = item.dataIndex;

					$("#wpp-tooltip").remove();
					var x = item.datapoint[0].toFixed(2),
						y = item.datapoint[1].toFixed(2);

					url = _data[item["dataIndex"]]["url"];

					// Get rid of the domain
					url = url.replace(/http[s]?:\/\/<?php echo $domain; ?>(:\d+)?/, "");

					showTooltip(item.pageX, item.pageY,
								item.series.label + "<br />" +
								url + "<br />" +
								y + " seconds");
				}
			} else {
				$("#wpp-tooltip").remove();
				previousPoint = null;            
			}
		});
	});
	


	/**************************************************************/
	/**  Query line chart data                                   **/
	/**************************************************************/
	var chart_<?php echo $query_chart_id; ?> = null;
	var data_<?php echo $query_chart_id; ?> = [
		{
			label: "# of Queries",
			data: [
			<?php if (!empty($profile)): ?>
				<?php foreach (array_values($url_stats) as $k => $v) : ?>
					[<?php echo $k+1; ?>,  <?php echo $v['queries']; ?>],
				<?php endforeach; ?>
			<?php endif; ?>
			]
		}
	];
	jQuery(document).ready(function($) {
		chart_<?php echo $query_chart_id; ?> = $.plot($("#wpp-holder_<?php echo $query_chart_id; ?>"), data_<?php echo $query_chart_id; ?>,
		{
				series: {
					lines: { show: true },
					points: { show: true }
				},
				grid: {
					hoverable: true,
					clickable: true
				},
				legend : {
					container: $("#wpp-legend_<?php echo $query_chart_id; ?>")
				},
				zoom: {
					interactive: true
				},
				pan: {
					interactive: true
				}
		});

		// zoom buttons
		$('<div class="button" style="float: left; position: relative; left: 440px; top: -290px;">-</div>').appendTo($("#wpp-holder_<?php echo $query_chart_id; ?>").parent()).click(function (e) {
			e.preventDefault();
			chart_<?php echo $query_chart_id; ?>.zoomOut();
		});
		$('<div class="button" style="float: left; position: relative; left: 440px; top: -290px;">+</div>').appendTo($("#wpp-holder_<?php echo $query_chart_id; ?>").parent()).click(function (e) {
			e.preventDefault();
			chart_<?php echo $query_chart_id; ?>.zoom();
		});

		$("#wpp-holder_<?php echo $query_chart_id; ?>").bind("plothover", function (event, pos, item) {
			if (item) {
				if (previousPoint != item.dataIndex) {
					previousPoint = item.dataIndex;

					$("#wpp-tooltip").remove();
					var x = item.datapoint[0].toFixed(2),
						y = item.datapoint[1]; //.toFixed(2);

					url = _data[item["dataIndex"]]["url"];

					// Get rid of the domain
					url = url.replace(/http[s]?:\/\/<?php echo $domain; ?>(:\d+)?/, "");

					qword = (y == 1) ? "query" : "queries";
					showTooltip(item.pageX, item.pageY,
								item.series.label + "<br />" +
								url + "<br />" +
								y + " " + qword);
				}
			} else {
				$("#wpp-tooltip").remove();
				previousPoint = null;            
			}
		});
	});


	/**************************************************************/
	/**  Compnent bar chart data                                 **/
	/**************************************************************/
	var chart_<?php echo $component_breakdown_chart_id; ?> = null;
	var data_<?php echo $component_breakdown_chart_id; ?> = [
		{
			label: 'WP Core Time',
			data: [[0, <?php echo $profile->averages['core']; ?>]]
		},
		{
			label: 'Theme',
			data: [[1, <?php echo $profile->averages['theme']; ?>]]
		},
		<?php $i = 2; $other = 0; $threshold = .05 * $profile->averages['plugins']; ?>
		<?php foreach ($profile->plugin_times as $k => $v) : ?>
			<?php if ($v <= $threshold ) : ?>
				<?php $other += $v; ?>
			<?php else : ?>
			{
				label: '<?php echo $k; ?>',
				data: [[<?php echo $i++; ?>, <?php echo $v; ?>]],
			},
			<?php endif; ?>
		<?php endforeach; ?>
		{
			label: 'Other',
			data: [[<?php echo $i++; ?>, <?php echo $other; ?>]]
		}
	];

	jQuery(document).ready(function($) {
		chart_<?php echo $component_breakdown_chart_id; ?> = $.plot($("#wpp-holder_<?php echo $component_breakdown_chart_id; ?>"), data_<?php echo $component_breakdown_chart_id; ?>,
		{
				series: {
					bars: {
						show: true,
						barWidth: 0.9,
						align: 'center'
					},
					stack: false,
					lines: {
						show: false,
						steps: false,
					}
				},
				grid: {
					hoverable: true,
					clickable: true
				},
				xaxis: {
					show: false,
					ticks: [
						[0, 'WP Core Time'],
						[1, 'Theme'],
						<?php $i = 2; ?>
						<?php $i = 2; $other = 0; $threshold = .05 * $profile->averages['plugins']; ?>
						<?php foreach ($profile->plugin_times as $k => $v) : ?>
							<?php if ($v > $threshold) : ?>
								[<?php echo $i++ ?>, '<?php echo $k; ?>'],
							<?php endif; ?>
						<?php endforeach; ?>
						[<?php echo $i++; ?>, 'Other']
					],
				},
				legend : {
					container: $("#wpp-legend_<?php echo $component_breakdown_chart_id; ?>")
				},
				zoom: {
					interactive: true
				},
				pan: {
					interactive: true
				}
		});

		$("#wpp-holder_<?php echo $component_breakdown_chart_id; ?>").bind("plothover", function (event, pos, item) {
			if (item) {
				$("#wpp-tooltip").remove();
				showTooltip(pos.pageX, pos.pageY,
					item.series.label + "<br />" + Math.round(item.datapoint[1] * Math.pow(10, 4)) / Math.pow(10, 4) + " seconds"
				);
			} else {
				$("#wpp-tooltip").remove();
			}
		});

		// zoom buttons
		$('<div class="button" style="float: left; position: relative; left: 440px; top: -290px;">-</div>').appendTo($("#wpp-holder_<?php echo $component_breakdown_chart_id; ?>").parent()).click(function (e) {
			e.preventDefault();
			chart_<?php echo $component_breakdown_chart_id; ?>.zoomOut();
		});
		$('<div class="button" style="float: left; position: relative; left: 440px; top: -290px;">+</div>').appendTo($("#wpp-holder_<?php echo $component_breakdown_chart_id; ?>").parent()).click(function (e) {
			e.preventDefault();
			chart_<?php echo $component_breakdown_chart_id; ?>.zoom();
		});
	});

	/**************************************************************/
	/**  Runtime by component line chart data                    **/
	/**************************************************************/
	var chart_<?php echo $component_runtime_chart_id; ?> = null;
	var data_<?php echo $component_runtime_chart_id; ?> = [
		{
			label: "WP Core Time",
			data: [
			<?php if (!empty($profile)): ?>
				<?php foreach (array_values($url_stats) as $k => $v) : ?>
					[<?php echo $k+1; ?>,  <?php echo $v['core']; ?>],
				<?php endforeach; ?>
			<?php endif; ?>
			]
		},
		{
			label: "Theme",
			data: [
			<?php if (!empty($profile)): ?>
				<?php foreach (array_values($url_stats) as $k => $v) : ?>
					[<?php echo $k+1; ?>,  <?php echo $v['theme']; ?>],
				<?php endforeach; ?>
			<?php endif; ?>
			]
		},
		<?php if (!empty($profile) && !empty($profile->detected_plugins)) : ?>
			<?php foreach ($profile->detected_plugins as $plugin) : ?>
				{
					label: "<?php echo $plugin; ?>",
					data: [
					<?php foreach (array_values($url_stats) as $k => $v) : ?>
						[<?php echo $k+1; ?>,  <?php echo $v['breakdown'][$plugin]; ?>],
					<?php endforeach; ?>
					]
				},
			<?php endforeach; ?>
		<?php endif; ?>
	];
	jQuery(document).ready(function($) {
		chart_<?php echo $component_runtime_chart_id; ?> = $.plot($("#wpp-holder_<?php echo $component_runtime_chart_id; ?>"), data_<?php echo $component_runtime_chart_id; ?>,
		{
				series: {
					lines: { show: true },
					points: { show: true }
				},
				grid: {
					hoverable: true,
					clickable: true
				},
				legend : {
					container: $("#wpp-legend_<?php echo $component_runtime_chart_id; ?>")
				},
				zoom: {
					interactive: true
				},
				pan: {
					interactive: true
				}
		});

		$("#wpp-holder_<?php echo $component_runtime_chart_id; ?>").bind("plothover", function (event, pos, item) {
			if (item) {
				if (previousPoint != item.dataIndex) {
					previousPoint = item.dataIndex;

					$("#wpp-tooltip").remove();
					var x = item.datapoint[0].toFixed(2),
						y = item.datapoint[1]; //.toFixed(2);

					url = _data[item["dataIndex"]]["url"];

					// Get rid of the domain
					url = url.replace(/http[s]?:\/\/<?php echo $domain; ?>(:\d+)?/, "");

					showTooltip(item.pageX, item.pageY,
								item.series.label + "<br />" +
								url + "<br />" +
								y + " seconds");
				}
			} else {
				$("#wpp-tooltip").remove();
				previousPoint = null;            
			}
		});
		
		// zoom buttons
		$('<div class="button" style="float: left; position: relative; left: 440px; top: -290px;">-</div>').appendTo($("#wpp-holder_<?php echo $component_runtime_chart_id; ?>").parent()).click(function (e) {
			e.preventDefault();
			chart_<?php echo $component_runtime_chart_id; ?>.zoomOut();
		});
		$('<div class="button" style="float: left; position: relative; left: 440px; top: -290px;">+</div>').appendTo($("#wpp-holder_<?php echo $component_runtime_chart_id; ?>").parent()).click(function (e) {
			e.preventDefault();
			chart_<?php echo $component_runtime_chart_id; ?>.zoom();
		});
	});

</script>
<div id="wpp-tabs">
	<ul>
		<li><a href="#wpp-tabs-1">Runtime By Plugin</a></li>
		<li><a href="#wpp-tabs-5">Component Breakdown</a></li>
		<li><a href="#wpp-tabs-6">Component Runtime Timeline</a></li>
		<li><a href="#wpp-tabs-2">Summary Runtime Timeline</a></li>
		<li><a href="#wpp-tabs-3">Query Timeline</a></li>
		<li><a href="#wpp-tabs-4">Advanced Metrics</a></li>
	</ul>

	<!-- Plugin bar chart -->
	<div id="wpp-tabs-5">
		<h2>Component Breakdown</h2>
		<div class="wpp-plugin-graph">
			<table>
				<tr>
					<td rowspan="2">
						<div class="wpp-y-axis-label">
							<em class="wpp-em">Seconds</em>
						</div>
					</td>
					<td rowspan="2">
						<div class="wpp-line wpp-graph-holder" id="wpp-holder_<?php echo $component_breakdown_chart_id; ?>"></div>
					</td>
					<td>
						<h3>Legend</h3>
					</td>
				</tr>
				<tr>
					<td>
						<div class="wpp-custom-legend" id="wpp-legend_<?php echo $component_breakdown_chart_id; ?>"></div>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2">
						<div class="wpp-x-axis-label" style="top: -10px;">
							<em class="wpp-em">Component</em>
						</div>
					</td>
				</tr>
			</table>
		</div>		
	</div>
	
	<!-- Plugin pie chart div -->
	<div id="wpp-tabs-1">
		<h2>Runtime by Plugin</h2>
		<div class="wpp-plugin-graph">
			<table>
				<tr>
					<td rowspan="2">
						<div style="width: 370px;" class="wpp-line wpp-graph-holder" id="wpp-holder_<?php echo $pie_chart_id; ?>"></div>
					</td>
					<td>
						<h3>Legend</h3>
					</td>
				</tr>
				<tr>
					<td>
						<div style="width: 250px;" class="wpp-custom-legend" id="wpp-legend_<?php echo $pie_chart_id;?>"></div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<!-- Runtime line chart div -->
	<div id="wpp-tabs-2">
		<h2>Summary Runtime Timeline</h2>
		<div class="wpp-plugin-graph">
			<table>
				<tr>
					<td rowspan="2">
						<div class="wpp-y-axis-label">
							<em class="wpp-em">Seconds</em>
						</div>
					</td>
					<td rowspan="2">
						<div class="wpp-line wpp-graph-holder" id="wpp-holder_<?php echo $runtime_chart_id; ?>"></div>
					</td>
					<td>
						<h3>Legend</h3>
					</td>
				</tr>
				<tr>
					<td>
						<div class="wpp-custom-legend" id="wpp-legend_<?php echo $runtime_chart_id; ?>"></div>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2">
						<div class="wpp-x-axis-label">
							<em class="wpp-em">Visit</em>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<!-- Query line chart div -->
	<div id="wpp-tabs-3">
		<h2>Query Timeline</h2>
		<div class="wpp-plugin-graph">
			<table>
				<tr>
					<td rowspan="2">
						<div class="wpp-y-axis-label">
							<em class="wpp-em">Queries</em>
						</div>
					</td>
					<td rowspan="2">
						<div class="wpp-line wpp-graph-holder" id="wpp-holder_<?php echo $query_chart_id; ?>"></div>
					</td>
					<td>
						<h3>Legend</h3>
					</td>
				</tr>
				<tr>
					<td>
						<div class="wpp-custom-legend" id="wpp-legend_<?php echo $query_chart_id; ?>"></div>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2">
						<div class="wpp-x-axis-label">
							<em class="wpp-em">Visit</em>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<!-- Component runtime chart div -->
	<div id="wpp-tabs-6">
		<h2>Component Runtime Timeline</h2>
		<div class="wpp-plugin-graph">
			<table>
				<tr>
					<td rowspan="2">
						<div class="wpp-y-axis-label">
							<em class="wpp-em">Seconds</em>
						</div>
					</td>
					<td rowspan="2">
						<div class="wpp-line wpp-graph-holder" id="wpp-holder_<?php echo $component_runtime_chart_id; ?>"></div>
					</td>
					<td>
						<h3>Legend</h3>
					</td>
				</tr>
				<tr>
					<td>
						<div class="wpp-custom-legend" id="wpp-legend_<?php echo $component_runtime_chart_id; ?>"></div>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2">
						<div class="wpp-x-axis-label">
							<em class="wpp-em">Visit</em>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	
	<!-- Advanced data -->
	<div id="wpp-tabs-4">
		<div id="wpp-metrics-container">
			<div class="ui-widget-header" id="wpp-metrics-header" style="padding: 8px;">
				<strong>Advanced Metrics</strong>
			</div>
			<div>
				<table class="wpp-results-table" id="wpp-results-table" cellpadding="0" cellspacing="0" border="0">
					<tbody>
						<tr class="advanced">
							<td class="qtip-tip" title="How long the site took to load.  This is an observed measurement (start timing when the page was requested,
											stop timing when the page was delivered to the browser, calcuate the difference).  Lower is better.">
								<strong>Total Load Time: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['total']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The calculated total load time minus the profile overhead.  This is closer to your site's real-life load time.  Lower is better.">
								<strong>Site Load Time</small></em></strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['site']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr class="advanced">
							<td class="qtip-tip" title="The load time spent in the profiling code.  Since using the profiler will slow down your load time, it is important
											to know how much impact the profiler is having on your site.  This won't impact your site's real-life load time.">
								<strong>Profile Overhead: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['profile']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The load time spent in plugins.  Because of the way WordPress is built, a function call can be traced from a
											plugin through a theme through the core.  The profiler prioritizes plugin calls first, theme calls second, and
											core calls last.  Lower is better.">
								<strong>Plugin Load Time: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['plugins']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The load time spent in the theme.  Because of the way WordPress is built, a function call can be traced from a
											plugin through a theme through the core.  The profiler prioritizes plugin calls first, theme calls second, and
											core calls last.  Lower is better.">
								<strong>Theme Load Time: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['theme']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The load time spent in the WordPress core.  Because of the way WordPress is built, a function call can be traced from a
											plugin through a theme through the core.  The profiler prioritizes plugin calls first, theme calls second, and
											core calls last.  This will probably be constant.">
								<strong>Core Load Time: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['core']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr class="advanced">
							<td class="qtip-tip" title="This is the difference between the observed runtime (what actually happened) and expected runtime (adding up the plugin
											runtime, theme runtime, core runtime, and profiler overhead).  There are several reasons this margin of error can exist.
											Most likely, the profiler is missing microsends while it's doing math to add up the runtime it just observed.  Using a
											network clock to set the time (NTP) can also cause minute timing changes.  Ideally, this number should be zero, but
											there's nothing you can do to change it.  It will give you an idea of how accurate the other results are.">
								<strong>Margin of Error: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['drift']); ?> seconds <em class="wpp-em">avg.</em>
								<br />
								<em class="wpp-em">
									(<span class="qtip-tip" title="How long the site took to load.  This is an observed measurement (start timing when the page was requested,
											stop timing when the page was delivered to the browser, calcuate the difference)."><?php printf('%.4f', $profile->averages['observed']); ?> observed<span>,
									 <span class="qtip-tip" title="The expected site load time calculated by adding plugin load time + core load time + theme load time + profiler overhead."><?php printf('%.4f', $profile->averages['expected']); ?> expected</span>)
								</em>
							</td>
						</tr>
						<tr class="advanced">
							<td class="qtip-tip" title="The number of visits registered during the profiling session.  More visits will give a more accurate summary.">
								<strong>Visits: </strong>
							</td>
							<td>
								<?php echo number_format($profile->visits); ?>
							</td>
						</tr>
						<tr class="advanced">
							<td class="qtip-tip" title="The number of php function calls generated by a plugin.  Lower is better.">
								<strong>Number of Plugin Function Calls: </strong>
							</td>
							<td>
								<?php echo number_format($profile->averages['plugin_calls']); ?> calls <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The amount of RAM usage observed.  This is reporeted by memory_get_peak_usage().  Lower is better.">
								<strong>Memory Usage: </strong>
							</td>
							<td>
								<?php echo number_format($profile->averages['memory'] / 1024 / 1024, 2); ?> MB <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The count of queries sent to the database.  This reported by the WordPress function get_num_queries().  Lower is better.">
								<strong>MySQL Queries: </strong>
							</td>
							<td>
								<?php echo round($profile->averages['queries']); ?> queries <em class="wpp-em">avg.</em>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Email these results -->
	<div class="button" id="wpp-email-results" style="width: 155px; padding: 5px;">
		<img src="<?php echo plugins_url(); ?>/wp-profiler/css/mail.png" height="22" width="22" align="center" alt="Email these results" title="Email these results" />
		<a href="javascript:;">Email these results</a>
	</div>
	
	<!-- Email results dialog -->
	<div id="wpp-email-results-dialog" class="wpp-dialog">
		<div>
			Recipient:<br />
			<input type="text" id="wpp-email-results-to" style="width:95%;" size="35" value="<?php $user = wp_get_current_user(); echo $user->user_email; ?>" title="Enter the e-mail address where you would like to send these results" />
		</div>
		<br />
		<div>
			Subject:<br />
			<input type="text" id="wpp-email-results-subject" style="width:95%;" size="35" value="Performance Profile Results - <?php bloginfo('name'); ?>" title="Enter the e-mail subject" />
		</div>
		<br />
		<div>
			Message:<br />
			<textarea id="wpp-email-results-message" style="width: 95%; height: 100px;">Hello,

I profiled my WordPress site's performance using the Profile Plugin and I wanted to share the results with you.  Please take a look at the information below:</textarea>
		</div>
		<br />
		<div>
			Results: <em class="wpp-em">(system generated, do not edit)</em><br />
			<textarea disabled="disabled" id="wpp-email-results-results" style="width: 95%; height: 120px;"><?php 
			echo "WordPress Plugin Profile Report\n";
			echo "===========================================\n";
			echo "Report date: " . date('D M j, Y', $profile->report_date) . "\n";
			echo "Pages browsed: " . $profile->visits . "\n";
			echo 'Avg. load time: ' . sprintf('%.4f', $profile->averages['site']) . " sec\n";
			echo 'Number of plugins: ' . count($profile->detected_plugins) . " \n";
			echo 'Plugin impact: ' . sprintf('%.2f%%', $profile->averages['plugin_impact']) . " % of load time\n";
			echo 'Avg. plugin time: ' . sprintf('%.4f', $profile->averages['plugins']) . " sec\n";
			echo 'Avg. core time: ' . sprintf('%.4f', $profile->averages['core']) . " sec\n";
			echo 'Avg. theme time: ' . sprintf('%.4f', $profile->averages['theme']) . " sec\n";
			echo 'Avg. mem usage: ' . number_format($profile->averages['memory'] / 1024 / 1024, 2) . " MB\n";
			echo 'Avg. plugin calls: ' . number_format($profile->averages['plugin_calls']) . "\n";
			echo 'Avg. db queries : ' . sprintf('%.2f', $profile->averages['queries']) . "\n";
			echo 'Margin of error : ' . sprintf('%.4f', $profile->averages['drift']) . " sec\n";
			echo "\nPlugin list:\n";
			echo "===========================================\n";
			echo implode("\n", $profile->detected_plugins) . "\n";
			?></textarea>
		</div>
		<input type="hidden" id="wpp-email-results-scan" value="<?php echo basename($scan); ?>" />
	</div>
	
	<!-- Email sending dialog -->
	<div id="wpp-email-sending-dialog" class="wpp-dialog">
		<div id="wpp-email-sending-loading">
			<img src="<?php echo get_site_url() . '/wp-admin/images/loading.gif' ?>" height="16" width="16" title="Loading" alt="Loading" />
		</div>
		<div id="wpp-email-sending-error">
			There was a problem sending the e-mail: <span id="wpp-email-error"></span>
		</div>
		<div id="wpp-email-sending-success">
			Your report was sent successfully to <span id="wpp-email-success-recipient"></span>
		</div>
		<div id="wpp-email-sending-close">
			<input type="checkbox" id="wpp-email-sending-close-submit" checked="checked" /><label for="wpp-email-sending-close-submit">Done</label>
		</div>
	</div>
