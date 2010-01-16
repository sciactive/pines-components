<?php
/**
 * Shows customer timer status.
 *
 * @package Pines
 * @subpackage com_customer_timer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Customer Timer Status';
?>
<script type="text/javascript">
	// <![CDATA[
	var customer_status;

	$(function(){
		customer_status = $("#customer_status");
		update_status();
	});

	function update_status() {
		$.ajax({
			url: "<?php echo pines_url('com_customer_timer', 'status_json'); ?>",
			type: "GET",
			dataType: "json",
			complete: function(){
				setTimeout(update_status, 5000);
			},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to refresh the status:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				customer_status.children(".loading").remove();
				if (!data || !data[0]) {
					customer_status.html('<span class="loading">No customers are logged in.</span>');
					return;
				}
				var guids = [];
				$.each(data, function(){
					guids = $.merge(guids, [this.guid]);
					show_status(this);
				});
				var filter_selector = ":not(.customer_box_"+guids.join(", .customer_box_")+")";
				customer_status.children(filter_selector).fadeOut("slow", function(){
					$(this).remove();
				});
				sort_customers();
			}
		});
	}

	function show_status(customer) {
		var box = customer_status.find(".customer_box_"+customer.guid);
		if (box.length == 0) {
			box = $("<div />").addClass("ui-widget ui-widget-content ui-corner-all customer_box customer_box_"+customer.guid).css({"position":"relative","display":"none","padding":"2px","margin-bottom":"5px"})
			.append($("<div/>").addClass("ui-state-default ui-corner-all customer_header").css("padding", "2px").append(
				$("<div>"+customer.guid+": "+customer.name+"</div>").append(
					$("<div>Status: </div>").addClass("status").css("float", "right").append($("<span />").addClass("value"))
				)
			))
			.append($("<div/>").css("position", "relative")
				.append($("<div>Login Time: </div>").addClass("login_time").append($("<span />").addClass("value")))
				.append($("<div>Points in Account: </div>").addClass("points").append($("<span />").addClass("value")))
				.append($("<span>Minutes this Session: </span>").addClass("ses_minutes").css("margin-right","12px").append($("<span />").addClass("value")))
				.append($("<span>Points Used: </span>").addClass("ses_points").css("margin-right","12px").append($("<span />").addClass("value")))
				.append($("<span>Points Left: </span>").addClass("points_remain").css("margin-right","12px").append($("<span />").addClass("value")))
				.append($("<button>Logout</button>").addClass("ui-state-default").css({"position":"absolute","right":"2px","top":"2px"}).hover(function(){
					$(this).addClass("ui-state-hover");
				}, function(){
					$(this).removeClass("ui-state-hover");
				}).click(function(){
					$.ajax({
						url: "<?php echo pines_url('com_customer_timer', 'logout_json'); ?>",
						type: "POST",
						data: {"id": customer.guid},
						dataType: "json",
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured while trying to log the user out:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (!data) {
								alert("The user couldn't be logged out.");
							} else {
								alert("The user has been logged out.");
							}
						}
					});
				}))
			);
			box.appendTo(customer_status).fadeIn("slow");
		}
		var login_time = new Date(customer.login_time * 1000);
		box.find(".login_time .value").html(login_time.toLocaleString());
		box.find(".points .value").html(customer.points);
		box.find(".ses_minutes .value").html(customer.ses_minutes);
		box.find(".ses_points .value").html(customer.ses_points);
		box.find(".points_remain .value").html(customer.points_remain);
		if (customer.points_remain <= <?php echo (int) $config->com_customer_timer->level_critical; ?>) {
			box.removeClass("ok").removeClass("warning").addClass("critical").children(".customer_header").removeClass("ui-state-highlight").addClass("ui-state-error");
			box.find(".status .value").html("Critical");
		} else if (customer.points_remain <= <?php echo (int) $config->com_customer_timer->level_warning; ?>) {
			box.removeClass("ok").addClass("warning").removeClass("critical").children(".customer_header").addClass("ui-state-highlight").removeClass("ui-state-error");
			box.find(".status .value").html("Warning");
		} else {
			box.addClass("ok").removeClass("warning").removeClass("critical").children(".customer_header").removeClass("ui-state-highlight").removeClass("ui-state-error");
			box.find(".status .value").html("OK");
		}
		if (customer.points_remain < 0) {
			box.find(".status .value").html("Overdrawn");
		}
	}

	function sort_customers() {
		customer_status.children(".customer_box.critical").prevAll(".customer_box:not(.critical)").appendTo(customer_status);
		customer_status.children(".customer_box.warning").prevAll(".customer_box:not(.critical, .warning)").appendTo(customer_status);
	}
	// ]]>
</script>
<div id="customer_status"><span class="loading">Loading, please wait...</span></div>