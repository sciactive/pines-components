<?php
/**
 * Allows the user to clock in and out of the timeclock.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Timeclock';
$entry_count = count($this->entity->timeclock);
?>
<div class="pf-form" id="p_muid_timeclock">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			var do_clock = function(pin){
				var loader;
				$.ajax({
					url: "<?php echo addslashes(pines_url('com_hrm', 'employee/timeclock/clock')); ?>",
					type: "POST",
					dataType: "json",
					data: {"id": "self", "pin": pin},
					beforeSend: function(){
						loader = $.pnotify({
							pnotify_title: 'Timeclock',
							pnotify_text: 'Communicating with server...',
							pnotify_notice_icon: 'picon picon-throbber',
							pnotify_nonblock: true,
							pnotify_hide: false,
							pnotify_history: false
						});
					},
					complete: function(){
						loader.pnotify_remove();
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while communicating with the server:\n"+XMLHttpRequest.status+": "+textStatus);
					},
					success: function(data){
						if (!data) {
							alert("No data was returned.");
							return;
						}
						if (data == "pin") {
							alert("Invalid PIN.");
							return;
						}
						if (!data[0]) {
							pines.error("There was an error saving the change to the database.");
							return;
						}
						$("#p_muid_status").html(data[1].status);
						$("#p_muid_time").html(data[1].time);
						if (data[1].status == "in") {
							$("#p_muid_button .p_muid_button_text").html("Clock Out");
						} else {
							$("#p_muid_button .p_muid_button_text").html("Clock In");
						}
						$("#p_muid_timeclock").effect("highlight");
					}
				});
			};

			$("#p_muid_button").click(function(){
				<?php if ($pines->config->com_hrm->timeclock_verify_pin && !empty($_SESSION['user']->pin)) { ?>
				var dialog = $("<div />", {
					"title": "Please Verify Your PIN",
					"html": '<div class="pf-form"><div class="pf-element"><label><span class="pf-label">PIN</span><input type="password" class="pf-field ui-widget-content ui-corner-all" /></label></div></div><br />'
				}).dialog({
					modal: true,
					open: function(){
						$(this).find("input[type=password]").focus().keypress(function(e){
							if (e.keyCode == 13)
								dialog.dialog("option", "buttons").Done();
						});
					},
					buttons: {
						"Done": function(){
							var pin = dialog.find("input[type=password]").val();
							if (pin == "") {
								alert("Please enter a PIN.");
								return;
							}
							do_clock(pin);
							dialog.dialog("close").remove();
						}
					}
				});
				<?php } else { ?>
				do_clock();
				<?php } ?>
			});
		});
		// ]]>
	</script>
	<div class="pf-element">
		<span class="pf-label"><?php echo htmlentities($this->entity->name); ?></span>
		<span class="pf-note"><span>Status: </span><span id="p_muid_status"><?php echo empty($this->entity->timeclock) ? 'out' : htmlentities($this->entity->timeclock[$entry_count - 1]['status']); ?></span></span>
		<span class="pf-note"><span>Time: </span><span id="p_muid_time"><?php echo empty($this->entity->timeclock) ? 'No Timeclock Data' : format_date($this->entity->timeclock[$entry_count - 1]['time']); ?></span></span>
	</div>
	<div class="pf-element pf-full-width">
		<button class="pf-field ui-state-default ui-corner-all" id="p_muid_button" type="button" style="float: right;"><span class="p_muid_button_text"><?php echo $this->entity->timeclock[$entry_count - 1]['status'] == 'in' ? 'Clock Out' : 'Clock In'; ?></span></button>
	</div>
</div>