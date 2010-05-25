<?php
/**
 * Provides a form for the user to manager customers on a floor.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Floor Timer ['.htmlentities($this->entity->name).']';
$pines->com_pgrid->load();
?>
<style type="text/css">
	/* <![CDATA[ */
	.station_layout {
		position: relative;
	}
	.station_layout .station_layout_bg {
		float: left;
		width: 98%;
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
	.station_layout .station.critical.pulse {
		background-color: pink;
		color: black;
	}
	.station_layout.warning {
		background-color: gold;
	}
	.station_layout.critical {
		background-color: crimson;
	}

	/* Customer Action Dialog */
	.customer_action div div {
		margin-bottom: .2em;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var station_layout = $(".station_layout");
		var station_floor = $(".station_layout .station_floor");
		var floor_id = "<?php echo $this->entity->guid; ?>";

		var stations = JSON.parse("<?php echo addslashes(json_encode($this->entity->stations)); ?>");

		$.each(stations, function(station_id, station){
			station.element = $("<div />", {
				"class": "station",
				"css": {
					"left": (station.left*100)+"%",
					"top": (station.top*100)+"%",
					"width": (station.width*100)+"%",
					"height": (station.height*100)+"%"
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
					pgrid_view_height: "200px",
					pgrid_double_click: function(){
						// Calling the function using call() allows it to use "this".
						var dialog = $(this).closest(".customer_search");
						dialog.dialog('option', 'buttons').Login.call(dialog);
					}
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
                            loader = $.pnotify({
								pnotify_title: 'Customer Search',
								pnotify_text: 'Searching for customers...',
								pnotify_notice_icon: 'picon picon_16x16_throbber',
								pnotify_nonblock: true,
								pnotify_hide: false,
								pnotify_history: false
							});
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
							var dialog = $(this);
							var customer = dialog.find("table.customer_table").pgrid_get_selected_rows().pgrid_export_rows()[0];
							if (customer.key) {
								$.ajax({
									url: "<?php echo pines_url('com_customertimer', 'login_json'); ?>",
									type: "POST",
									data: {"id": customer.key, "floor": floor_id, "station": station_id},
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
								dialog.dialog("close").remove();
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
				.find(".other_minutes .value").html(station.customer.other_minutes).end()
				.find(".other_points .value").html(station.customer.other_points).end()
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
								data: {"id": station.customer.guid, "floor": floor_id, "station": station_id},
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
				station.customer = customer;
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
					station.start_pulsing();
				else
					station.stop_pulsing();
			};
			station.remove_customer = function (){
				// Remove the customer from the station.
				station.customer = null;
				station.element.children("span.ui-button-text").children(".name").remove();
				station.element.children("span.ui-button-text").children(".points_remain").remove();
				station.element.removeClass("filled").removeClass("ok").removeClass("warning").removeClass("critical");
			};
			station.start_pulsing = function (){
				if (station.pulsing)
					return;
				station.pulsing = true;
				var pulse = function(){
					station.element.toggleClass("pulse");
				};
				station.pulse_timer = setInterval(pulse, 1000);
			};
			station.stop_pulsing = function (){
				if (!station.pulsing)
					return;
				station.pulsing = false;
				window.clearInterval(station.pulse_timer);
				station.element.removeClass("pulse");
			};
		});

		var worst_status;
		var timer;
		function update_status() {
			// Grab the status for all customers.
			$.ajax({
				url: "<?php echo pines_url('com_customertimer', 'status_json'); ?>",
				type: "GET",
				data: {"floor": floor_id},
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
	<img class="station_layout_bg" src="<?php echo $this->entity->background; ?>" alt="Station Layout" />
	<div class="station_floor"></div>
	<br style="clear: both; height: 1px;" />
</div>
<div id="com_customertimer_dialogs" style="display: none;">
	<div class="customer_action" title="">
		<div style="float: left;">
			<div class="customer_id">Customer ID:</div>
			<div class="login_time">Login Time:</div>
			<div class="points">Points in Account:</div>
			<div class="ses_minutes">Minutes this Session:</div>
			<div class="ses_points">Points this Session:</div>
			<div class="other_minutes">Minutes in other Sessions:</div>
			<div class="other_points">Points in other Sessions:</div>
			<div class="points_remain">Points Left:</div>
			<br />
			<div class="status">Status:</div>
		</div>
		<div style="float: left; margin-left: 1em;">
			<div class="customer_id"><span class="value"></span></div>
			<div class="login_time"><span class="value"></span></div>
			<div class="points"><span class="value"></span></div>
			<div class="ses_minutes"><span class="value"></span></div>
			<div class="ses_points"><span class="value"></span></div>
			<div class="other_minutes"><span class="value"></span></div>
			<div class="other_points"><span class="value"></span></div>
			<div class="points_remain"><span class="value"></span></div>
			<br />
			<div class="status"><span class="value"></span></div>
		</div>
	</div>
	<div class="customer_search" title="Choose a Customer">
		<div class="pf-form">
			<div class="pf-element">
				<span class="pf-label">Customer</span>
				<span class="pf-note">Enter part of a name, company, email, or phone # to search.</span>
				<div class="pf-group">
					<input class="pf-field ui-widget-content" type="text" name="customer_search" size="24" />
					<button class="pf-field ui-state-default ui-corner-all search_button" type="button"><span class="picon picon_16x16_system-search" style="padding-left: 16px; background-repeat: no-repeat;">Search</span></button>
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
		<br style="clear: both; height: 1px;" />
	</div>
</div>