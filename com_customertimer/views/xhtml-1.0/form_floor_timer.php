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
$pines->com_customer->load_customer_select();
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_station_layout {
		position: relative;
	}
	#p_muid_station_layout .layout_bg {
		float: left;
		width: 98%;
		height: auto;
	}
	#p_muid_station_layout .station {
		position: absolute;
		background-image: none;
	}
	#p_muid_station_layout .station .name {
		display: block;
		float: left;
		font: normal small sans-serif;
	}
	#p_muid_station_layout .station .points_remain {
		display: block;
		float: right;
		margin: 2px;
		font: bold small sans-serif;
	}
	#p_muid_station_layout .station .station_id {
		display: block;
		margin: 5px;
		font: bold large sans-serif;
	}
	#p_muid_station_layout .station.filled .station_id {
		float: left;
		margin: 2px;
		font: normal small sans-serif;
	}
	#p_muid_station_layout .station.filled span.ui-button-text {
		padding: 0.4em 0.2em;
	}

	/* Status Dependent Styles */
	#p_muid_station_layout .station.ok {
		background-color: green;
		color: white;
	}
	#p_muid_station_layout .station.warning {
		background-color: yellow;
		color: black;
	}
	#p_muid_station_layout .station.critical {
		background-color: red;
		color: white;
	}
	#p_muid_station_layout .station.critical.pulse {
		background-color: pink;
		color: black;
	}
	#p_muid_station_layout.warning {
		background-color: gold;
	}
	#p_muid_station_layout.critical {
		background-color: crimson;
	}

	/* Customer Action Dialog */
	#p_muid_customer_action div div {
		margin-bottom: .2em;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var station_layout = $("#p_muid_station_layout");
		var station_floor = $("#p_muid_station_layout .station_floor");
		var floor_id = "<?php echo $this->entity->guid; ?>";
		var sel_station_id;
		var sel_station;

		var stations = JSON.parse("<?php echo addslashes(json_encode($this->entity->stations)); ?>");

		$("#p_muid_customer_search").dialog({
			"width": "500px",
			"modal": true,
			"autoOpen": false,
			"buttons": {
				"Login": function(){
					// Add the selected customer.
					var dialog = $(this);
					var customer = parseInt($("#p_muid_customer").val().replace(/\D/g, ""));
					if (!isNaN(customer) && customer != 0) {
						$.ajax({
							url: "<?php echo pines_url('com_customertimer', 'login_json'); ?>",
							type: "POST",
							data: {"id": customer, "floor": floor_id, "station": sel_station_id},
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
						dialog.dialog("close");
					} else {
						alert("Please select a customer.");
					}
				}
			}
		});

		$("#p_muid_customer_action").dialog({
			"width": "450px",
			"modal": true,
			"autoOpen": false,
			"buttons": {
				"Logout": function(){
					$.ajax({
						url: "<?php echo pines_url('com_customertimer', 'logout_json'); ?>",
						type: "POST",
						data: {"id": sel_station.customer.guid, "floor": floor_id, "station": sel_station_id},
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
					pines.get("<?php echo pines_url('com_customer', 'customer/edit'); ?>", {
						"id": sel_station.customer.guid
					});
				},
				"Purchase Minutes": function(){
					pines.post("<?php echo pines_url('com_customertimer', 'newsale'); ?>", {
						"customer": sel_station.customer.guid
					});
				}
			}
		});

		$("#p_muid_customer").customerselect();

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

			station.pick_customer = function(){
				// A dialog for logging a customer into a station.
				sel_station_id = station_id;
				$("#p_muid_customer_search")
				.dialog("option", "title", "Station "+station_id+": Choose a Customer")
				.dialog("open");
				$("#p_muid_customer").val("").focus();
			};
			station.customer_action = function(){
				// A dialog for managing a customer in a station.
				sel_station_id = station_id;
				sel_station = station;
				var login_time = new Date(station.customer.login_time * 1000);
				$("#p_muid_customer_action")
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
				.dialog("option", "title", "Station "+station_id+": "+station.customer.name)
				.dialog("open");
			};
			station.insert_customer = function(customer){
				// Insert a customer into the station.
				station.element.addClass("filled").children("span.ui-button-text").append($("<span />", {
					"class": "points_remain"
				})).append($("<span />", {
					"class": "name"
				}));
				station.update_customer(customer);
			};
			station.check_customer = function(customer){
				// Check if a customer is in the station.
				if (!station.customer)
					return false;
				return (customer.guid == station.customer.guid);
			};
			station.update_customer = function(customer){
				// Update the customer's info.
				station.customer = customer;
				station.element.find(".name").html(customer.name);
				station.element.find(".points_remain").html(customer.points_remain);
				if (customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_critical; ?>) {
					if (station.element.hasClass("ok"))
						station.element.removeClass("ok");
					if (station.element.hasClass("warning"))
						station.element.removeClass("warning");
					if (!station.element.hasClass("critical"))
						station.element.addClass("critical");
					worst_status = "critical";
				} else if (customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_warning; ?>) {
					if (station.element.hasClass("ok"))
						station.element.removeClass("ok");
					if (!station.element.hasClass("warning"))
						station.element.addClass("warning");
					if (station.element.hasClass("critical"))
						station.element.removeClass("critical");
					if (worst_status != "critical")
						worst_status = "warning";
				} else {
					if (!station.element.hasClass("ok"))
						station.element.addClass("ok");
					if (station.element.hasClass("warning"))
						station.element.removeClass("warning");
					if (station.element.hasClass("critical"))
						station.element.removeClass("critical");
				}
				if (customer.points_remain <= 0)
					station.start_pulsing();
				else
					station.stop_pulsing();
			};
			station.remove_customer = function(){
				// Remove the customer from the station.
				station.customer = null;
				station.element.children("span.ui-button-text").children(".name").remove();
				station.element.children("span.ui-button-text").children(".points_remain").remove();
				station.element.removeClass("filled").removeClass("ok").removeClass("warning").removeClass("critical");
			};
			station.start_pulsing = function(){
				if (station.pulsing)
					return;
				station.pulsing = true;
				var pulse = function(){
					station.element.toggleClass("pulse");
				};
				station.pulse_timer = setInterval(pulse, 1000);
			};
			station.stop_pulsing = function(){
				if (!station.pulsing)
					return;
				station.pulsing = false;
				window.clearInterval(station.pulse_timer);
				station.element.removeClass("pulse");
			};
		});

		var worst_status;
		var updating = false;
		var update_status = function(){
			if (updating)
				return;
			updating = true;
			// Grab the status for all customers.
			$.ajax({
				url: "<?php echo pines_url('com_customertimer', 'status_json'); ?>",
				type: "GET",
				data: {"floor": floor_id},
				dataType: "json",
				complete: function(){
					updating = false;
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
						if (!cur_customer) {
							if (station.customer)
								station.remove_customer();
							return;
						}
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
		};
		update_status();
		setInterval(update_status, 20000);
	});
	// ]]>
</script>
<div id="p_muid_station_layout">
	<img class="layout_bg" src="<?php echo $this->entity->background; ?>" alt="Station Layout" />
	<div class="station_floor"></div>
	<br style="clear: both; height: 1px;" />
</div>
<div id="p_muid_customer_action" style="display: none;">
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
<div id="p_muid_customer_search" style="display: none;" title="Choose a Customer">
	<div class="pf-form">
		<div class="pf-element">
			<span class="pf-label">Customer</span>
			<span class="pf-note">Enter part of a name, company, email, or phone # to search.</span>
			<div class="pf-group">
				<input class="pf-field ui-widget-content" type="text" id="p_muid_customer" name="customer" size="24" />
			</div>
		</div>
	</div>
	<br style="clear: both; height: 1px;" />
</div>