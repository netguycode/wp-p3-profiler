<?php
	$url_stats = array();
	$domain = '';
	if (!empty($profile)) {
		$url_stats = $profile->get_stats_by_url();
		$domain = @parse_url($profile->report_url, PHP_URL_HOST);
	}
	$pie_chart_id     = substr(md5(uniqid()), -8);
	$runtime_chart_id = substr(md5(uniqid()), -8);
	$query_chart_id   = substr(md5(uniqid()), -8);
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
		$("#wpp-hide-glossary").trigger("click");
		$("#wpp-glossary-container").dblclick(function() {
			$("#wpp-hide-glossary").trigger("click");
		});
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
	/**  Plugin pie chart    a                                   **/
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
					item.series.label + "<br />" + Math.round(item.series.percent, 2) + "%"
				);
			} else {
				$("#wpp-tooltip").remove();
			}
		});
	});



	/**************************************************************/
	/**  Runtime line chart data                                 **/
	/**************************************************************/
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
		$.plot($("#wpp-holder_<?php echo $runtime_chart_id; ?>"), data_<?php echo $runtime_chart_id; ?>,
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
				}
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
					url = url.replace(/http[s]?:\/\/<?php echo $domain; ?>/, "");

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
		$.plot($("#wpp-holder_<?php echo $query_chart_id; ?>"), data_<?php echo $query_chart_id; ?>,
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
				}
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
					url = url.replace(/http[s]?:\/\/<?php echo $domain; ?>/, "");

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

</script>
<div id="wpp-tabs">
	<ul>
		<li><a href="#wpp-tabs-1">Runtime By Plugin</a></li>
		<li><a href="#wpp-tabs-2">Runtime Timeline</a></li>
		<li><a href="#wpp-tabs-3">Query Timeline</a></li>
		<li><a href="#wpp-tabs-4">Advanced Metrics</a></li>
	</ul>

	<!-- Plugin pie chart div -->
	<div id="wpp-tabs-1">
		<h2>Average Load Time by Plugin</h2>
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
		<h2>Runtime Timeline</h2>
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
							<td>
								<strong>Total Load Time: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['total']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td>
								<strong>Site Load Time</small></em></strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['site']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr class="advanced">
							<td>
								<strong>Profile Overhead: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['profile']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td>
								<strong>Plugin Load Time: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['plugins']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td>
								<strong>Theme Load Time: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['theme']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td>
								<strong>Core Load Time: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['core']); ?> seconds <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr class="advanced">
							<td>
								<strong>Margin of Error: </strong>
							</td>
							<td>
								<?php printf('%.4f', $profile->averages['drift']); ?> seconds <em class="wpp-em">avg.</em>
								<br />
								<em class="wpp-em">
									(<?php printf('%.4f', $profile->averages['observed']); ?> observed,
									 <?php printf('%.4f', $profile->averages['expected']); ?> expected)
								</em>
							</td>
						</tr>
						<tr class="advanced">
							<td>
								<strong>Visits: </strong>
							</td>
							<td>
								<?php echo number_format($profile->visits); ?>
							</td>
						</tr>
						<tr class="advanced">
							<td>
								<strong>Number of Plugin Function Calls: </strong>
							</td>
							<td>
								<?php echo number_format($profile->averages['plugin_calls']); ?> calls <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td>
								<strong>Memory Usage: </strong>
							</td>
							<td>
								<?php echo number_format($profile->averages['memory'] / 1024 / 1024, 2); ?> MB <em class="wpp-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td>
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
													stop timing when the page was delivered to the browser, calcuate the difference).
												</div>
											</td>
											<td width="400" rowspan="12" id="wpp-glossary-term-display">&nbsp;</td>
										</tr>
										<tr>
											<td class="term"><strong>Site Load Time</strong>
												<div id="site-load-time-definition" style="display: none;" class="definition">
													The calculated total load time minus the profile overhead.  This is closer to your site's real-life load time.
												</div>
											</td>
										</tr>
										<tr>
											<td class="term"><strong>Profile Overhead</strong>
												<div id="profile-overhead-definition" style="display: none;" class="definition">
													The load time spent in the profiling code.  Since using the profiler will slow down your load time, it is important
													to know how much impact the profiler is having on your site.
												</div>
											</td>
										</tr>
										<tr>
											<td class="term"><strong>Plugin Load Time</strong>
												<div id="plugin-load-time-definition" style="display: none;" class="definition">
													The load time spent in plugins.  Because of the way WordPress is built, a function call can be traced from a
													plugin through a theme through the core.  The profiler prioritizes plugin calls first, theme calls second, and
													core calls last.
												</div>
											</td>
										</tr>
										<tr>
											<td class="term"><strong>Theme Load Time</strong>
												<div id="theme-load-time-definition" style="display: none;" class="definition">
													The load time spent in the theme.  Because of the way WordPress is built, a function call can be traced from a
													plugin through a theme through the core.  The profiler prioritizes plugin calls first, theme calls second, and
													core calls last.
												</div>
											</td>
										</tr>
										<tr>
											<td class="term"><strong>Core Load Time</strong>
												<div id="core-load-time-definition" style="display: none;" class="definition">
													The load time spent in the WordPress core.  Because of the way WordPress is built, a function call can be traced from a
													plugin through a theme through the core.  The profiler prioritizes plugin calls first, theme calls second, and
													core calls last.
												</div>
											</td>
										</tr>
										<tr>
											<td class="term"><strong>Margin of Error</strong>
												<div id="drift-definition" style="display: none;" class="definition">
													This is the difference between the observed runtime (what actually happened) and expected runtime (adding up the plugin
													runtime, theme runtime, core runtime, and profiler overhead).  There are several reasons this offset can exist.  Most likely,
													the profiler is missing microsends while it's doing math to add up the runtime it just observed.  Ideally, you want
													this number to be as close to zero as possible, but there's nothing you can do to change it.  It will give you an idea of how
													accurate the other results are, though.
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
													The number of php function calls generated by a plugin.
												</div>
											</td>
										</tr>
										<tr>
											<td class="term"><strong>Memory Usage</strong>
												<div id="memory-usage-definition" style="display: none;" class="definition">
													The amount of RAM usage observed.  This is reporeted by <a href="http://php.net/memory_get_peak_usage" target="_blank">memory_get_peak_usage()</a>.
												</div>
											</td>
										</tr>
										<tr>
											<td class="term"><strong>MySQL Queries</strong>
												<div id="mysql-queries-definition" style="display: none;" class="definition">
													The count of queries sent to the database.  This reported by the WordPress function <a href="http://codex.wordpress.org/Function_Reference/get_num_queries" target="_new">get_num_queries()</a>.
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
</div>