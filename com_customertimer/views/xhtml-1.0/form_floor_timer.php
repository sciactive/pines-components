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
	#p_muid_layout_bg {
		float: left;
		width: 98%;
		height: auto;
	}
	#p_muid_station_floor .station {
		position: absolute;
		background-image: none;
	}
	#p_muid_station_floor .station .name {
		display: block;
		float: left;
		font: normal small sans-serif;
	}
	#p_muid_station_floor .station .points_remain {
		display: block;
		float: right;
		margin: 2px;
		font: bold small sans-serif;
	}
	#p_muid_station_floor .station .station_id {
		display: block;
		margin: 5px;
		font: bold large sans-serif;
	}
	#p_muid_station_floor .station.filled .station_id {
		float: left;
		margin: 2px;
		font: normal small sans-serif;
	}
	#p_muid_station_floor .station.filled span.ui-button-text {
		padding: 0.4em 0.2em;
	}

	/* Status Dependent Styles */
	#p_muid_station_floor .station.ok {
		background-color: green;
		color: white;
	}
	#p_muid_station_floor .station.warning {
		background-color: yellow;
		color: black;
	}
	#p_muid_station_floor .station.critical {
		background-color: red;
		color: white;
	}
	#p_muid_station_floor .station.critical.pulse {
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
		var station_floor = $("#p_muid_station_floor");
		var floor_id = "<?php echo $this->entity->guid; ?>";
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
							data: {"id": customer, "floor": floor_id, "station": sel_station.id},
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
						data: {"id": sel_station.customer.guid, "floor": floor_id, "station": sel_station.id},
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

		// Station actions.
		var pick_customer = function(station){
			// A dialog for logging a customer into a station.
			sel_station = station;
			$("#p_muid_customer_search")
			.dialog("option", "title", "Station "+station.id+": Choose a Customer")
			.dialog("open");
			$("#p_muid_customer").val("").focus();
		};
		var customer_action = function(station){
			// A dialog for managing a customer in a station.
			sel_station = station;
			var login_time = new Date(station.customer.login_time * 1000);
			$("#p_muid_customer_action_customer_id").html(station.customer.guid);
			$("#p_muid_customer_action_login_time").html(login_time.toLocaleString());
			$("#p_muid_customer_action_points").html(station.customer.points);
			$("#p_muid_customer_action_ses_minutes").html(station.customer.ses_minutes);
			$("#p_muid_customer_action_ses_points").html(station.customer.ses_points);
			$("#p_muid_customer_action_other_minutes").html(station.customer.other_minutes);
			$("#p_muid_customer_action_other_points").html(station.customer.other_points);
			$("#p_muid_customer_action_points_remain").html(station.customer.points_remain);
			$("#p_muid_customer_action_status").html(
				station.customer.points_remain < 0 ?
					"Overdrawn" :
					station.customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_critical; ?> ?
						"Critical" :
						station.customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_warning; ?> ?
							"Warning" :
							"OK"
			);
			$("#p_muid_customer_action")
			.dialog("option", "title", "Station "+station.id+": "+station.customer.name)
			.dialog("open");
		};
		var insert_customer = function(station, customer){
			// Insert a customer into the station.
			station.element.addClass("filled").children("span.ui-button-text").append($("<span />", {
				"class": "points_remain"
			})).append($("<span />", {
				"class": "name"
			}));
			update_customer(station, customer);
		};
		var check_customer = function(station, customer){
			// Check if a customer is in the station.
			if (!station.customer)
				return false;
			return (customer.guid == station.customer.guid);
		};
		var update_customer = function(station, customer){
			// Update the customer's info.
			station.customer = customer;
			$(".name", station.element).html(customer.name);
			$(".points_remain", station.element).html(customer.points_remain);
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
				start_pulsing(station);
			else
				stop_pulsing(station);
		};
		var remove_customer = function(station){
			// Remove the customer from the station.
			station.customer = null;
			station.element.children("span.ui-button-text").children(".name").remove();
			station.element.children("span.ui-button-text").children(".points_remain").remove();
			station.element.removeClass("filled").removeClass("ok").removeClass("warning").removeClass("critical");
		};
		var start_pulsing = function(station){
			if (station.pulsing)
				return;
			station.pulsing = true;
			var pulse = function(){
				station.element.toggleClass("pulse");
			};
			station.pulse_timer = setInterval(pulse, 1000);
		};
		var stop_pulsing = function(station){
			if (!station.pulsing)
				return;
			station.pulsing = false;
			window.clearInterval(station.pulse_timer);
			station.element.removeClass("pulse");
		};

		// Make each station's element.
		$.each(stations, function(station_id, station){
			station.id = station_id;
			station.element = $("<div />", {
				"class": "station",
				"css": {
					"left": (station.left*100)+"%",
					"top": (station.top*100)+"%",
					"width": (station.width*100)+"%",
					"height": (station.height*100)+"%"
				},
				"html": "<span class=\"station_id\">"+station_id+"</span>"
			})
			.button()
			.appendTo(station_floor);
		});

		// Handle clicks on them.
		station_floor.delegate("div.station", "click", function(){
			var station_id = $("span.station_id", this).text();
			var station = stations[station_id];
			if ("customer" in station && station.customer)
				customer_action(station);
			else
				pick_customer(station);
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
								remove_customer(station);
							return;
						}
						if (check_customer(station, cur_customer)) {
							update_customer(station, cur_customer);
						} else {
							remove_customer(station);
							insert_customer(station, cur_customer);
						}
					});
					if (worst_status == "critical") {
						if (station_layout.hasClass("warning"))
							station_layout.removeClass("warning");
						if (!station_layout.hasClass("critical"))
							station_layout.addClass("critical");
					} else if (worst_status == "warning") {
						if (!station_layout.hasClass("warning"))
							station_layout.addClass("warning");
						if (station_layout.hasClass("critical"))
							station_layout.removeClass("critical");
					} else {
						if (station_layout.hasClass("warning"))
							station_layout.removeClass("warning")
						if (station_layout.hasClass("critical"))
							station_layout.removeClass("critical");
					}
				}
			});
		};
		update_status();
		setInterval(update_status, 20000);
	});
	// ]]>
</script>
<div id="p_muid_station_layout">
	<img id="p_muid_layout_bg" src="<?php echo $this->entity->background; ?>" alt="Station Layout" />
	<div id="p_muid_station_floor"></div>
	<br style="clear: both; height: 1px;" />
</div>
<div id="p_muid_customer_action" style="display: none;">
	<div style="float: left;">
		<div>Customer ID:</div>
		<div>Login Time:</div>
		<div>Points in Account:</div>
		<div>Minutes this Session:</div>
		<div>Points this Session:</div>
		<div>Minutes in other Sessions:</div>
		<div>Points in other Sessions:</div>
		<div>Points Left:</div>
		<br />
		<div>Status:</div>
	</div>
	<div style="float: left; margin-left: 1em;">
		<div><span id="p_muid_customer_action_customer_id"></span></div>
		<div><span id="p_muid_customer_action_login_time"></span></div>
		<div><span id="p_muid_customer_action_points"></span></div>
		<div><span id="p_muid_customer_action_ses_minutes"></span></div>
		<div><span id="p_muid_customer_action_ses_points"></span></div>
		<div><span id="p_muid_customer_action_other_minutes"></span></div>
		<div><span id="p_muid_customer_action_other_points"></span></div>
		<div><span id="p_muid_customer_action_points_remain"></span></div>
		<br />
		<div><span id="p_muid_customer_action_status"></span></div>
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