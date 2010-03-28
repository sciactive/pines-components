<?php
/**
 * Shows customer stations.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Customer Stations';
?>
<style type="text/css">
	/* <![CDATA[ */
	.station_layout {
		position: relative;
	}
	.station_layout .station_layout_bg {
		float: left;
		width: 100%;
		height: auto;
	}
	.station_layout .station {
		position: absolute;
		background-image: none;
	}
	.station_layout .station .name {
		display: block;
		float: left;
		font: normal small sans-serif;
	}
	.station_layout .station .points_remain {
		display: block;
		float: right;
		margin: 2px;
		font: bold small sans-serif;
	}
	.station_layout .station .station_id {
		display: block;
		margin: 5px;
		font: bold large sans-serif;
	}
	.station_layout .station.filled .station_id {
		float: left;
		margin: 2px;
		font: normal small sans-serif;
	}
	.station_layout .station.filled span.ui-button-text {
		padding: 0.4em 0.2em;
	}

	/* Status Dependent Styles */
	.station_layout .station.ok {
		background-color: green;
		color: white;
	}
	.station_layout .station.warning {
		background-color: yellow;
		color: black;
	}
	.station_layout .station.critical {
		background-color: red;
		color: white;
	}
	.station_layout.warning {
		background-color: goldenrod;
	}
	.station_layout.critical {
		background-color: crimson;
	}

	.customer_actions {
		text-align: center;
	}
	.customer_actions button {
		width: 120px;
		height: 20px;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		var station_layout = $(".station_layout");
		var station_floor = $(".station_layout .station_floor");
		
		var stations = {
			"1": {"left": "5.9178743961352656%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"2a": {"left": "18.115942028985507%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"2b": {"left": "18.115942028985507%", "top": "15.749235474006115%", "width": "7.85024154589372%", "height": "12.385321100917432%"},
			"3": {"left": "28.6231884057971%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"4a": {"left": "38.88888888888889%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"4b": {"left": "38.88888888888889%", "top": "15.749235474006115%", "width": "7.85024154589372%", "height": "12.385321100917432%"},
			"5": {"left": "49.27536231884058%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"6a": {"left": "59.78260869565217%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"6b": {"left": "59.78260869565217%", "top": "15.749235474006115%", "width": "7.85024154589372%", "height": "12.385321100917432%"},
			"7": {"left": "70.04830917874396%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},
			"8": {"left": "80.31400966183575%", "top": "1.529051987767584%", "width": "7.85024154589372%", "height": "12.538226299694188%"},

			"9a": {"left": "84.54106280193237%", "top": "16.36085626911315%", "width": "15.217391304347827%", "height": "8.409785932721713%"},
			"9b": {"left": "84.54106280193237%", "top": "24.92354740061162%", "width": "15.217391304347827%", "height": "8.256880733944955%"},
			"9c": {"left": "84.54106280193237%", "top": "33.33333333333333%", "width": "15.217391304347827%", "height": "8.256880733944955%"},
			"9d": {"left": "84.54106280193237%", "top": "41.743119266055045%", "width": "15.217391304347827%", "height": "8.256880733944955%"},

			"10a": {"left": "84.54106280193237%", "top": "51.52905198776758%", "width": "15.217391304347827%", "height": "8.409785932721713%"},
			"10b": {"left": "84.54106280193237%", "top": "60.09174311926605%", "width": "15.217391304347827%", "height": "8.256880733944955%"},
			"10c": {"left": "84.54106280193237%", "top": "68.50152905198776%", "width": "15.217391304347827%", "height": "8.256880733944955%"},
			"10d": {"left": "84.54106280193237%", "top": "76.91131498470948%", "width": "15.217391304347827%", "height": "8.256880733944955%"},

			"11": {"left": "80.55555555555556%", "top": "87.46177370030581%", "width": "7.729468599033816%", "height": "11.467889908256881%"},
			"12": {"left": "70.28985507246377%", "top": "87.46177370030581%", "width": "7.729468599033816%", "height": "11.467889908256881%"},
			"13a": {"left": "59.90338164251208%", "top": "87.46177370030581%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			"13b": {"left": "59.90338164251208%", "top": "73.70540140601108%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			"14": {"left": "49.39613526570048%", "top": "87.46177370030581%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			"15a": {"left": "39.1304347826087%", "top": "87.46177370030581%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			"15b": {"left": "39.1304347826087%", "top": "73.70540140601108%", "width": "7.85024154589372%", "height": "11.467889908256881%"},
			"16": {"left": "5.9178743961352656%", "top": "85.93272171253823%", "width": "7.85024154589372%", "height": "12.996941896024464%"},

			"17": {"left": "0.36231884057971015%", "top": "74.6177370030581%", "width": "10.144927536231885%", "height": "9.938837920489296%"},
			"18": {"left": "0.36231884057971015%", "top": "62.84403669724771%", "width": "10.144927536231885%", "height": "9.785932721712538%"},
			"19": {"left": "0.36231884057971015%", "top": "28.287461773700306%", "width": "10.144927536231885%", "height": "10.397553516819572%"},
			"20": {"left": "0.36231884057971015%", "top": "15.749235474006115%", "width": "10.144927536231885%", "height": "10.397553516819572%"}
		};
		
		$.each(stations, function(station_id, station){
			station.element = $("<div />", {
				"class": "station",
				"css": {
					"left": station.left,
					"top": station.top,
					"width": station.width,
					"height": station.height
				},
				"html": $("<span />", {
					"class": "station_id",
					"html": station_id
				}),
				"click": function(){
					if (station.customer)
						station.customer_action();
					else
						station.pick_customer();
				}
			})
			.button()
			.appendTo(station_floor);
			/*
			.draggable({
				"stop": function(){
					$("#position").html(
						"left: "+$(this).position().left/$(this).parent().parent().width()+"<br />"+
						"top: "+$(this).position().top/$(this).parent().parent().height()+"<br />"+
						"width: "+$(this).width()/$(this).parent().parent().width()+"<br />"+
						"height: "+$(this).height()/$(this).parent().parent().height()
					);
				}
			})
			.resizable({
				"stop": function(){
					$("#position").html(
						"left: "+$(this).position().left/$(this).parent().parent().width()+"<br />"+
						"top: "+$(this).position().top/$(this).parent().parent().height()+"<br />"+
						"width: "+$(this).width()/$(this).parent().parent().width()+"<br />"+
						"height: "+$(this).height()/$(this).parent().parent().height()
					);
				}
			});
			*/

			station.pick_customer = function (){
				// A dialog for logging a customer into a station.
				$("#com_customertimer_dialogs > .customer_search").clone()
				.attr("title", "Station "+station_id+": Choose a Customer")
				.find("table.customer_table")
				.find("tbody tr").remove().end()
				.pgrid({
					pgrid_hidden_cols: [3, 4, 5, 6, 7, 8, 9, 13],
					pgrid_count: false,
					pgrid_multi_select: false,
					pgrid_perpage: 5,
					pgrid_filtering: false,
					pgrid_view_height: "200px"
				})
				.end()
				.find("button.search_button").click(function(){
					// Search for customers.
					var loader;
					var customer_table = $(this).closest(".customer_search").find("table.customer_table");
					var customer_search = $(this).closest(".customer_search").find("input[name=customer_search]");
					$.ajax({
						url: "<?php echo pines_url("com_customer", "customersearch"); ?>",
						type: "POST",
						dataType: "json",
						data: {"q": customer_search.val()},
						beforeSend: function(){
							loader = pines.alert('Searching for customers...', 'Customer Search', 'icon picon_16x16_animations_throbber', {pnotify_hide: false, pnotify_history: false});
							customer_table.pgrid_get_all_rows().pgrid_delete();
						},
						complete: function(){
							loader.pnotify_remove();
						},
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured while trying to find customers:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (!data) {
								alert("No customers were found that matched the query.");
								return;
							}
							customer_table.pgrid_add(data);
						}
					});
				})
				.end()
				.dialog({
					"width": "500px",
					"modal": true,
					"buttons": {
						"Login": function(){
							// Add the selected customer.
							var customer = $(this).find("table.customer_table").pgrid_get_selected_rows().pgrid_export_rows()[0];
							if (customer.key) {
								$.ajax({
									url: "<?php echo pines_url('com_customertimer', 'login_json'); ?>",
									type: "POST",
									data: {"id": customer.key, "station": station_id},
									dataType: "json",
									error: function(XMLHttpRequest, textStatus){
										pines.error("An error occured while trying to log the user in:\n"+XMLHttpRequest.status+": "+textStatus);
									},
									success: function(data){
										if (!data) {
											pines.error("The user couldn't be logged in.");
										} else {
											alert("The user has been logged in.");
											update_status();
										}
									}
								});
								$(this).dialog("close").remove();
							} else {
								alert("Please select a customer.");
							}
						}
					}
				})
				.find("input[name=customer_search]")
				.focus()
				.keypress(function(event){
					// Click search when the user presses enter.
					if (event.keyCode == 13)
						$(this).closest(".customer_search").find("button.search_button").click();
				});
			};
			station.customer_action = function (){
				// A dialog for managing a customer in a station.
				var login_time = new Date(station.customer.login_time * 1000);
				$("#com_customertimer_dialogs > .customer_action").clone()
				.attr("title", "Station "+station_id+": "+station.customer.name)
				.find(".customer_id .value").html(station.customer.guid).end()
				.find(".login_time .value").html(login_time.toLocaleString()).end()
				.find(".points .value").html(station.customer.points).end()
				.find(".ses_minutes .value").html(station.customer.ses_minutes).end()
				.find(".ses_points .value").html(station.customer.ses_points).end()
				.find(".points_remain .value").html(station.customer.points_remain).end()
				.find(".status .value").html(
					station.customer.points_remain < 0 ?
						"Overdrawn" :
						station.customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_critical; ?> ?
							"Critical" :
							station.customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_warning; ?> ?
								"Warning" :
								"OK"
				).end()
				.dialog({
					"width": "450px",
					"modal": true,
					"buttons": {
						"Logout": function(){
							$.ajax({
								url: "<?php echo pines_url('com_customertimer', 'logout_json'); ?>",
								type: "POST",
								data: {"id": station.customer.guid},
								dataType: "json",
								error: function(XMLHttpRequest, textStatus){
									pines.error("An error occured while trying to log the user out:\n"+XMLHttpRequest.status+": "+textStatus);
								},
								success: function(data){
									if (!data) {
										pines.error("The user couldn't be logged out.");
									} else {
										alert("The user has been logged out.");
										update_status();
									}
								}
							});
							$(this).dialog("close");
						},
						"Edit Customer": function(){
							pines.get("<?php echo pines_url('com_customer', 'editcustomer'); ?>", {
								"id": station.customer.guid
							});
						},
						"Purchase Minutes": function(){
							pines.post("<?php echo pines_url('com_customertimer', 'newsale'); ?>", {
								"customer": station.customer.guid
							});
						}
					}
				});
			};
			station.insert_customer = function (customer){
				// Insert a customer into the station.
				station.customer = customer;
				station.element.addClass("filled").children("span.ui-button-text").append($("<span />", {
					"class": "points_remain"
				})).append($("<span />", {
					"class": "name"
				}));
				station.update_customer(customer);
			};
			station.check_customer = function (customer){
				// Check if a customer is in the station.
				if (!station.customer)
					return false;
				return (customer.guid == station.customer.guid);
			};
			station.update_customer = function (customer){
				// Update the customer's info.
				station.element.find(".name").html(customer.name);
				station.element.find(".points_remain").html(customer.points_remain);
				if (customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_critical; ?>) {
					station.element.removeClass("ok").removeClass("warning").addClass("critical");
					worst_status = "critical";
				} else if (customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_warning; ?>) {
					station.element.removeClass("ok").addClass("warning").removeClass("critical");
					if (worst_status != "critical")
						worst_status = "warning";
				} else {
					station.element.addClass("ok").removeClass("warning").removeClass("critical");
				}
				if (customer.points_remain <= 0)
					station.element.effect("pulsate");
			};
			station.remove_customer = function (){
				// Remove the customer from the station.
				station.customer = null;
				station.element.children("span.ui-button-text").children(".name").remove();
				station.element.children("span.ui-button-text").children(".points_remain").remove();
				station.element.removeClass("filled").removeClass("ok").removeClass("warning").removeClass("critical");
			};
		});

		var worst_status;
		var timer;
		function update_status() {
			// Grab the status for all customers.
			$.ajax({
				url: "<?php echo pines_url('com_customertimer', 'status_json'); ?>",
				type: "GET",
				dataType: "json",
				complete: function(){
					window.clearTimeout(timer);
					timer = setTimeout(update_status, 20000);
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to refresh the status:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					worst_status = "ok";
					$.each(stations, function(station_id, station){
						var cur_customer = false;
						$.each(data, function(){
							if (station_id == this.station)
								cur_customer = this;
						});
						if (!cur_customer)
							return station.remove_customer();
						if (station.check_customer(cur_customer)) {
							station.update_customer(cur_customer);
						} else {
							station.remove_customer();
							station.insert_customer(cur_customer);
						}
					});
					if (worst_status == "critical")
						station_layout.removeClass("warning").addClass("critical");
					else if (worst_status == "warning")
						station_layout.addClass("warning").removeClass("critical");
					else
						station_layout.removeClass("warning").removeClass("critical");
				}
			});
		}

		update_status();
	});
	// ]]>
</script>
<div class="station_layout">
	<img src="<?php echo $pines->config->rela_location; ?>components/com_customertimer/includes/station_floor.png" class="station_layout_bg" alt="Station Layout" />
	<div class="station_floor"></div>
	<br class="spacer" />
</div>
<div id="com_customertimer_dialogs" style="display: none;">
	<div class="customer_action" title="">
		<div class="customer_id">Customer ID: <span class="value"></span></div>
		<div class="login_time">Login Time: <span class="value"></span></div>
		<div class="points">Points in Account: <span class="value"></span></div>
		<div class="ses_minutes">Minutes this Session: <span class="value"></span></div>
		<div class="ses_points">Points Used: <span class="value"></span></div>
		<div class="points_remain">Points Left: <span class="value"></span></div>
		<br />
		<div class="status">Status: <span class="value"></span></div>
	</div>
	<div class="customer_search" title="Choose a Customer">
		<div class="pform">
			<div class="element">
				<span class="label">Customer</span>
				<span class="note">Enter part of a name, company, email, or phone # to search.</span>
				<div class="group">
					<input class="field ui-widget-content" type="text" name="customer_search" size="24" />
					<button type="button" class="search_button"><span class="picon_16x16_actions_system-search" style="padding-left: 16px; background-repeat: no-repeat;">Search</span></button>
				</div>
			</div>
		</div>
		<table class="customer_table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Company</th>
					<th>Job Title</th>
					<th>Address 1</th>
					<th>Address 2</th>
					<th>City</th>
					<th>State</th>
					<th>Zip</th>
					<th>Home Phone</th>
					<th>Work Phone</th>
					<th>Cell Phone</th>
					<th>Fax</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
				</tr>
			</tbody>
		</table>
		<br class="spacer" />
	</div>
</div>
<!--
<div id="position"></div>
-->