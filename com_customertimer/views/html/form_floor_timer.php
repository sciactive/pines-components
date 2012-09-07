<?php
/**
 * Provides a form for the user to manager customers on a floor.
 *
 * @package Components\customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Floor Timer ['.htmlspecialchars($this->entity->name).']';
$pines->com_pgrid->load();
$pines->com_customer->load_customer_select();
?>
<style type="text/css">
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
	}
	#p_muid_station_floor .station .name {
		display: block;
		float: left;
		font: normal xx-small sans-serif;
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

	/* Status Dependent Styles */
	#p_muid_station_floor .station.pulse {
		background-color: black;
		color: white;
	}

	/* Customer Action Dialog */
	#p_muid_customer_action div div {
		margin-bottom: .2em;
	}
</style>
<script type="text/javascript">
	pines(function(){
		var station_layout = $("#p_muid_station_layout");
		var station_floor = $("#p_muid_station_floor");
		var floor_id = <?php echo json_encode("{$this->entity->guid}"); ?>;
		var sel_station;

		var stations = <?php echo json_encode($this->entity->stations); ?>;

		$("#p_muid_customer_search").dialog({
			"width": "500px",
			"modal": true,
			"autoOpen": false,
			"buttons": {
				"Login": function(){
					// Add the selected customer.
					var dialog = $(this);
					var customer = parseInt($("#p_muid_customer").val().replace(/\D.*/, ""));
					if (!isNaN(customer) && customer != 0) {
						$.ajax({
							url: <?php echo json_encode(pines_url('com_customertimer', 'login_json')); ?>,
							type: "POST",
							data: {"id": customer, "floor": floor_id, "station": sel_station.id},
							dataType: "json",
							error: function(XMLHttpRequest, textStatus){
								pines.error("An error occured while trying to log the user in:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
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
						url: <?php echo json_encode(pines_url('com_customertimer', 'logout_json')); ?>,
						type: "POST",
						data: {"id": sel_station.customer.guid, "floor": floor_id, "station": sel_station.id},
						dataType: "json",
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured while trying to log the user out:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
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
					pines.get(<?php echo json_encode(pines_url('com_customer', 'customer/edit')); ?>, {
						"id": sel_station.customer.guid
					});
				},
				"Purchase Minutes": function(){
					pines.post(<?php echo json_encode(pines_url('com_customertimer', 'newsale')); ?>, {
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
			$("#p_muid_customer_action_customer_id").html(pines.safe(station.customer.guid));
			$("#p_muid_customer_action_login_time").html(pines.safe(login_time.toLocaleString()));
			$("#p_muid_customer_action_points").html(pines.safe(station.customer.points));
			$("#p_muid_customer_action_ses_minutes").html(pines.safe(station.customer.ses_minutes));
			$("#p_muid_customer_action_ses_points").html(pines.safe(station.customer.ses_points));
			$("#p_muid_customer_action_other_minutes").html(pines.safe(station.customer.other_minutes));
			$("#p_muid_customer_action_other_points").html(pines.safe(station.customer.other_points));
			$("#p_muid_customer_action_points_remain").html(pines.safe(station.customer.points_remain));
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
			.dialog("option", "title", "Station "+pines.safe(station.id)+": "+pines.safe(station.customer.name))
			.dialog("open");
		};
		var insert_customer = function(station, customer){
			// Insert a customer into the station.
			station.element.addClass("filled").append($("<span></span>", {
				"class": "points_remain"
			})).append($("<span></span>", {
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
			$(".name", station.element).html(pines.safe(customer.name));
			$(".points_remain", station.element).html(pines.safe(customer.points_remain));
			if (customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_critical; ?>) {
				if (station.element.hasClass("btn-success"))
					station.element.removeClass("btn-success");
				if (station.element.hasClass("btn-warning"))
					station.element.removeClass("btn-warning");
				if (!station.element.hasClass("btn-danger"))
					station.element.addClass("btn-danger");
				worst_status = "btn-danger";
			} else if (customer.points_remain <= <?php echo (int) $pines->config->com_customertimer->level_warning; ?>) {
				if (station.element.hasClass("btn-success"))
					station.element.removeClass("btn-success");
				if (!station.element.hasClass("btn-warning"))
					station.element.addClass("btn-warning");
				if (station.element.hasClass("btn-danger"))
					station.element.removeClass("btn-danger");
				if (worst_status != "btn-danger")
					worst_status = "btn-warning";
			} else {
				if (!station.element.hasClass("btn-success"))
					station.element.addClass("btn-success");
				if (station.element.hasClass("btn-warning"))
					station.element.removeClass("btn-warning");
				if (station.element.hasClass("btn-danger"))
					station.element.removeClass("btn-danger");
			}
			if (customer.points_remain <= 0)
				start_pulsing(station);
			else
				stop_pulsing(station);
		};
		var remove_customer = function(station){
			// Remove the customer from the station.
			station.customer = null;
			station.element.children(".name").remove();
			station.element.children(".points_remain").remove();
			station.element.removeClass("filled").removeClass("btn-success").removeClass("btn-warning").removeClass("btn-danger");
			stop_pulsing(station);
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
			station.element = $("<button></button>", {
				"class": "station btn",
				"css": {
					"left": (station.left*100)+"%",
					"top": (station.top*100)+"%",
					"width": (station.width*100)+"%",
					"height": (station.height*100)+"%"
				},
				"html": "<span class=\"station_id\">"+station_id+"</span>"
			})
			.appendTo(station_floor);
		});

		// Handle clicks on them.
		station_floor.delegate(".station", "click", function(){
			var station_id = $(".station_id", this).text();
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
				url: <?php echo json_encode(pines_url('com_customertimer', 'status_json')); ?>,
				type: "GET",
				data: {"floor": floor_id},
				dataType: "json",
				complete: function(){
					updating = false;
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to refresh the status:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					worst_status = "btn-success";
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
					if (worst_status == "btn-danger") {
						if (station_layout.hasClass("alert-success"))
							station_layout.removeClass("alert-success");
						if (!station_layout.hasClass("alert-error"))
							station_layout.addClass("alert-error");
					} else if (worst_status == "btn-warning") {
						if (station_layout.hasClass("alert-success"))
							station_layout.removeClass("alert-success");
						if (station_layout.hasClass("alert-error"))
							station_layout.removeClass("alert-error");
					} else {
						if (!station_layout.hasClass("alert-success"))
							station_layout.addClass("alert-success")
						if (station_layout.hasClass("alert-error"))
							station_layout.removeClass("alert-error");
					}
				}
			});
		};
		update_status();
		setInterval(update_status, 20000);
	});
</script>
<div id="p_muid_station_layout" class="alert alert-success">
	<img id="p_muid_layout_bg" src="<?php echo htmlspecialchars($this->entity->background); ?>" alt="Station Layout" />
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
				<input class="pf-field" type="text" id="p_muid_customer" name="customer" size="24" />
			</div>
		</div>
	</div>
	<br style="clear: both; height: 1px;" />
</div>